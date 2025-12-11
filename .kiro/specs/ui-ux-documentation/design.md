# Design Document - UI/UX Specification Documentation

## Overview

This design document outlines the comprehensive approach for creating UI/UX specification documentation based on the existing Laravel backend API. The backend consists of 12 modular features (Auth, Schemes, Enrollments, Learning, Gamification, Forums, Content, Notifications, Grading, Search, Operations, Common) with over 40 controllers and 200+ endpoints.

The documentation will be generated through systematic analysis of:
- Route definitions (api.php files)
- Controller methods and their PHPDoc annotations
- FormRequest validation rules
- DTO (Data Transfer Object) definitions
- API Resource transformers
- Policy authorization rules
- Database models and relationships

The output will be a structured markdown document with tables, JSON examples, and user flow diagrams that designers can use directly to create UI mockups and prototypes.

## Architecture

### Documentation Generation Pipeline

```
┌─────────────────────────────────────────────────────────────────┐
│                   Documentation Generation Flow                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. Module Discovery                                             │
│     └─> Scan Modules/* directories                              │
│         └─> Identify all modules with routes/api.php            │
│                                                                  │
│  2. Endpoint Extraction                                          │
│     └─> Parse routes/api.php files                              │
│         └─> Extract: Method, URI, Controller, Middleware        │
│                                                                  │
│  3. Input Specification Analysis                                 │
│     └─> Locate FormRequest classes                              │
│         └─> Extract validation rules                            │
│         └─> Parse rule traits and concerns                      │
│         └─> Identify DTO classes                                │
│         └─> Extract field types and constraints                 │
│                                                                  │
│  4. Output Specification Analysis                                │
│     └─> Locate API Resource classes                             │
│         └─> Extract response structure                          │
│         └─> Identify relationships and includes                 │
│         └─> Parse controller return statements                  │
│                                                                  │
│  5. Authorization Analysis                                       │
│     └─> Parse middleware definitions                            │
│         └─> Extract policy rules                                │
│         └─> Identify role requirements                          │
│                                                                  │
│  6. Documentation Compilation                                    │
│     └─> Generate feature tables                                 │
│         └─> Create input/output specifications                  │
│         └─> Build user flow diagrams                            │
│         └─> Generate JSON examples                              │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Module Organization

The backend is organized into 12 functional modules:

1. **Auth** - Authentication, user management, profile
2. **Schemes** - Courses, units, lessons, progress tracking
3. **Enrollments** - Course enrollment management
4. **Learning** - Assignments, submissions, learning materials
5. **Gamification** - XP, badges, challenges, leaderboards
6. **Forums** - Discussion threads, replies, reactions
7. **Content** - News, announcements, content management
8. **Notifications** - Push notifications, preferences
9. **Grading** - Assignment grading and feedback
10. **Search** - Global search functionality
11. **Operations** - Admin operations and reports
12. **Common** - Master data, categories, shared resources

## Components and Interfaces

### 1. Module Analyzer Component

**Purpose**: Discovers and catalogs all modules in the system

**Inputs**:
- Modules directory path
- Module structure patterns

**Outputs**:
- List of module names
- Module metadata (description, routes file, controllers directory)

**Methods**:
- `discoverModules()`: Scans Modules directory
- `getModuleMetadata(moduleName)`: Extracts module.json information
- `validateModuleStructure(moduleName)`: Ensures required files exist

### 2. Route Parser Component

**Purpose**: Extracts endpoint definitions from route files

**Inputs**:
- Route file path (routes/api.php)
- Module name

**Outputs**:
- Array of endpoint definitions with:
  - HTTP method (GET, POST, PUT, DELETE)
  - URI pattern
  - Controller class and method
  - Middleware stack
  - Route name
  - Parameter bindings

**Methods**:
- `parseRouteFile(filePath)`: Parses PHP route definitions
- `extractMiddleware(route)`: Identifies auth, role, throttle middleware
- `extractParameters(uri)`: Identifies route parameters
- `groupEndpointsByController()`: Organizes endpoints by controller

### 3. Validation Rule Extractor Component

**Purpose**: Extracts input validation rules from FormRequest classes

**Inputs**:
- FormRequest class path
- Request class name

**Outputs**:
- Field definitions with:
  - Field name
  - Data type (string, integer, boolean, array, file)
  - Required/optional status
  - Validation rules (max, min, email, regex, unique, exists, etc.)
  - Custom error messages
  - Conditional validation logic

**Methods**:
- `extractRules(requestClass)`: Gets rules() method return value
- `parseRuleString(ruleString)`: Converts Laravel rule to structured format
- `extractMessages(requestClass)`: Gets custom validation messages
- `resolveTraitRules(requestClass)`: Follows trait inheritance
- `identifyFieldType(rules)`: Determines data type from rules

### 4. DTO Analyzer Component

**Purpose**: Extracts field definitions from DTO classes

**Inputs**:
- DTO class path
- DTO class name

**Outputs**:
- Field definitions with:
  - Property name
  - Type hint
  - Default value
  - Nullable status
  - Validation attributes

**Methods**:
- `extractProperties(dtoClass)`: Gets constructor parameters
- `parseTypeHint(parameter)`: Extracts type information
- `extractValidationAttributes(property)`: Gets Spatie Data attributes

### 5. Response Structure Analyzer Component

**Purpose**: Extracts response structure from API Resource classes

**Inputs**:
- Resource class path
- Resource class name

**Outputs**:
- Response field definitions with:
  - Field name
  - Data type
  - Source (model attribute, computed, relationship)
  - Conditional inclusion logic
  - Nested resources

**Methods**:
- `extractToArrayMethod(resourceClass)`: Parses toArray() method
- `identifyRelationships(resource)`: Finds included relationships
- `extractConditionalFields(resource)`: Identifies when() conditions
- `buildResponseExample(resource, model)`: Generates sample JSON

### 6. Authorization Analyzer Component

**Purpose**: Extracts authorization requirements from policies and middleware

**Inputs**:
- Policy class path
- Route middleware definitions

**Outputs**:
- Authorization requirements with:
  - Required authentication (yes/no)
  - Required roles (Superadmin, Admin, Instructor, Student)
  - Policy method name
  - Authorization conditions

**Methods**:
- `extractPolicyRules(policyClass)`: Parses policy methods
- `extractRoleMiddleware(route)`: Identifies role requirements
- `extractThrottleRules(route)`: Identifies rate limiting

### 7. Documentation Generator Component

**Purpose**: Compiles all extracted data into formatted documentation

**Inputs**:
- Module data
- Endpoint data
- Validation data
- Response data
- Authorization data

**Outputs**:
- Markdown documentation with:
  - Module overview tables
  - Feature specification tables
  - Input field tables
  - Output JSON examples
  - User flow diagrams
  - Alternative scenario documentation

**Methods**:
- `generateModuleSection(module)`: Creates module documentation
- `generateFeatureTable(endpoints)`: Creates feature overview table
- `generateInputTable(fields)`: Creates input specification table
- `generateOutputExample(resource)`: Creates JSON response example
- `generateUserFlow(feature)`: Creates step-by-step flow
- `generateAlternativeFlows(feature)`: Documents error scenarios

## Data Models

### Module Model

```typescript
interface Module {
  name: string;                    // e.g., "Auth", "Schemes"
  displayName: string;             // e.g., "Authentication & Users"
  description: string;             // Module purpose
  routeFile: string;               // Path to routes/api.php
  controllersPath: string;         // Path to controllers directory
  features: Feature[];             // List of features in module
}
```

### Feature Model

```typescript
interface Feature {
  name: string;                    // e.g., "User Registration"
  description: string;             // Feature purpose
  endpoints: Endpoint[];           // Related endpoints
  userFlows: UserFlow[];           // Interaction flows
  alternativeFlows: AlternativeFlow[]; // Error/edge case flows
}
```

### Endpoint Model

```typescript
interface Endpoint {
  method: HttpMethod;              // GET, POST, PUT, DELETE
  uri: string;                     // e.g., "/api/v1/auth/register"
  controller: string;              // Controller class name
  controllerMethod: string;        // Method name
  routeName: string;               // Laravel route name
  middleware: string[];            // Applied middleware
  parameters: Parameter[];         // Route parameters
  authentication: AuthRequirement; // Auth requirements
  inputSpec: InputSpecification;   // Input fields and validation
  outputSpec: OutputSpecification; // Response structure
  rateLimit: RateLimit;           // Rate limiting rules
}
```

### InputSpecification Model

```typescript
interface InputSpecification {
  fields: InputField[];            // All input fields
  requestClass: string;            // FormRequest class name
  dtoClass?: string;               // DTO class name if exists
}

interface InputField {
  name: string;                    // Field name
  type: DataType;                  // string, integer, boolean, array, file
  required: boolean;               // Required or optional
  validationRules: ValidationRule[]; // All validation rules
  defaultValue?: any;              // Default value if exists
  description: string;             // Field purpose
  exampleValue: any;               // Example value
  conditionalLogic?: string;       // Conditional validation
  enumValues?: EnumValue[];        // If field accepts enum values
}

interface ValidationRule {
  rule: string;                    // e.g., "max", "email", "regex"
  parameters?: any[];              // Rule parameters
  message: string;                 // Custom error message
}

interface EnumValue {
  value: string;                   // Enum value
  label: string;                   // Display label
}
```

### OutputSpecification Model

```typescript
interface OutputSpecification {
  successResponse: ResponseStructure;     // 200/201 response
  errorResponses: ErrorResponse[];        // Error scenarios
  resourceClass?: string;                 // API Resource class
  paginationSupport: boolean;             // Supports pagination
  includeOptions?: string[];              // Available includes
}

interface ResponseStructure {
  statusCode: number;              // HTTP status code
  structure: ResponseField[];      // Response fields
  exampleJson: string;             // JSON example
}

interface ResponseField {
  name: string;                    // Field name
  type: DataType;                  // Data type
  description: string;             // Field purpose
  nullable: boolean;               // Can be null
  conditional?: string;            // Condition for inclusion
  nested?: ResponseField[];        // Nested fields
}

interface ErrorResponse {
  statusCode: number;              // HTTP status code
  scenario: string;                // Error scenario name
  exampleJson: string;             // JSON example
  uiGuidance: string;              // How UI should handle
}
```

### AuthRequirement Model

```typescript
interface AuthRequirement {
  required: boolean;               // Authentication required
  roles?: string[];                // Required roles
  policyMethod?: string;           // Policy method name
  policyConditions?: string;       // Authorization conditions
}
```

### UserFlow Model

```typescript
interface UserFlow {
  name: string;                    // Flow name
  role: string;                    // User role for this flow
  steps: FlowStep[];               // Sequential steps
  successOutcome: string;          // Expected result
}

interface FlowStep {
  stepNumber: number;              // Step sequence
  action: string;                  // User action
  endpoint?: string;               // API endpoint called
  inputData?: Record<string, any>; // Data sent
  validation?: string;             // Validation performed
  uiState: string;                 // UI state during step
  nextStep: string;                // Next step or decision
}
```

### AlternativeFlow Model

```typescript
interface AlternativeFlow {
  scenario: string;                // Scenario name
  trigger: string;                 // What triggers this flow
  steps: FlowStep[];               // Steps in alternative flow
  uiGuidance: string;              // How UI should handle
  recoveryOptions?: string[];      // How user can recover
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property Reflection

After reviewing all testable criteria from the prework, I've identified several areas where properties can be consolidated:

**Consolidation Opportunities:**

1. **Field Documentation Properties (2.2, 2.3, 2.4, 2.5, 2.6, 2.7)** - These can be combined into a single comprehensive property about complete field documentation
2. **Response Documentation Properties (3.1, 3.4, 3.5, 3.6)** - These can be combined into a property about complete response structure documentation
3. **Flow Documentation Properties (4.1, 4.2, 4.3, 4.4, 4.5, 4.6)** - These can be combined into a property about complete user flow documentation
4. **Alternative Flow Properties (5.1, 5.2, 5.3, 5.4, 5.5, 5.6)** - These can be combined into a property about complete alternative scenario documentation
5. **Format Consistency Properties (6.1, 6.2, 6.3, 6.4, 6.5)** - These can be combined into a property about consistent formatting
6. **Query Feature Properties (8.1, 8.2, 8.3, 8.4, 8.5)** - These can be combined into a property about complete query feature documentation
7. **File Upload Properties (9.1, 9.2, 9.3, 9.4, 9.5)** - These can be combined into a property about complete file upload documentation
8. **Notification Properties (10.1, 10.2, 10.3, 10.4)** - These can be combined into a property about complete notification documentation
9. **Gamification Properties (11.1, 11.2, 11.3, 11.4)** - These can be combined into a property about complete gamification documentation
10. **Master Data Properties (12.1, 12.2, 12.3, 12.4)** - These can be combined into a property about complete master data documentation

**Redundancy Elimination:**

- Properties 1.3 and 1.4 overlap with the general completeness property 1.2
- Properties 3.2 and 3.3 are subsumed by the general response documentation property
- Properties 7.2 and 7.3 are subsumed by the general authorization documentation property

After consolidation, we have a cleaner set of non-redundant properties that provide comprehensive coverage.

### Correctness Properties

Property 1: Module Discovery Completeness
*For any* backend codebase with the standard Laravel modular structure, the documentation generator should identify exactly 12 modules with the expected names (Auth, Schemes, Enrollments, Learning, Gamification, Forums, Content, Notifications, Grading, Search, Operations, Common)
**Validates: Requirements 1.1**

Property 2: Endpoint Extraction Completeness
*For any* module with a routes/api.php file, all defined routes should be extracted and documented without omission
**Validates: Requirements 1.2**

Property 3: Input Field Documentation Completeness
*For any* endpoint with a FormRequest or DTO, all input fields should be documented with name, type, required/optional status, validation rules, conditional logic, enum values, dependencies, and default values where applicable
**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7**

Property 4: Response Structure Documentation Completeness
*For any* endpoint, the documentation should include success response examples with complete field structure, all possible error responses with status codes and formats, and nested object documentation where applicable
**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**

Property 5: User Flow Documentation Completeness
*For any* feature, the documentation should include a complete step-by-step user flow with all decision branches, multi-endpoint sequences, error handling paths, authentication requirements, and role-based variations where applicable
**Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.5, 4.6**

Property 6: Alternative Scenario Documentation Completeness
*For any* feature, the documentation should include empty state scenarios, all error conditions with UI implications, loading states, permission denial scenarios, rate limit handling, and validation error display guidance
**Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6**

Property 7: Documentation Format Consistency
*For any* generated documentation, all tables should use consistent column structures, JSON examples should be in formatted code blocks, user flows should use numbered lists or diagrams, and validation rule terminology should be consistent throughout
**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

Property 8: Authorization Documentation Completeness
*For any* endpoint, the documentation should indicate authentication requirements, list required roles where applicable, document policy authorization logic, include JWT token details for auth endpoints, and provide 401/403 error examples
**Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5**

Property 9: Query Feature Documentation Completeness
*For any* endpoint supporting filtering, sorting, search, pagination, or includes, the documentation should list all available options with syntax, parameters, defaults, and maximum values
**Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5**

Property 10: File Upload Documentation Completeness
*For any* endpoint accepting file uploads, the documentation should specify accepted file types, size limits, dimension requirements, optional/required status, and maximum file count for multi-file uploads
**Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5**

Property 11: Notification Documentation Completeness
*For any* notification feature, the documentation should list all notification types with triggers, preference options, priority levels with UI implications, and real-time update mechanisms
**Validates: Requirements 10.1, 10.2, 10.3, 10.4**

Property 12: Gamification Documentation Completeness
*For any* gamification feature, the documentation should list all XP earning actions with point values, badge types with unlock conditions and visual requirements, leaderboard ranking criteria with time periods and filters, and challenge types with requirements and rewards
**Validates: Requirements 11.1, 11.2, 11.3, 11.4**

Property 13: Master Data Documentation Completeness
*For any* field using master data or enums, the documentation should list all possible values with display labels, master data endpoints, hierarchical relationships where applicable, and available languages for translatable data
**Validates: Requirements 12.1, 12.2, 12.3, 12.4**

## Error Handling

### Documentation Generation Errors

**Missing Route File**
- **Scenario**: Module directory exists but routes/api.php is missing
- **Handling**: Log warning, skip module, continue with other modules
- **UI Guidance**: Display warning in documentation that module was skipped

**Invalid Route Syntax**
- **Scenario**: Route file contains syntax errors or cannot be parsed
- **Handling**: Log error with file path and line number, skip problematic routes
- **UI Guidance**: Display error message with details for manual review

**Missing FormRequest Class**
- **Scenario**: Route references FormRequest class that doesn't exist
- **Handling**: Log warning, document endpoint without input validation details
- **UI Guidance**: Mark endpoint as "validation details unavailable"

**Missing Resource Class**
- **Scenario**: Controller returns resource that doesn't exist
- **Handling**: Log warning, generate basic response structure from controller code
- **UI Guidance**: Mark response as "inferred from controller"

**Circular Dependencies**
- **Scenario**: DTO or Resource has circular references
- **Handling**: Detect cycles, break at second occurrence, log warning
- **UI Guidance**: Note circular reference in documentation

**Trait Resolution Failure**
- **Scenario**: Cannot resolve trait methods for validation rules
- **Handling**: Log warning, document only directly defined rules
- **UI Guidance**: Note that some rules may be missing

### Validation Errors

**Invalid Markdown Syntax**
- **Scenario**: Generated markdown contains syntax errors
- **Handling**: Validate markdown before writing, fix common issues automatically
- **UI Guidance**: Display validation errors if auto-fix fails

**Invalid JSON Examples**
- **Scenario**: Generated JSON examples are not valid JSON
- **Handling**: Validate JSON before including, use placeholder if invalid
- **UI Guidance**: Mark JSON as "example structure only"

**Missing Required Fields**
- **Scenario**: Documentation template requires field that wasn't extracted
- **Handling**: Use placeholder value, log warning
- **UI Guidance**: Mark field as "to be determined"

## Testing Strategy

### Unit Testing Approach

Unit tests will verify specific components and their interactions:

**Module Discovery Tests**
- Test that module scanner correctly identifies all 12 modules
- Test that module metadata extraction works for valid module.json
- Test error handling for missing or invalid module structures

**Route Parser Tests**
- Test parsing of simple routes (GET, POST, PUT, DELETE)
- Test parsing of routes with middleware
- Test parsing of routes with parameter bindings
- Test parsing of grouped routes
- Test error handling for invalid route syntax

**Validation Rule Extractor Tests**
- Test extraction of basic rules (required, max, min, email)
- Test extraction of complex rules (regex, unique, exists)
- Test extraction of conditional rules (required_if, required_with)
- Test extraction of custom validation messages
- Test trait resolution for inherited rules

**Response Structure Analyzer Tests**
- Test extraction of simple resource fields
- Test extraction of nested resources
- Test extraction of conditional fields (when, mergeWhen)
- Test extraction of relationship includes
- Test generation of JSON examples

**Documentation Generator Tests**
- Test markdown table generation with correct columns
- Test JSON code block formatting
- Test user flow diagram generation
- Test consistency of terminology across sections

### Property-Based Testing Approach

Property-based tests will verify universal properties across all inputs using a PHP property testing library (e.g., Eris or Pest with property testing plugin). Each test will run a minimum of 100 iterations.

**Property Test Configuration**
- Library: Pest with property testing plugin or Eris
- Iterations per test: 100 minimum
- Seed: Random (logged for reproducibility)
- Shrinking: Enabled for failure case minimization

**Property Test Tagging**
Each property-based test will be tagged with a comment in this format:
```php
// Feature: ui-ux-documentation, Property 1: Module Discovery Completeness
```

**Test Data Generators**
- Module structure generator: Creates valid/invalid module directories
- Route file generator: Creates valid/invalid route definitions
- FormRequest generator: Creates validation rule arrays
- Resource generator: Creates response structure definitions

## Implementation Notes

### Technology Stack

**Language**: PHP 8.2+
**Framework**: Laravel 11+ (for accessing Laravel internals)
**Dependencies**:
- nikic/php-parser: For parsing PHP files
- symfony/finder: For file system traversal
- league/commonmark: For markdown generation
- spatie/laravel-data: For DTO analysis

### Performance Considerations

**Caching Strategy**
- Cache parsed route files to avoid re-parsing
- Cache extracted validation rules
- Cache resource structures
- Invalidate cache when source files change

**Parallel Processing**
- Process modules in parallel using Laravel queues
- Process endpoints within a module in parallel
- Aggregate results after all processing complete

**Memory Management**
- Process large modules in chunks
- Stream output to file instead of building in memory
- Release parsed AST after extraction

### Output Structure

The generated documentation will be organized as follows:

```
docs/ui-ux-specification/
├── README.md                          # Overview and table of contents
├── 01-authentication.md               # Auth module documentation
├── 02-courses-and-schemes.md          # Schemes module documentation
├── 03-enrollments.md                  # Enrollments module documentation
├── 04-learning-and-assignments.md     # Learning module documentation
├── 05-gamification.md                 # Gamification module documentation
├── 06-forums.md                       # Forums module documentation
├── 07-content-and-news.md             # Content module documentation
├── 08-notifications.md                # Notifications module documentation
├── 09-grading.md                      # Grading module documentation
├── 10-search.md                       # Search module documentation
├── 11-operations-and-reports.md       # Operations module documentation
├── 12-master-data.md                  # Common module documentation
├── appendix-a-authentication-flows.md # Detailed auth flows
├── appendix-b-error-responses.md      # All error response examples
├── appendix-c-master-data-enums.md    # All enum values
└── appendix-d-file-upload-specs.md    # All file upload specifications
```

### Documentation Template Structure

Each module documentation file will follow this structure:

1. **Module Overview**
   - Module name and description
   - Key features summary
   - Related modules

2. **Feature List Table**
   - Feature name
   - Description
   - Endpoints count
   - Authentication required
   - Roles allowed

3. **Endpoint Specifications** (for each endpoint)
   - Endpoint summary
   - HTTP method and URI
   - Authentication requirements
   - Rate limiting
   - Input specification table
   - Output specification with JSON examples
   - Error responses table
   - User flow diagram
   - Alternative flows
   - UI/UX notes

4. **Master Data Reference** (if applicable)
   - Enum values used in module
   - Master data endpoints

5. **Common Patterns** (if applicable)
   - Filtering examples
   - Sorting examples
   - Pagination examples
   - Search examples

### Example Documentation Section

Here's an example of how a single endpoint will be documented:

```markdown
### User Registration

**Endpoint**: `POST /api/v1/auth/register`
**Authentication**: Not required
**Rate Limit**: 10 requests per minute
**Roles**: Public (unauthenticated)

#### Description

Allows new users to create an account in the system. After successful registration, the user receives a verification email and can log in immediately with unverified status.

#### Input Specification

| Field Name | Type | Required | Validation Rules | Example Value | Notes |
|------------|------|----------|------------------|---------------|-------|
| name | string | Yes | max:255 | "John Doe" | Full name of user |
| username | string | Yes | min:3, max:50, regex:/^[a-z0-9_\.\-]+$/i, unique:users | "johndoe" | Only letters, numbers, dots, underscores, hyphens |
| email | string | Yes | email, max:255, unique:users | "john@example.com" | Must be valid email format |
| password | string | Yes | min:8, confirmed | "SecurePass123!" | Must contain uppercase, lowercase, number, special char |
| password_confirmation | string | Yes | same:password | "SecurePass123!" | Must match password field |

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Registrasi berhasil. Silakan cek email untuk verifikasi.",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "email_verified_at": null,
      "status": "active",
      "created_at": "2025-12-11T10:30:00.000000Z"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "abc123def456...",
    "token_type": "bearer",
    "expires_in": 900
  }
}
```

#### Error Responses

| Status Code | Scenario | Example Response | UI Guidance |
|-------------|----------|------------------|-------------|
| 422 | Validation Error | `{"success": false, "message": "Validasi gagal", "errors": {"email": ["Email sudah digunakan."]}}` | Display field-specific errors below each input |
| 429 | Rate Limit Exceeded | `{"success": false, "message": "Terlalu banyak percobaan. Silakan coba lagi dalam 60 detik."}` | Show countdown timer, disable submit button |
| 500 | Server Error | `{"success": false, "message": "Terjadi kesalahan server."}` | Show generic error message, suggest retry |

#### User Flow

1. **User visits registration page**
   - UI State: Empty form with name, username, email, password fields
   - Validation: None yet

2. **User fills in registration form**
   - UI State: Form fields populated, submit button enabled
   - Validation: Client-side validation on blur (email format, username format)

3. **User submits form**
   - UI State: Loading spinner, submit button disabled
   - API Call: `POST /api/v1/auth/register` with form data
   - Validation: Server-side validation of all fields

4. **Success scenario**
   - Response: 201 with user data and tokens
   - UI State: Success message displayed
   - Next Action: Redirect to dashboard or show email verification prompt
   - Token Storage: Store access_token and refresh_token securely

5. **Error scenario**
   - Response: 422 with validation errors
   - UI State: Error messages displayed below relevant fields
   - Next Action: User corrects errors and resubmits

#### Alternative Flows

**Empty State**: N/A (registration form always shows empty)

**Validation Error Flow**:
1. User submits form with invalid data
2. Server returns 422 with field-specific errors
3. UI displays errors below each field in red
4. User corrects errors
5. Submit button re-enabled
6. User resubmits

**Rate Limit Flow**:
1. User submits registration multiple times rapidly
2. Server returns 429 after 10 attempts
3. UI shows "Too many attempts" message with countdown
4. Submit button disabled for 60 seconds
5. After cooldown, user can retry

**Duplicate Email/Username Flow**:
1. User submits form with existing email or username
2. Server returns 422 with "already taken" error
3. UI highlights the duplicate field
4. Suggest alternative usernames if applicable
5. User changes value and resubmits

#### UI/UX Notes

- **Password Strength Indicator**: Show real-time password strength meter
- **Username Availability**: Consider adding real-time username availability check
- **Email Verification**: Clearly communicate that email verification is required
- **Auto-login**: User is automatically logged in after registration
- **Token Management**: Store tokens securely (httpOnly cookie recommended)
- **Loading States**: Show loading spinner during API call
- **Success Feedback**: Show success message before redirect
- **Error Recovery**: Provide clear guidance on how to fix validation errors
- **Accessibility**: Ensure form is keyboard navigable and screen reader friendly
```

This example demonstrates the level of detail that will be provided for each endpoint in the documentation.
