# User Creation Enhancement: Student Support with Auto-Generation

## Overview
Enhanced the user creation API to support creating Student users with automatic username and password generation when not provided.

## Changes Made

### 1. Validation Rules Updated
**File**: `Modules/Auth/app/Http/Requests/Concerns/HasAuthRequestRules.php`

#### Before:
- `username`: **required**
- `password`: not in validation (auto-generated for all)
- `role`: Student creation was **forbidden**

#### After:
- `username`: **nullable** (auto-generated if empty)
- `password`: **nullable** (auto-generated if empty, min:8 if provided)
- `role`: Student creation **allowed**

### 2. Service Logic Updated
**File**: `Modules/Auth/app/Services/Support/UserLifecycleProcessor.php`

#### Key Changes:

1. **Removed Student Creation Block**
   - Removed validation that prevented Student role creation
   - Updated authorization to allow Admin and Superadmin to create Students

2. **Added Auto-Generation Logic**
   ```php
   $passwordPlain = $validated['password'] ?? Str::random(12);
   
   if (empty($validated['username'])) {
       $validated['username'] = $this->generateUniqueUsername($validated['name'], $validated['email']);
   }
   ```

3. **Added Username Generation Methods**
   - `generateUniqueUsername()`: Creates unique username from name or email
   - `sanitizeUsername()`: Cleans and formats username to match validation rules

#### Username Generation Logic:
1. Sanitize the user's name (lowercase, remove special chars)
2. If name is empty/invalid, use email prefix
3. Check uniqueness in database
4. If exists, append counter (e.g., `john_doe1`, `john_doe2`)
5. Limit to 50 characters

## API Endpoint

### Create User
```
POST /api/v1/users
```

**Authorization**: Admin or Superadmin role required

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body

#### Required Fields:
- `name` (string, max:255) - Full name
- `email` (string, email, unique) - Email address
- `role` (enum) - Student, Instructor, Admin, Superadmin

#### Optional Fields:
- `username` (string, min:3, max:255, unique) - Auto-generated if not provided
- `password` (string, min:8) - Auto-generated if not provided
- `specialization_id` (integer) - Required if role=Instructor

### Example Requests

#### 1. Create Student with Auto-Generated Username & Password
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "role": "Student"
}
```

**Result**:
- Username: `john_doe` (or `john_doe1` if exists)
- Password: Random 12-character string
- Credentials sent via email

#### 2. Create Student with Custom Username
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "username": "janesmith",
  "role": "Student"
}
```

**Result**:
- Username: `janesmith`
- Password: Random 12-character string
- Credentials sent via email

#### 3. Create Student with Custom Username & Password
```json
{
  "name": "Bob Wilson",
  "email": "bob.wilson@example.com",
  "username": "bobwilson",
  "password": "SecurePass123!",
  "role": "Student"
}
```

**Result**:
- Username: `bobwilson`
- Password: `SecurePass123!`
- Credentials sent via email

#### 4. Create Instructor (Requires Specialization)
```json
{
  "name": "Dr. Alice Brown",
  "email": "alice.brown@example.com",
  "role": "Instructor",
  "specialization_id": 5
}
```

**Result**:
- Username: Auto-generated from name
- Password: Random 12-character string
- Credentials sent via email

### Response Format

#### Success (201 Created):
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 123,
    "name": "John Doe",
    "username": "john_doe",
    "email": "john.doe@example.com",
    "role": "Student",
    "status": "Active",
    "is_password_set": false,
    "created_at": "2026-03-06T10:00:00Z"
  }
}
```

#### Error (422 Validation Error):
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email has already been taken."],
    "username": ["The username has already been taken."]
  }
}
```

## Authorization Matrix

| Auth User Role | Can Create Roles |
|----------------|------------------|
| Superadmin | Student, Instructor, Admin, Superadmin |
| Admin | Student, Instructor, Admin |
| Instructor | ❌ No access |
| Student | ❌ No access |

## Username Generation Rules

1. **Source Priority**:
   - Use provided `name` field
   - Fallback to email prefix if name is invalid

2. **Sanitization**:
   - Convert to lowercase
   - Remove all characters except: `a-z`, `0-9`, `_`, `.`, `-`
   - Replace multiple consecutive special chars with single `_`
   - Trim leading/trailing special chars
   - Limit to 50 characters

3. **Uniqueness**:
   - Check database for existing username
   - Append counter if exists (1, 2, 3, ...)
   - Continue until unique username found

4. **Examples**:
   - `"John Doe"` → `john_doe`
   - `"Jane O'Brien"` → `jane_obrien`
   - `"Bob-Smith Jr."` → `bob_smith_jr`
   - `"李明"` → (empty, fallback to email)
   - `"john@example.com"` → `john`

## Password Generation

- **Length**: 12 characters
- **Method**: `Str::random(12)`
- **Characters**: Alphanumeric (a-z, A-Z, 0-9)
- **Security**: Cryptographically secure random

## Email Notification

After user creation, an email is automatically sent containing:
- Username
- Temporary password
- Login URL
- Instructions to change password

**Email Class**: `Modules\Mail\Mail\Auth\UserCredentialsMail`

## Security Considerations

1. **Password Security**:
   - All passwords are hashed using `Hash::make()`
   - `is_password_set` flag set to `false` for auto-generated passwords
   - Users should change password on first login

2. **Username Validation**:
   - Regex: `/^[a-z0-9_\.\-]+$/i`
   - Prevents SQL injection and XSS
   - Ensures URL-safe usernames

3. **Email Uniqueness**:
   - Email must be unique across all users
   - Prevents duplicate accounts

4. **Authorization**:
   - Only Admin and Superadmin can create users
   - Role-based restrictions enforced

## Testing

### Manual Testing

1. **Create Student with Auto-Generation**:
   ```bash
   curl -X POST http://localhost:8000/api/v1/users \
     -H "Authorization: Bearer {admin_token}" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test Student",
       "email": "test.student@example.com",
       "role": "Student"
     }'
   ```

2. **Create Student with Custom Username**:
   ```bash
   curl -X POST http://localhost:8000/api/v1/users \
     -H "Authorization: Bearer {admin_token}" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test Student 2",
       "email": "test.student2@example.com",
       "username": "teststudent2",
       "role": "Student"
     }'
   ```

3. **Verify Email Sent**:
   - Check mail logs or mail catcher
   - Verify credentials are included

### Edge Cases to Test

1. **Duplicate Username Handling**:
   - Create user with name "John Doe"
   - Create another user with name "John Doe"
   - Verify second user gets "john_doe1"

2. **Special Characters in Name**:
   - Test with: "O'Brien", "José García", "李明"
   - Verify sanitization works correctly

3. **Empty/Invalid Name**:
   - Provide empty name or special chars only
   - Verify fallback to email prefix

4. **Authorization**:
   - Try creating user as Student role
   - Verify 403 Forbidden response

## Migration Impact

**No database migrations required** - This is a logic-only change.

## Backward Compatibility

✅ **Fully backward compatible**

- Existing API calls with `username` and `password` still work
- Only adds optional behavior when fields are omitted
- No breaking changes to existing functionality

## Benefits

1. **Simplified User Creation**: No need to generate usernames manually
2. **Bulk Import Friendly**: Easy to import users from CSV/Excel
3. **Reduced Errors**: Auto-generation prevents invalid usernames
4. **Flexible**: Can still provide custom username/password if needed
5. **Student Support**: Can now create student accounts via API

## Use Cases

1. **Bulk Student Import**: Import students from school database
2. **Course Enrollment**: Auto-create student accounts during enrollment
3. **Integration**: Third-party systems can create users easily
4. **Admin Panel**: Simplified user creation form

---

**Created**: March 6, 2026  
**Status**: Complete  
**Files Modified**: 2  
**Breaking Changes**: None  
**Migration Required**: No
