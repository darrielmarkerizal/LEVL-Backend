# Testing dengan Pest

Semua test di project ini menggunakan [Pest PHP](https://pestphp.com/) sebagai testing framework.

## Setup Database Testing

Testing menggunakan **MySQL** (bukan SQLite) karena migration menggunakan syntax MySQL yang tidak kompatibel dengan SQLite.

### Prerequisites

1. **MySQL Server** harus sudah running
2. **User MySQL** dengan permission untuk create database
3. Database test akan dibuat otomatis saat test pertama kali dijalankan

### Konfigurasi Database Test

Edit `phpunit.xml` untuk mengatur kredensial MySQL sesuai environment Anda:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="3306"/>
<env name="DB_DATABASE" value="prep_lsp_test"/>
<env name="DB_USERNAME" value="root"/>
<env name="DB_PASSWORD" value=""/>
```

**Default values:**
- Host: `127.0.0.1`
- Port: `3306`
- Database: `prep_lsp_test` (akan dibuat otomatis)
- Username: `root`
- Password: `` (kosong)

### Membuat Database Test Manual (Optional)

Jika ingin membuat database secara manual:

```bash
mysql -u root -p
CREATE DATABASE prep_lsp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Troubleshooting

**Error: Access denied for user**
- Pastikan username dan password di `phpunit.xml` benar
- Pastikan user memiliki permission untuk create database

**Error: Unknown database 'prep_lsp_test'**
- Database akan dibuat otomatis saat test pertama kali dijalankan
- Atau buat manual dengan command di atas

## Cara Menjalankan Test

### Menjalankan semua test
```bash
php artisan test
```

### Menjalankan test unit saja
```bash
php artisan test --testsuite=Unit
# atau
php artisan test tests/Unit
```

### Menjalankan test feature saja
```bash
php artisan test --testsuite=Feature
# atau
php artisan test tests/Feature
```

### Menjalankan test di folder tertentu
```bash
php artisan test tests/Unit/Schemes
php artisan test tests/Unit/Enrollments
```

### Menjalankan test file tertentu
```bash
php artisan test tests/Unit/Schemes/ProgressionServiceTest.php
```

### Menjalankan test dengan filter
```bash
php artisan test --filter="enroll creates"
```

### Menjalankan test dengan coverage
```bash
php artisan test --coverage
```

## Struktur Test

### Format Pest Test

Semua test menggunakan format Pest:

```php
<?php

use Modules\Schemes\Services\TagService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = new TagService();
});

test('create generates slug automatically', function () {
    $tag = $this->service->create(['name' => 'Web Development']);
    
    expect($tag)->not->toBeNull();
    expect($tag->name)->toEqual('Web Development');
    expect($tag->slug)->toEqual('web-development');
});
```

### Key Differences dari PHPUnit

1. **Tidak perlu class**: Test langsung menggunakan function `test()`
2. **Expectations**: Menggunakan `expect()` instead of `$this->assert*()`
3. **Setup**: Menggunakan `beforeEach()` instead of `setUp()`
4. **Uses**: Menggunakan `uses()` untuk traits

## Helper Functions

Di `tests/Pest.php` sudah tersedia helper functions:

- `assertDatabaseHas(string $table, array $data)`: Assert database record exists
- `assertDatabaseMissing(string $table, array $data)`: Assert database record does not exist

## Contoh Assertions

### Basic Assertions
```php
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeNull();
expect($value)->not->toBeNull();
expect($value)->toEqual('expected');
expect($value)->toBeInt();
expect($value)->toBeString();
expect($value)->toBeArray();
```

### Collection Assertions
```php
expect($collection)->toHaveCount(3);
expect($collection)->toContain('value');
```

### Exception Assertions
```php
expect(fn () => $service->method())
    ->toThrow(ValidationException::class);
```

### Database Assertions
```php
assertDatabaseHas('users', ['email' => 'test@example.com']);
assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
```

## Test Organization

```
tests/
├── Pest.php              # Configuration & helpers
├── TestCase.php          # Base test case
├── Unit/                 # Unit tests
│   ├── Schemes/
│   ├── Enrollments/
│   ├── Learning/
│   ├── Gamification/
│   ├── Common/
│   ├── Operations/
│   ├── Assessments/
│   ├── Grading/
│   ├── Notifications/
│   ├── Middleware/
│   ├── Policies/
│   └── Authorization/
└── Feature/              # Feature tests
```

## Tips

1. **RefreshDatabase**: Gunakan `uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)` untuk reset database setiap test
2. **Mocking**: Gunakan `Event::fake()`, `Mail::fake()` untuk mock
3. **Factories**: Gunakan `Model::factory()->create()` untuk membuat test data
4. **beforeEach**: Gunakan untuk setup yang sama di semua test dalam file

## Resources

- [Pest Documentation](https://pestphp.com/docs)
- [Laravel Testing](https://laravel.com/docs/testing)

