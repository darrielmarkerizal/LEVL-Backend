# DOKUMENTASI POSTMAN - LEVL API
**Platform**: Mobile App, Admin Web, Instructor Web  
**Versi API**: v1  
**Last Updated**: 2026-03-14

---

## 📚 DAFTAR DOKUMENTASI

### 🎯 Mulai Dari Sini
1. **[POSTMAN_QUICK_REFERENCE.md](POSTMAN_QUICK_REFERENCE.md)** ⭐
   - Quick start guide untuk semua developer
   - Pilih platform Anda dan langsung mulai
   - Endpoint yang paling sering digunakan
   - Authentication flow
   - Common error codes

2. **[POSTMAN_DOCUMENTATION_STRUCTURE.md](POSTMAN_DOCUMENTATION_STRUCTURE.md)**
   - Struktur lengkap collection
   - Organisasi folder dan naming convention
   - Environment variables
   - Best practices

3. **[POSTMAN_COLLECTION_TEMPLATE.md](POSTMAN_COLLECTION_TEMPLATE.md)**
   - Template untuk setiap platform
   - Contoh request format
   - Testing scripts

---

## 🚀 QUICK START

### 1. Import Collection ke Postman
```bash
# Download collection dari repository
# Import ke Postman: File > Import > Choose Files
```

### 2. Setup Environment
```bash
# Buat environment baru: Development
# Tambahkan variables:
base_url: http://localhost:8000/api
auth_token: (akan diisi otomatis setelah login)
user_id: (akan diisi otomatis setelah login)
role: student
```

### 3. Login
```bash
# Jalankan request: POST [Shared] - Auth - Login
# Token akan tersimpan otomatis di environment
```

### 4. Mulai Testing
```bash
# Pilih folder sesuai platform Anda
# Jalankan request yang Anda butuhkan
```

---

## 📱 UNTUK MOBILE DEVELOPER

**Folder Anda**: `📱 [MOBILE] Student App` + `🌐 [SHARED] Common APIs`

**Workflow Umum**:
1. Login → Simpan token
2. Get enrolled courses
3. Get course detail
4. Get lesson detail
5. Mark lesson complete
6. Check gamification stats

**Dokumentasi Lengkap**:
- [PANDUAN_LEVEL_MANAGEMENT_LENGKAP.md](PANDUAN_LEVEL_MANAGEMENT_LENGKAP.md)
- [PANDUAN_BADGE_MANAGEMENT_LENGKAP_V2.md](PANDUAN_BADGE_MANAGEMENT_LENGKAP_V2.md)
- [PANDUAN_FORUM_MANAGEMENT_LENGKAP.md](PANDUAN_FORUM_MANAGEMENT_LENGKAP.md)

---

## 💻 UNTUK ADMIN WEB DEVELOPER

**Folder Anda**: `💻 [WEB] Admin Dashboard` + `🌐 [SHARED] Common APIs`

**Workflow Umum**:
1. Login → Simpan token
2. List users/courses/enrollments
3. Create/Update/Delete resources
4. View reports & analytics
5. Manage gamification

**Dokumentasi Lengkap**:
- [PANDUAN_USER_MANAGEMENT_LENGKAP.md](PANDUAN_USER_MANAGEMENT_LENGKAP.md)
- [PANDUAN_ENROLLMENT_MANAGEMENT_LENGKAP.md](PANDUAN_ENROLLMENT_MANAGEMENT_LENGKAP.md)
- [PANDUAN_FORM_MANAGEMENT_LENGKAP.md](PANDUAN_FORM_MANAGEMENT_LENGKAP.md)

---

## 🎓 UNTUK INSTRUCTOR WEB DEVELOPER

**Folder Anda**: `🎓 [WEB] Instructor Dashboard` + `🌐 [SHARED] Common APIs`

**Workflow Umum**:
1. Login → Simpan token
2. List my courses
3. Create/Update content
4. Grade submissions
5. View course analytics
6. Manage forums

**Dokumentasi Lengkap**:
- [DOKUMENTASI_API_LEARNING_SCHEMES_LENGKAP.md](DOKUMENTASI_API_LEARNING_SCHEMES_LENGKAP.md)
- [PANDUAN_FORUM_MANAGEMENT_LENGKAP.md](PANDUAN_FORUM_MANAGEMENT_LENGKAP.md)

---

## 🔑 AUTHENTICATION

### Login Flow
```
1. POST /auth/login
   → Dapatkan token

2. Simpan token di environment
   → pm.environment.set("auth_token", token)

3. Gunakan token di semua request
   → Header: Authorization: Bearer {{auth_token}}

4. Token expired? Login ulang
   → Atau gunakan refresh token
```

### Token Management
- Token disimpan di environment variable `auth_token`
- Token otomatis digunakan di semua request (via header)
- Token expired setelah 24 jam (default)
- Gunakan refresh token untuk perpanjang session

---

## 📊 RESPONSE FORMAT

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  },
  "links": { }
}
```

---

## ⚠️ COMMON ISSUES

### 1. Token Expired (401)
**Problem**: `Unauthenticated`  
**Solution**: Login ulang

### 2. Validation Error (422)
**Problem**: `Validation failed`  
**Solution**: Periksa field yang error

### 3. Not Found (404)
**Problem**: `Resource not found`  
**Solution**: Periksa ID resource

### 4. Forbidden (403)
**Problem**: `Forbidden`  
**Solution**: Gunakan user dengan role yang sesuai

---

## 🧪 TESTING

### Basic Tests (Tambahkan di setiap request)
```javascript
// Test status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test response structure
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});
```

### Save Variables
```javascript
// Simpan token setelah login
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.token);
}
```

---

## 📖 DOKUMENTASI MODULE

### Core Modules
- [User Management](PANDUAN_USER_MANAGEMENT_LENGKAP.md)
- [Enrollment Management](PANDUAN_ENROLLMENT_MANAGEMENT_LENGKAP.md)
- [Forum Management](PANDUAN_FORUM_MANAGEMENT_LENGKAP.md)

### Gamification
- [Badge Management](PANDUAN_BADGE_MANAGEMENT_LENGKAP_V2.md)
- [Level Management](PANDUAN_LEVEL_MANAGEMENT_LENGKAP.md)

### Learning
- [Learning & Schemes](DOKUMENTASI_API_LEARNING_SCHEMES_LENGKAP.md)
- [Form Management](PANDUAN_FORM_MANAGEMENT_LENGKAP.md)

---

## 🔄 UPDATE LOG

### 2026-03-14
- ✅ Struktur dokumentasi dibuat
- ✅ Quick reference guide
- ✅ Template collection
- ✅ Platform-specific guides

---

## 💡 TIPS

1. **Gunakan environment variables** untuk semua dynamic values
2. **Simpan token otomatis** setelah login
3. **Gunakan folder yang sesuai** dengan platform Anda
4. **Test sebelum integrate** ke aplikasi
5. **Update dokumentasi** jika ada perubahan

---

## 📞 SUPPORT

Butuh bantuan?
- Backend Team: untuk pertanyaan API
- DevOps Team: untuk pertanyaan environment
- Documentation Team: untuk pertanyaan dokumentasi

---

**Happy Coding! 🚀**
