# Implementation Plan - Laravel Comprehensive Audit

## Priority To-Do List

### [HIGH] Critical Security & Consistency Issues

- [x] 1. Fix Authentication Middleware Inconsistency
  - [x] 1.1 Update Operations module to use `auth:api` instead of `auth:sanctum`
    - File: `Modules/Operations/routes/api.php`
    - Change `auth:sanctum` to `auth:api`
    - _Requirements: 3.1, 7.1_

  - [x] 1.2 Audit all modules for middleware consistency
    - Verify all protected routes use `auth:api`
    - _Requirements: 7.1_

- [x] 2. Remove or Protect Debug Endpoints
  - [x] 2.1 Remove FileTestController routes from production
    - File: `Modules/Operations/routes/api.php`
    - Wrap with environment check or remove entirely
    - _Requirements: 7.1, 8.1_

  - [x] 2.2 Add environment-based route protection
    - Only expose debug routes in local/testing environments
    - _Requirements: 7.1_

- [ ] 3. Fix Database Normalization Issues
  - [ ] 3.1 Create migration to fix courses.category
    - Add foreign key to categories table
    - Migrate existing data
    - _Requirements: 5.1_

  - [ ] 3.2 Remove redundant tags_json column
    - Ensure all data is in course_tag pivot table
    - Drop tags_json column
    - _Requirements: 5.1_

  - [ ] 3.3 Clarify users.status vs users.account_status
    - Document the difference or consolidate
    - _Requirements: 5.1_

### [HIGH] Database Performance

- [ ] 4. Add Missing Database Indexes
  - [ ] 4.1 Create migration for missing indexes
    - Add index on `users.email_verified_at`
    - Add index on `courses.published_at`
    - Add index on `courses.category`
    - Add index on `enrollments.enrolled_at`
    - _Requirements: 5.3, 6.4_

  - [ ]* 4.2 Write property test for index coverage
    - **Property 6: Index Coverage**
    - **Validates: Requirements 5.3, 6.4**

### [MEDIUM] Testing Improvements

- [ ] 5. Add Missing Test Cases for Auth Module
  - [ ] 5.1 Add login throttling tests
    - Test rate limiting after multiple failed attempts
    - _Requirements: 10.2_

  - [ ] 5.2 Add refresh token expiry tests
    - Test expired refresh token handling
    - Test idle timeout
    - _Requirements: 10.2_

  - [ ] 5.3 Add password validation edge case tests
    - Test minimum length, special characters, etc.
    - _Requirements: 10.3_

  - [ ]* 5.4 Write property test for API response consistency
    - **Property 1: API Response Consistency**
    - **Validates: Requirements 4.2**

- [ ] 6. Add Missing Test Cases for Assessments Module
  - [ ] 6.1 Add attempt time limit tests
    - Test enforcement of time limits
    - _Requirements: 10.2_

  - [ ] 6.2 Add multiple attempts limit tests
    - Test max attempts per exercise
    - _Requirements: 10.2_

  - [ ] 6.3 Add auto-grading tests
    - Test automatic grading for multiple choice
    - _Requirements: 10.1_

- [ ] 7. Add Cross-Resource Authorization Tests
  - [ ] 7.1 Add tests for user accessing other user's resources
    - Test 403 responses for unauthorized access
    - _Requirements: 10.2_

  - [ ] 7.2 Add tests for role-based access
    - Test all role combinations
    - _Requirements: 10.2_

  - [ ]* 7.3 Write property test for authorization
    - **Property 7: Test Coverage for Endpoints**
    - **Validates: Requirements 10.1, 10.2**

- [ ] 8. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

### [MEDIUM] Documentation Sync

- [ ] 9. Update OpenAPI Documentation
  - [ ] 9.1 Audit routes vs documentation
    - Compare all routes in code with OpenAPI spec
    - List missing endpoints
    - _Requirements: 11.1, 11.2_

  - [ ] 9.2 Add missing endpoint documentation
    - Document new endpoints not in spec
    - _Requirements: 11.2_

  - [ ] 9.3 Update response schemas
    - Ensure schemas match actual responses
    - _Requirements: 11.3_

  - [ ]* 9.4 Write property test for documentation sync
    - **Property 8: Documentation Sync**
    - **Validates: Requirements 11.1, 11.2**

### [MEDIUM] Code Quality Improvements

- [ ] 10. Standardize Enum Values
  - [ ] 10.1 Audit all enum definitions
    - List all enums and their value formats
    - _Requirements: 2.4_

  - [ ] 10.2 Standardize to lowercase
    - Update enum values to consistent format
    - _Requirements: 2.4_

- [ ] 11. Review and Refactor Large Controllers
  - [ ] 11.1 Identify controllers with >200 lines
    - List potential god-classes
    - _Requirements: 1.3_

  - [ ] 11.2 Extract business logic to services
    - Move complex logic from controllers to services
    - _Requirements: 1.4_

### [LOW] Production Optimization

- [ ] 12. Configure Production Cache/Queue
  - [ ] 12.1 Document Redis configuration
    - Add Redis config to .env.example
    - _Requirements: 8.4_

  - [ ] 12.2 Create production deployment checklist
    - Document all production settings
    - _Requirements: 8.1, 8.2, 8.3_

- [ ] 13. Add Query Optimization
  - [ ] 13.1 Audit N+1 queries
    - Use Laravel Debugbar or Telescope
    - _Requirements: 6.1_

  - [ ] 13.2 Add eager loading where missing
    - Update queries to use `with()`
    - _Requirements: 6.1_

  - [ ]* 13.3 Write property test for eager loading
    - **Property 5: Eager Loading for Relations**
    - **Validates: Requirements 6.1**

- [ ] 14. Final Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

---

## Test Cases to Add

### Auth Module Tests

```php
// tests/Feature/Api/AuthThrottlingTest.php
it('blocks login after 5 failed attempts', function () {...});
it('resets throttle after successful login', function () {...});
it('returns 429 when rate limited', function () {...});

// tests/Feature/Api/RefreshTokenTest.php
it('fails with expired refresh token', function () {...});
it('fails with revoked refresh token', function () {...});
it('rotates refresh token on use', function () {...});

// tests/Feature/Api/PasswordValidationTest.php
it('fails when password is too short', function () {...});
it('fails when password has no uppercase', function () {...});
it('fails when password has no number', function () {...});
```

### Assessments Module Tests

```php
// tests/Feature/Api/AttemptTimeLimitTest.php
it('auto-completes attempt when time limit exceeded', function () {...});
it('prevents answer submission after time limit', function () {...});

// tests/Feature/Api/AttemptLimitTest.php
it('prevents starting attempt when max attempts reached', function () {...});
it('allows retry when attempts remaining', function () {...});

// tests/Feature/Api/AutoGradingTest.php
it('auto-grades multiple choice questions', function () {...});
it('calculates correct score for mixed answers', function () {...});
```

### Authorization Tests

```php
// tests/Feature/Api/CrossResourceAuthorizationTest.php
it('prevents user from viewing other user enrollment', function () {...});
it('prevents student from grading submissions', function () {...});
it('prevents instructor from accessing other course', function () {...});
```

---

## Migration Scripts

### Add Missing Indexes

```php
// database/migrations/2025_12_04_000001_add_missing_indexes.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('email_verified_at');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index('published_at');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('enrolled_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email_verified_at']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['published_at']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['enrolled_at']);
        });
    }
};
```

### Fix Category Foreign Key

```php
// database/migrations/2025_12_04_000002_fix_courses_category_fk.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add category_id column
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('level_tag')
                ->constrained('categories')->nullOnDelete();
        });

        // Migrate existing data
        DB::statement('
            UPDATE courses c
            SET category_id = (
                SELECT id FROM categories WHERE value = c.category LIMIT 1
            )
            WHERE c.category IS NOT NULL
        ');

        // Drop old category column
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('level_tag');
        });

        DB::statement('
            UPDATE courses c
            SET category = (
                SELECT value FROM categories WHERE id = c.category_id LIMIT 1
            )
            WHERE c.category_id IS NOT NULL
        ');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
```
