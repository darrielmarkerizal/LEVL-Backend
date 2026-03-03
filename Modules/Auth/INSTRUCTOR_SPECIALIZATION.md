# Instructor Specialization Feature

## Overview

Fitur spesialisasi untuk user dengan role Instructor telah ditambahkan. Field `specialization_id` adalah foreign key ke tabel `categories`. Ketika GET `/users`, specialization akan return object dengan `id`, `name`, dan `value`.

## Database Changes

### Migration 1: Add specialization column (deprecated)

File: `2026_03_03_100000_add_specialization_to_users_table.php`

This migration adds a string column (will be replaced by migration 2).

### Migration 2: Change to foreign key

File: `2026_03_03_100001_change_specialization_to_foreign_key.php`

```php
Schema::table('users', function (Blueprint $table) {
    // Drop old specialization column
    $table->dropColumn('specialization');
});

Schema::table('users', function (Blueprint $table) {
    // Add specialization_id as foreign key
    $table->foreignId('specialization_id')
        ->nullable()
        ->after('bio')
        ->constrained('categories')
        ->nullOnDelete();
});
```

**Field Details:**
- Name: `specialization_id`
- Type: `BIGINT UNSIGNED` (foreign key)
- Nullable: Yes
- Position: After `bio` column
- References: `categories.id`
- On Delete: SET NULL

### Running Migrations

```bash
php artisan migrate
```

## Model Changes

### User Model

File: `Modules/Auth/app/Models/User.php`

**Added to `$fillable`:**
```php
protected $fillable = [
    // ... existing fields
    'specialization_id',
    // ... other fields
];
```

**Added relationship:**
```php
public function specialization()
{
    return $this->belongsTo(\Modules\Common\Models\Category::class, 'specialization_id');
}
```

### Category Model

File: `Modules/Common/app/Models/Category.php`

**Added relationship:**
```php
public function instructors()
{
    return $this->hasMany(\Modules\Auth\Models\User::class, 'specialization_id');
}
```

## Seeder

### InstructorSpecializationSeeder

File: `Modules/Auth/database/seeders/InstructorSpecializationSeeder.php`

Seeder ini akan mengisi `specialization_id` random untuk semua instructor yang sudah ada di database.

**Running Seeder:**

```bash
# Jalankan CategorySeeder terlebih dahulu
php artisan db:seed --class=Modules\\Common\\Database\\Seeders\\CategorySeeder

# Kemudian jalankan InstructorSpecializationSeeder
php artisan db:seed --class=Modules\\Auth\\Database\\Seeders\\InstructorSpecializationSeeder
```

**How it works:**
1. Fetches active category IDs from database
2. Finds all users with "Instructor" role without specialization
3. Assigns random category ID to each instructor
4. Processes in chunks of 100 for performance

## Factory Changes

### UserFactory

File: `database/factories/UserFactory.php`

**Updated Method:**
```php
public function instructor(): static
{
    // Get active category IDs from database
    $categoryIds = \Modules\Common\Models\Category::where('status', 'active')
        ->pluck('id')
        ->toArray();

    $specializationId = !empty($categoryIds) ? fake()->randomElement($categoryIds) : null;

    return $this->state(fn (array $attributes) => [
        'specialization_id' => $specializationId,
        'status' => UserStatus::Active->value,
        'email_verified_at' => now()->subDays(rand(1, 365)),
    ]);
}
```

**Usage:**
```php
// Create instructor with random specialization
User::factory()->instructor()->create();

// Create instructor with specific specialization
User::factory()->instructor()->create([
    'specialization_id' => 1 // Category ID
]);
```

## API Changes

### Validation Rules

File: `Modules/Auth/app/Http/Requests/Concerns/HasAuthRequestRules.php`

**POST /users (Create User):**
```php
'specialization_id' => [
    'nullable',
    'required_if:role,Instructor',
    'integer',
    'exists:categories,id',
]
```

**Validation Logic:**
- `nullable`: Field is optional for non-Instructor roles
- `required_if:role,Instructor`: Required when role is Instructor
- `integer`: Must be integer type
- `exists:categories,id`: Must exist in categories table (id column)

### API Response

File: `Modules/Auth/app/Http/Resources/UserIndexResource.php`

**GET /users Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "username": "john.doe",
      "avatar_url": "https://...",
      "status": "active",
      "account_status": "active",
      "specialization": {
        "id": 3,
        "name": "Pengembangan Web",
        "value": "web-development"
      },
      "created_at": "2026-03-03T10:00:00+00:00",
      "email_verified_at": "2026-03-03T10:00:00+00:00",
      "role_names": ["Instructor"]
    }
  ]
}
```

**Field Behavior:**
- Returns `null` for non-Instructor users or instructors without specialization
- Returns object with `id`, `name`, `value` for instructors with specialization
- Automatically eager loads specialization relationship

## Usage Examples

### Creating Instructor via API

**POST /users**

```json
{
  "name": "Jane Smith",
  "username": "jane.smith",
  "email": "jane@example.com",
  "role": "Instructor",
  "specialization_id": 3
}
```

**Response:**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "username": "jane.smith",
    "specialization": {
      "id": 3,
      "name": "Pengembangan Web",
      "value": "web-development"
    },
    "role_names": ["Instructor"]
  }
}
```

### Creating Student (No Specialization)

**POST /users**

```json
{
  "name": "Bob Johnson",
  "username": "bob.johnson",
  "email": "bob@example.com",
  "role": "Student"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 3,
    "name": "Bob Johnson",
    "email": "bob@example.com",
    "username": "bob.johnson",
    "specialization": null,
    "role_names": ["Student"]
  }
}
```

## Validation Errors

### Missing Specialization for Instructor

**Request:**
```json
{
  "name": "Test User",
  "username": "test.user",
  "email": "test@example.com",
  "role": "Instructor"
}
```

**Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "specialization_id": [
      "The specialization id field is required when role is Instructor."
    ]
  }
}
```

### Invalid Specialization ID

**Request:**
```json
{
  "name": "Test User",
  "username": "test.user",
  "email": "test@example.com",
  "role": "Instructor",
  "specialization_id": 9999
}
```

**Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "specialization_id": [
      "The selected specialization id is invalid."
    ]
  }
}
```

## Testing

### Manual Testing Steps

1. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

2. **Run CategorySeeder (Required):**
   ```bash
   php artisan db:seed --class=Modules\\Common\\Database\\Seeders\\CategorySeeder
   ```

3. **Run InstructorSpecializationSeeder:**
   ```bash
   php artisan db:seed --class=Modules\\Auth\\Database\\Seeders\\InstructorSpecializationSeeder
   ```

4. **Test GET /users with Instructor filter:**
   ```bash
   curl -X GET "http://localhost:8000/api/users?role=Instructor" \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

5. **Test POST /users with Instructor role:**
   ```bash
   curl -X POST "http://localhost:8000/api/users" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test Instructor",
       "username": "test.instructor",
       "email": "test.instructor@example.com",
       "role": "Instructor",
       "specialization_id": 3
     }'
   ```

## Notes

- Specialization is stored as foreign key to categories table
- More efficient and normalized database design
- Automatic eager loading of specialization relationship
- Returns full category object (id, name, value) in API response
- CategorySeeder must be run before InstructorSpecializationSeeder
- On category deletion, specialization_id is set to NULL (nullOnDelete)

## Migration Rollback

To rollback the migrations:

```bash
php artisan migrate:rollback --step=2
```

This will remove the `specialization_id` column and restore the old `specialization` column.

## Implementation Status

### ✅ Completed
1. Migration to change specialization from string to foreign key
2. User model updated with relationship and fillable field
3. Category model updated with inverse relationship
4. UserFactory updated to use category IDs
5. InstructorSpecializationSeeder updated to use category IDs
6. Validation rules updated to validate category ID
7. API resource updated to return category object
8. Eager loading added to UserFinder service
9. Documentation updated with new structure

### 🎯 Ready for Testing
- Run migrations
- Run CategorySeeder
- Run InstructorSpecializationSeeder
- Test API endpoints with category IDs
