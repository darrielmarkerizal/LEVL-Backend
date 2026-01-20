# API Documentation - Modules/Enrollments

## Base URL

Prefix: `/api/v1`

## 1. List Enrollments (Managed by Instructor/Admin)

List enrollments for a specific course that you manage.

**Endpoint**
`GET /courses/{slug}/enrollments`

**Query Parameters**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | int | No | Page number (default: 1) |
| `per_page` | int | No | Items per page (default: 15) |
| `sort` | string | No | Sort field using Spatie QueryBuilder. Default: `priority` (Pending first). |

**Allowed Filters** (`filter[key]=value`)
| Filter Key | Type | Description |
|------------|------|-------------|
| `status` | string | Exact match. Values: `active`, `pending`, `completed`, `cancelled` |
| `user_id` | int | Exact match. Filter by student ID. |
| `search` | string | Search by **User Name** or **Email**. |
| `enrolled_from` | date | Filter enrollments on or after date (`YYYY-MM-DD`). |
| `enrolled_to` | date | Filter enrollments on or before date (`YYYY-MM-DD`). |

**Allowed Sorts** (`sort=field` or `sort=-field`)
| Sort Field | Description |
|------------|-------------|
| `priority` | **Default**. Sorts `pending` items first, then by date. |
| `enrolled_at` | Date enrolled |
| `completed_at` | Date completed |
| `created_at` | Date created |

---

## 2. List All Managed Enrollments

List all enrollments across all courses that you manage (as Instructor/Admin).

**Endpoint**
`GET /courses/enrollments`

**Query Parameters**
Same as endpoint #1, plus:

**Additional Allowed Filters**
| Filter Key | Type | Description |
|------------|------|-------------|
| `course_id` | int | Exact match. Filter by course ID. |
| `course_slug`| string | Exact match. Filter by course slug (custom logic). |

---

## 3. List My Enrollments (Student) / All (Superadmin)

List enrollments for the authenticated user. If Superadmin, lists all system enrollments.

**Endpoint**
`GET /enrollments`

**Allowed Filters**
| Filter Key | Type | Description |
|------------|------|-------------|
| `status` | string | Exact match. |
| `course_id` | int | Exact match. |
| `search` | string | Search by **Course Title** or **Slug** (Student view only). |
| `enrolled_from` | date | Start date range. |
| `enrolled_to` | date | End date range. |

---

## 4. Check Enrollment Status

Check if the user is enrolled in a specific course.

**Endpoint**
`GET /courses/{slug}/enrollment-status`

**Query Parameters**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | int | No | **Superadmin Only**. Check status for a specific user ID. |

---

## 5. Enroll in Course

Enroll the current user into a course.

**Endpoint**
`POST /courses/{slug}/enrollments`

**Request Body** (JSON/Form-Data)
| Field | Type | Required | Rules | Description |
|-------|------|----------|-------|-------------|
| `enrollment_key`| string | Conditional | `nullable`, `string`, `max:100` | Required if course is key-protected. |

---

## 6. Cancel Enrollment Request

Cancel a pending enrollment request.

**Endpoint**
`POST /courses/{slug}/cancel`

**Request Body** (JSON/Form-Data)
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | int | No | **Superadmin Only**. Cancel on behalf of another user. |

---

## 7. Withdraw from Course

Withdraw from an active course.

**Endpoint**
`POST /courses/{slug}/withdraw`

**Request Body** (JSON/Form-Data)
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | int | No | **Superadmin Only**. Withdraw on behalf of another user. |

---

## 8. Approve Enrollment (Manager)

Approve a pending enrollment application.

**Endpoint**
`POST /enrollments/{enrollment_id}/approve`

**Request Body**
None.

---

## 9. Decline Enrollment (Manager)

Decline a pending enrollment application.

**Endpoint**
`POST /enrollments/{enrollment_id}/decline`

**Request Body**
None.

---

## 10. Remove User from Course (Manager)

Force remove a user from a course (Active/Pending -> Cancelled).

**Endpoint**
`POST /enrollments/{enrollment_id}/remove`

**Request Body**
None.
