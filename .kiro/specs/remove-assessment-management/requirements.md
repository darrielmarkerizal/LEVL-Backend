# Requirements Document

## Introduction

Dokumen ini mendefinisikan requirements untuk menghapus semua referensi ke fitur **Assessment** (`/api/v1/assessments/*`) dari sistem Learning Support Platform. Fitur assessment (asesmen/ujian sertifikasi) dihilangkan dari scope aplikasi.

**PENTING:** 
- **Assignment** (tugas dalam lesson) **TETAP ADA** - tidak dihapus
- Yang dihapus adalah **Assessment** (asesmen/ujian sertifikasi) termasuk exercises, attempts, questions, dll.

## Glossary

- **Assessment**: Asesmen/ujian sertifikasi yang terdiri dari exercises dan questions (DIHAPUS)
- **Exercise**: Latihan soal dalam assessment (DIHAPUS)
- **Attempt**: Percobaan pengerjaan exercise (DIHAPUS)
- **Question**: Soal dalam exercise (DIHAPUS)
- **Assignment**: Tugas yang diberikan dalam lesson (TETAP ADA)
- **OpenAPI Spec**: Dokumentasi API yang di-generate oleh OpenApiGeneratorService

## Requirements

### Requirement 1: Hapus Assessment dari OpenAPI Documentation

**User Story:** Sebagai developer, saya ingin menghapus semua dokumentasi Assessment endpoints dari OpenAPI spec, sehingga dokumentasi API tidak menampilkan fitur yang tidak ada.

#### Acceptance Criteria

1. WHEN the OpenAPI spec is generated THEN the spec SHALL NOT contain `/v1/assessments/{assessment}/register` endpoint
2. WHEN the OpenAPI spec is generated THEN the spec SHALL NOT contain `/v1/assessments/{assessment}/prerequisites` endpoint
3. WHEN the OpenAPI spec is generated THEN the spec SHALL NOT contain `/v1/assessments/{assessment}/slots` endpoint
4. WHEN the OpenAPI spec is generated THEN the spec SHALL NOT contain `/v1/assessment-registrations/{registration}` endpoint
5. WHEN the OpenAPI spec is generated THEN the spec SHALL NOT contain `/v1/assessments/exercises` endpoints
6. WHEN the OpenAPI spec is generated THEN the spec SHALL NOT contain `/v1/assessments/attempts` endpoints

### Requirement 2: Hapus Assessment Examples dari OpenApiGeneratorService

**User Story:** Sebagai developer, saya ingin menghapus semua Assessment-related examples dari OpenApiGeneratorService, sehingga tidak ada dead code.

#### Acceptance Criteria

1. WHEN OpenApiGeneratorService is loaded THEN the endpointExamples property SHALL NOT contain any assessment-related entries
2. WHEN OpenApiGeneratorService is loaded THEN the summaryOverrides property SHALL NOT contain any assessment-related entries
3. WHEN OpenApiGeneratorService is loaded THEN the descriptionOverrides property SHALL NOT contain any assessment-related entries

### Requirement 3: Hapus Assessment dari Requirements dan Design Documents

**User Story:** Sebagai developer, saya ingin menghapus semua referensi Assessment dari spec documents, sehingga dokumentasi tetap akurat.

#### Acceptance Criteria

1. WHEN reviewing openapi-scalar-documentation requirements THEN the document SHALL NOT contain Requirement 4 (Assessment Registration Documentation)
2. WHEN reviewing openapi-scalar-documentation requirements THEN the document SHALL NOT contain Requirements 20-23 (Assessment Exercises, Questions, Attempts, Grading)
3. WHEN reviewing openapi-scalar-documentation design THEN the document SHALL NOT contain Assessment Module Endpoints section
4. WHEN reviewing openapi-scalar-documentation tasks THEN the document SHALL NOT contain assessment-related tasks

### Requirement 4: Bersihkan Assessment dari Notification Enums

**User Story:** Sebagai developer, saya ingin menghapus Assessment dari notification types jika tidak diperlukan, sehingga enum values tetap relevan.

#### Acceptance Criteria

1. WHEN the system is deployed THEN the NotificationType enum MAY retain 'assessment' value for backward compatibility OR remove it if no data exists
2. WHEN the system is deployed THEN the notification_preferences migration SHALL handle assessment category appropriately
