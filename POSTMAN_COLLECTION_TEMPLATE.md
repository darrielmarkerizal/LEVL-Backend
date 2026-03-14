# TEMPLATE POSTMAN COLLECTION - LEVL API

## 📋 TEMPLATE UNTUK SETIAP PLATFORM

### Template ini berisi contoh struktur untuk masing-masing platform

---

## 📱 TEMPLATE: MOBILE STUDENT APP

### Struktur Folder
```
[MOBILE] Student App/
├── 🔐 1. Authentication/
├── 📚 2. Learning/
├── 🎮 3. Gamification/
├── 💬 4. Forums/
├── 📊 5. Dashboard/
└── 👤 6. Profile/
```

### Contoh Request: Login
```
Name: POST [Mobile] - Auth - Login
Method: POST
URL: {{base_url}}/auth/login

Headers:
- Content-Type: application/json
- Accept: application/json

Body (raw JSON):
{
  "email": "student@example.com",
  "password": "password123",
  "device_name": "iPhone 13"
}

Tests:
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('token');
});

if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("auth_token", jsonData.data.token);
    pm.environment.set("user_id", jsonData.data.user.id);
}
```

---

## 💻 TEMPLATE: ADMIN WEB DASHBOARD

### Struktur Folder
```
[WEB] Admin Dashboard/
├── 🔐 1. Authentication/
├── 👥 2. User Management/
├── 📖 3. Course Management/
├── 📝 4. Content Management/
├── 📊 5. Reports & Analytics/
├── 🎯 6. Enrollment Management/
├── 🎮 7. Gamification Management/
└── 🗑️ 8. Trash Management/
```

### Contoh Request: Create Student
```
Name: POST [Admin] - Users - Create Student
Method: POST
URL: {{base_url}}/admin/users/students

Headers:
- Authorization: Bearer {{auth_token}}
- Content-Type: application/json
- Accept: application/json

Body (raw JSON):
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890"
}

Tests:
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("User created successfully", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});
```

---

## 🎓 TEMPLATE: INSTRUCTOR WEB DASHBOARD

### Struktur Folder
```
[WEB] Instructor Dashboard/
├── 🔐 1. Authentication/
├── 📖 2. My Courses/
├── 📝 3. Content Creation/
├── ✅ 4. Grading/
├── 💬 5. Forums/
├── 📊 6. Course Analytics/
└── 👤 7. Profile/
```

---

## 🌐 TEMPLATE: SHARED COMMON APIs

### Struktur Folder
```
[SHARED] Common APIs/
├── 🔐 1. Auth/
├── 👤 2. Profile Management/
├── 🔔 3. Notifications/
├── 🔍 4. Search/
├── 📁 5. Media Upload/
└── ⚙️ 6. System Settings/
```

---

## 📝 TEMPLATE REQUEST LENGKAP

Setiap request harus memiliki:

1. **Name**: Format `[METHOD] [Platform] - [Feature] - [Action]`
2. **Description**: Penjelasan lengkap
3. **Headers**: Authorization, Content-Type, Accept
4. **Body**: Contoh request body (jika POST/PUT)
5. **Tests**: Basic validation tests
6. **Examples**: Success dan error responses

---

Lihat file lengkap di dokumentasi terpisah untuk setiap module.
