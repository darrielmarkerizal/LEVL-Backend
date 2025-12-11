<?php

use App\Support\CodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Schemes\Models\Course;

uses(RefreshDatabase::class);

describe('CodeGenerator', function () {
    describe('generate', function () {
        it('generates unique code with prefix', function () {
            $code = CodeGenerator::generate('TEST-', 6, Course::class);

            expect($code)->toStartWith('TEST-');
            expect($code)->toHaveLength(11); // TEST- (5) + 6 chars
            expect($code)->toMatch('/^TEST-[A-Z0-9]{6}$/');
        });

        it('ensures uniqueness by checking database', function () {
            // Create a course with specific code
            Course::factory()->create(['code' => 'CRS-ABC123']);

            // Generate new code (should not be ABC123)
            $code = CodeGenerator::generate('CRS-', 6, Course::class);

            expect($code)->toStartWith('CRS-');
            expect($code)->not->toBe('CRS-ABC123');
        });

        it('throws exception for non-existent model class', function () {
            expect(fn () => CodeGenerator::generate('TEST-', 6, 'NonExistentModel'))
                ->toThrow(InvalidArgumentException::class, 'does not exist');
        });

        it('throws exception for non-model class', function () {
            expect(fn () => CodeGenerator::generate('TEST-', 6, \stdClass::class))
                ->toThrow(InvalidArgumentException::class, 'must extend');
        });
    });

    describe('generateNumeric', function () {
        it('generates numeric code with prefix', function () {
            $code = CodeGenerator::generateNumeric('INV-', 6, Course::class);

            expect($code)->toStartWith('INV-');
            expect($code)->toMatch('/^INV-\d{6}$/');
        });

        it('pads numbers with leading zeros', function () {
            $code = CodeGenerator::generateNumeric('ORD-', 8, Course::class);

            expect($code)->toMatch('/^ORD-\d{8}$/');
        });

        it('ensures uniqueness', function () {
            Course::factory()->create(['code' => 'INV-123456']);

            $code = CodeGenerator::generateNumeric('INV-', 6, Course::class);

            expect($code)->toStartWith('INV-');
            expect($code)->not->toBe('INV-123456');
        });
    });

    describe('generateSequential', function () {
        it('generates first sequential code', function () {
            $code = CodeGenerator::generateSequential('SEQ-', Course::class, 6);

            expect($code)->toBe('SEQ-000001');
        });

        it('increments from last code', function () {
            Course::factory()->create(['code' => 'SEQ-000005']);
            Course::factory()->create(['code' => 'SEQ-000010']);

            $code = CodeGenerator::generateSequential('SEQ-', Course::class, 6);

            expect($code)->toBe('SEQ-000011');
        });

        it('handles custom padding', function () {
            $code = CodeGenerator::generateSequential('NUM-', Course::class, 4);

            expect($code)->toBe('NUM-0001');
        });

        it('only considers codes with same prefix', function () {
            Course::factory()->create(['code' => 'OTHER-000050']);
            Course::factory()->create(['code' => 'SEQ-000005']);

            $code = CodeGenerator::generateSequential('SEQ-', Course::class, 6);

            expect($code)->toBe('SEQ-000006');
        });
    });

    describe('generateWithDate', function () {
        it('generates code with date prefix', function () {
            $code = CodeGenerator::generateWithDate('INV-', 'Ymd', 4, Course::class);

            $today = date('Ymd');
            expect($code)->toStartWith("INV-{$today}-");
            expect($code)->toMatch("/^INV-\d{8}-[A-Z0-9]{4}$/");
        });

        it('uses custom date format', function () {
            $code = CodeGenerator::generateWithDate('REF-', 'Y-m', 6, Course::class);

            $yearMonth = date('Y-m');
            expect($code)->toStartWith("REF-{$yearMonth}-");
        });

        it('works without model class', function () {
            $code = CodeGenerator::generateWithDate('TMP-', 'Ymd', 4);

            $today = date('Ymd');
            expect($code)->toStartWith("TMP-{$today}-");
        });

        it('ensures uniqueness when model provided', function () {
            $today = date('Ymd');
            Course::factory()->create(['code' => "DATE-{$today}-ABCD"]);

            $code = CodeGenerator::generateWithDate('DATE-', 'Ymd', 4, Course::class);

            expect($code)->toStartWith("DATE-{$today}-");
            expect($code)->not->toBe("DATE-{$today}-ABCD");
        });
    });
});
