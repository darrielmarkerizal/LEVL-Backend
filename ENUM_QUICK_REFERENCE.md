# PostgreSQL ENUM Types - Quick Reference

## All Available ENUM Types

### 1. `user_status`
```sql
'pending' | 'active' | 'inactive' | 'banned'
```
- **Table**: `users`
- **Column**: `status`
- **Default**: `'pending'::user_status`
- **PHP Enum**: Check `Modules\Auth\Enums\UserStatus` (if exists)

---

### 2. `course_type`
```sql
'okupasi' | 'kluster'
```
- **Table**: `courses`
- **Column**: `type`
- **Default**: `'okupasi'::course_type`
- **PHP Enum**: `Modules\Schemes\Enums\CourseType`

---

### 3. `level_tag`
```sql
'dasar' | 'menengah' | 'mahir'
```
- **Table**: `courses`
- **Column**: `level_tag`
- **Default**: `'dasar'::level_tag`
- **PHP Enum**: `Modules\Schemes\Enums\LevelTag`

---

### 4. `enrollment_type`
```sql
'auto_accept' | 'key_based' | 'approval'
```
- **Table**: `courses`
- **Column**: `enrollment_type`
- **Default**: `'auto_accept'::enrollment_type`
- **PHP Enum**: `Modules\Schemes\Enums\EnrollmentType`

---

### 5. `course_status`
```sql
'draft' | 'published' | 'archived'
```
- **Table**: `courses`
- **Column**: `status`
- **Default**: `'draft'::course_status`
- **PHP Enum**: `Modules\Schemes\Enums\CourseStatus`

---

### 6. `enrollment_status`
```sql
'pending' | 'active' | 'completed' | 'cancelled'
```
- **Table**: `enrollments`
- **Column**: `status`
- **Default**: `'active'::enrollment_status`
- **PHP Enum**: `Modules\Enrollments\Enums\EnrollmentStatus`

---

### 7. `assignment_status`
```sql
'draft' | 'published' | 'archived'
```
- **Table**: `assignments`
- **Column**: `status`
- **Default**: `'draft'::assignment_status`
- **PHP Enum**: `Modules\Learning\Enums\AssignmentStatus`

---

### 8. `review_mode`
```sql
'immediate' | 'manual' | 'deferred' | 'hidden'
```
- **Table**: `assignments`
- **Column**: `review_mode`
- **Default**: `'immediate'::review_mode`
- **PHP Enum**: `Modules\Learning\Enums\ReviewMode`

---

## Quick Commands

### List All ENUM Types
```bash
psql -U darrielmarkerizal -d "LEVL-DB" -c "\dT"
```

### View Specific ENUM
```bash
psql -U darrielmarkerizal -d "LEVL-DB" -c "\dT+ user_status"
```

### Check Table Column Type
```bash
psql -U darrielmarkerizal -d "LEVL-DB" -c "\d users"
```

### Query with ENUM
```sql
-- Correct
SELECT * FROM users WHERE status = 'active';
SELECT * FROM users WHERE status = 'active'::user_status;

-- Also works (Laravel handles this)
SELECT * FROM courses WHERE type = 'okupasi';
```

---

## Adding New ENUM Values

To add a new value to an existing ENUM, create a migration:

```php
// Add new value to user_status
DB::statement("ALTER TYPE user_status ADD VALUE 'suspended' AFTER 'inactive'");

// Note: Cannot remove values without recreating the ENUM
```

---

## API Usage

All ENUMs accept and return string values in JSON:

```json
{
  "status": "active",
  "type": "okupasi",
  "level_tag": "dasar",
  "enrollment_type": "auto_accept"
}
```

Laravel automatically converts between strings and ENUMs.

---

## PHP Enum Usage

```php
use Modules\Schemes\Enums\CourseStatus;

// Create
$course->status = CourseStatus::Published;

// Compare
if ($course->status === CourseStatus::Published) {
    // ...
}

// Get value
$statusValue = $course->status->value; // 'published'

// Get all values
$allStatuses = CourseStatus::values(); // ['draft', 'published', 'archived']

// Validation rule
'status' => ['required', Rule::enum(CourseStatus::class)]
```

---

## Troubleshooting

### Error: invalid input value for enum
**Cause**: Trying to insert a value not in the ENUM definition  
**Solution**: Check the ENUM values with `\dT+ enum_name`

### Error: column "status" is of type user_status but expression is of type character varying
**Cause**: Explicit type casting needed  
**Solution**: Use `'value'::enum_type` or let Laravel handle it

### Need to change ENUM values
**Solution**: Create a migration to:
1. Add new column with new ENUM
2. Copy data
3. Drop old column
4. Rename new column

---

## Performance Notes

- ENUMs are stored as 4-byte integers internally
- Much faster than VARCHAR comparisons
- Excellent for indexed columns
- Minimal storage overhead

---

## Best Practices

1. ✅ Always use PHP Enum classes in code
2. ✅ Let Laravel handle string ↔ enum conversion
3. ✅ Use `Rule::enum()` for validation
4. ✅ Document all ENUM values
5. ❌ Don't use raw strings in queries
6. ❌ Don't add too many values (keep under 10)
7. ❌ Don't use ENUMs for frequently changing values
