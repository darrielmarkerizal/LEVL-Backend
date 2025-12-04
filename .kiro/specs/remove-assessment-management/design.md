# Design Document

## Overview

Dokumen ini menjelaskan desain teknis untuk menghapus semua referensi ke fitur **Assessment** (`/api/v1/assessments/*`) dari sistem. Assessment adalah fitur asesmen/ujian sertifikasi yang tidak lagi diperlukan dalam scope aplikasi.

**Scope Penghapusan:**
- Dokumentasi Assessment endpoints dari OpenAPI spec
- Assessment examples dari OpenApiGeneratorService
- Assessment-related requirements dari spec documents

**Yang TETAP ADA:**
- Assignment (tugas dalam lesson)
- Grading Module

## Architecture

### Current State

```
┌─────────────────────────────────────────────────────────────────┐
│                    OpenAPI Documentation                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  OpenApiGeneratorService                                        │
│       │                                                          │
│       ├── endpointExamples (contains assessment entries)        │
│       ├── summaryOverrides (contains assessment entries)        │
│       └── descriptionOverrides (contains assessment entries)    │
│                                                                  │
│  Spec Documents                                                  │
│       │                                                          │
│       ├── requirements.md (Req 4, 20-23 about assessment)       │
│       ├── design.md (Assessment Module Endpoints section)       │
│       └── tasks.md (assessment-related tasks)                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Target State

```
┌─────────────────────────────────────────────────────────────────┐
│                    OpenAPI Documentation                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  OpenApiGeneratorService                                        │
│       │                                                          │
│       ├── endpointExamples (NO assessment entries)              │
│       ├── summaryOverrides (NO assessment entries)              │
│       └── descriptionOverrides (NO assessment entries)          │
│                                                                  │
│  Spec Documents                                                  │
│       │                                                          │
│       ├── requirements.md (NO assessment requirements)          │
│       ├── design.md (NO Assessment Module section)              │
│       └── tasks.md (NO assessment tasks)                        │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. OpenApiGeneratorService Cleanup

File: `app/Services/OpenApiGeneratorService.php`

**Entries to Remove from endpointExamples:**
```php
// REMOVE these entries:
'v1/assessments/{assessment}/register' => [...]
'v1/assessments/{assessment}/prerequisites' => [...]
'v1/assessments/{assessment}/slots' => [...]
'v1/assessments/exercises' => [...]
'v1/assessments/exercises/{exercise}' => [...]
'v1/assessments/exercises/{exercise}/publish' => [...]
'v1/assessments/exercises/{exercise}/questions' => [...]
'v1/assessments/questions/{question}' => [...]
'v1/assessments/questions/{question}/options' => [...]
'v1/assessments/options/{option}' => [...]
'v1/assessments/exercises/{exercise}/attempts' => [...]
'v1/assessments/attempts' => [...]
'v1/assessments/attempts/{attempt}' => [...]
'v1/assessments/attempts/{attempt}/answers' => [...]
'v1/assessments/attempts/{attempt}/complete' => [...]
'v1/assessments/answers/{answer}/feedback' => [...]
'v1/assessments/attempts/{attempt}/score' => [...]
'v1/assessment-registrations/{registration}' => [...]
```

**Entries to Remove from summaryOverrides:**
```php
// REMOVE all entries with 'assessment' in the key
'v1/assessments/{assessment}/register' => [...]
'v1/assessments/{assessment}/prerequisites' => [...]
'v1/assessments/{assessment}/slots' => [...]
// ... etc
```

**Entries to Remove from descriptionOverrides:**
```php
// REMOVE all entries with 'assessment' in the key
```

### 2. Spec Documents Cleanup

#### requirements.md Updates

File: `.kiro/specs/openapi-scalar-documentation/requirements.md`

**Requirements to Remove:**
- Requirement 4: Assessment Registration Documentation
- Requirement 20: Assessment Exercises Documentation
- Requirement 21: Assessment Questions Documentation
- Requirement 22: Assessment Attempts Documentation
- Requirement 23: Assessment Grading Documentation

#### design.md Updates

File: `.kiro/specs/openapi-scalar-documentation/design.md`

**Sections to Remove:**
- "Assessment Module Endpoints" section (lines ~706-785)
- All `v1/assessments/*` entries in endpoint examples

#### tasks.md Updates

File: `.kiro/specs/openapi-scalar-documentation/tasks.md`

**Tasks to Remove:**
- Task 15: Add Assessment Module examples (15.1-15.5)
- Any references to assessment in other tasks

## Data Models

Tidak ada perubahan data model karena ini hanya cleanup dokumentasi.

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: No Assessment Entries in OpenApiGeneratorService
*For any* key in the endpointExamples, summaryOverrides, or descriptionOverrides properties of OpenApiGeneratorService, the key SHALL NOT contain the substring 'assessment'.
**Validates: Requirements 2.1, 2.2, 2.3**

## Error Handling

Tidak ada error handling khusus karena ini adalah cleanup task.

## Testing Strategy

### Unit Testing
- Verify OpenApiGeneratorService properties tidak mengandung assessment entries
- Verify generated OpenAPI spec tidak mengandung assessment paths

### Property-Based Testing
- Menggunakan PHPUnit untuk test Property 1
- Test bahwa semua keys di endpointExamples tidak mengandung 'assessment'

### Manual Verification
- Review spec documents untuk memastikan tidak ada assessment references
- Regenerate OpenAPI spec dan verify di Scalar UI

## Implementation Notes

### Files to Modify

1. `app/Services/OpenApiGeneratorService.php`
   - Remove assessment entries from endpointExamples
   - Remove assessment entries from summaryOverrides
   - Remove assessment entries from descriptionOverrides

2. `.kiro/specs/openapi-scalar-documentation/requirements.md`
   - Remove Requirements 4, 20-23

3. `.kiro/specs/openapi-scalar-documentation/design.md`
   - Remove Assessment Module Endpoints section

4. `.kiro/specs/openapi-scalar-documentation/tasks.md`
   - Remove assessment-related tasks

### Verification Steps

1. Run `php artisan openapi:generate` untuk regenerate spec
2. Access `/scalar` untuk verify tidak ada assessment endpoints
3. Run tests untuk verify Property 1
