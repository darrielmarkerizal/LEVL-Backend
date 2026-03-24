# Flutter Mobile Implementation Example

## Deep Link Configuration

### Android (android/app/src/main/AndroidManifest.xml)
```xml
<activity
    android:name=".MainActivity"
    ...>
    <!-- Deep Link Intent Filter -->
    <intent-filter>
        <action android:name="android.intent.action.VIEW" />
        <category android:name="android.intent.category.DEFAULT" />
        <category android:name="android.intent.category.BROWSABLE" />
        <data
            android:scheme="levl"
            android:host="verify" />
    </intent-filter>
</activity>
```

### iOS (ios/Runner/Info.plist)
```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleTypeRole</key>
        <string>Editor</string>
        <key>CFBundleURLName</key>
        <string>com.levl.app</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>levl</string>
        </array>
    </dict>
</array>
```

## Flutter Implementation

### 1. Add Dependencies (pubspec.yaml)
```yaml
dependencies:
  uni_links: ^0.5.1
  http: ^1.1.0
  shared_preferences: ^2.2.0
```

### 2. Deep Link Handler Service
```dart
// lib/services/deep_link_service.dart
import 'dart:async';
import 'package:uni_links/uni_links.dart';
import 'package:flutter/services.dart';

class DeepLinkService {
  StreamSubscription? _sub;
  
  // Callback untuk handle verification
  Function(String userId, String email, String uuid, String token)? onVerificationLink;
  
  Future<void> init() async {
    // Handle initial link jika app dibuka dari deep link
    try {
      final initialLink = await getInitialLink();
      if (initialLink != null) {
        _handleDeepLink(initialLink);
      }
    } on PlatformException {
      // Handle error
    }
    
    // Listen untuk deep link saat app sudah running
    _sub = linkStream.listen((String? link) {
      if (link != null) {
        _handleDeepLink(link);
      }
    }, onError: (err) {
      // Handle error
    });
  }
  
  void _handleDeepLink(String link) {
    final uri = Uri.parse(link);
    
    // Check if it's verification link: levl://verify
    if (uri.scheme == 'levl' && uri.host == 'verify') {
      final userId = uri.queryParameters['userId'];
      final email = uri.queryParameters['email'];
      final uuid = uri.queryParameters['uuid'];
      final token = uri.queryParameters['token'];
      
      if (userId != null && email != null && uuid != null && token != null) {
        onVerificationLink?.call(userId, email, uuid, token);
      }
    }
  }
  
  void dispose() {
    _sub?.cancel();
  }
}
```

### 3. API Service
```dart
// lib/services/api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'https://api.levl.com/api/v1';
  
  // Verify email dengan token dan uuid
  Future<Map<String, dynamic>> verifyEmail({
    required String token,
    required String uuid,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/email/verify'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'token': token,
        'uuid': uuid,
      }),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      
      // Save tokens
      if (data['success'] == true && data['data'] != null) {
        await _saveTokens(
          accessToken: data['data']['access_token'],
          refreshToken: data['data']['refresh_token'],
        );
        
        return data['data'];
      }
    }
    
    throw Exception('Verification failed: ${response.body}');
  }
  
  Future<void> _saveTokens({
    required String accessToken,
    required String refreshToken,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('access_token', accessToken);
    await prefs.setString('refresh_token', refreshToken);
  }
}
```

### 4. Verification Screen
```dart
// lib/screens/verification_screen.dart
import 'package:flutter/material.dart';

class VerificationScreen extends StatefulWidget {
  final String userId;
  final String email;
  final String uuid;
  final String token;
  
  const VerificationScreen({
    Key? key,
    required this.userId,
    required this.email,
    required this.uuid,
    required this.token,
  }) : super(key: key);
  
  @override
  State<VerificationScreen> createState() => _VerificationScreenState();
}

class _VerificationScreenState extends State<VerificationScreen> {
  bool _isVerifying = false;
  String? _errorMessage;
  
  @override
  void initState() {
    super.initState();
    _verifyEmail();
  }
  
  Future<void> _verifyEmail() async {
    setState(() {
      _isVerifying = true;
      _errorMessage = null;
    });
    
    try {
      final apiService = ApiService();
      final result = await apiService.verifyEmail(
        token: widget.token,
        uuid: widget.uuid,
      );
      
      // Verification successful
      if (mounted) {
        // Navigate to home/dashboard
        Navigator.of(context).pushReplacementNamed(
          '/home',
          arguments: result['user'],
        );
      }
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isVerifying = false;
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Verifikasi Email'),
      ),
      body: Center(
        child: _isVerifying
            ? Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const CircularProgressIndicator(),
                  const SizedBox(height: 16),
                  Text('Memverifikasi email ${widget.email}...'),
                ],
              )
            : _errorMessage != null
                ? Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.error_outline,
                        color: Colors.red,
                        size: 64,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Verifikasi Gagal',
                        style: Theme.of(context).textTheme.headlineSmall,
                      ),
                      const SizedBox(height: 8),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Text(
                          _errorMessage!,
                          textAlign: TextAlign.center,
                        ),
                      ),
                      const SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: _verifyEmail,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  )
                : const SizedBox.shrink(),
      ),
    );
  }
}
```

### 5. Main App Setup
```dart
// lib/main.dart
import 'package:flutter/material.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);
  
  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  final DeepLinkService _deepLinkService = DeepLinkService();
  final GlobalKey<NavigatorState> _navigatorKey = GlobalKey<NavigatorState>();
  
  @override
  void initState() {
    super.initState();
    _initDeepLinks();
  }
  
  void _initDeepLinks() {
    _deepLinkService.onVerificationLink = (userId, email, uuid, token) {
      // Navigate to verification screen
      _navigatorKey.currentState?.push(
        MaterialPageRoute(
          builder: (context) => VerificationScreen(
            userId: userId,
            email: email,
            uuid: uuid,
            token: token,
          ),
        ),
      );
    };
    
    _deepLinkService.init();
  }
  
  @override
  void dispose() {
    _deepLinkService.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Levl',
      navigatorKey: _navigatorKey,
      home: const HomeScreen(),
      routes: {
        '/home': (context) => const HomeScreen(),
        // ... other routes
      },
    );
  }
}
```

## Testing Deep Link

### Android
```bash
# Test deep link via ADB
adb shell am start -W -a android.intent.action.VIEW \
  -d "levl://verify?userId=123&email=test@example.com&uuid=xxx-xxx-xxx&token=xxxxxxxxxxxxxxxx" \
  com.levl.app
```

### iOS
```bash
# Test deep link via xcrun
xcrun simctl openurl booted \
  "levl://verify?userId=123&email=test@example.com&uuid=xxx-xxx-xxx&token=xxxxxxxxxxxxxxxx"
```

## Error Handling

### Common Errors

1. **Token Invalid (422)**
```dart
if (response.statusCode == 422) {
  final error = jsonDecode(response.body);
  if (error['message'].contains('invalid')) {
    // Show: "Token verifikasi tidak valid"
  }
}
```

2. **Token Expired (422)**
```dart
if (error['message'].contains('expired')) {
  // Show: "Token sudah kadaluarsa. Silakan minta link baru"
  // Provide button to resend verification email
}
```

3. **Network Error**
```dart
try {
  await apiService.verifyEmail(...);
} on SocketException {
  // Show: "Tidak ada koneksi internet"
} on TimeoutException {
  // Show: "Request timeout. Coba lagi"
}
```

## User Flow

1. User register di app
2. User menerima email dengan deep link
3. User tap link di email
4. App terbuka dan navigate ke VerificationScreen
5. VerificationScreen otomatis hit API verify
6. Jika sukses: save tokens dan navigate ke home
7. Jika gagal: show error dan tombol retry

## Security Considerations

1. **Token Storage**: Gunakan `flutter_secure_storage` untuk production
2. **SSL Pinning**: Implement SSL pinning untuk API calls
3. **Token Expiry**: Handle token expiry dengan refresh token
4. **Deep Link Validation**: Validate semua parameters sebelum hit API
