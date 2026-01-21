# Common Module API Documentation

Complete API reference for the Common module, including Master Data management and Activity Logs.

---

## Base URL

```
/api/v1
```

## Authentication

All endpoints require authentication via Bearer token:

```
Authorization: Bearer {access_token}
```

Some endpoints are restricted to specific roles (e.g., **Superadmin**).

---

## Master Data Types

Common types available in the system include (but are not limited to):
- `user-status`
- `roles`
- `course-status`
- `course-types`
- `enrollment-types`
- `enrollment-status`
- `tags` (managed via specific endpoints or generic CRUD)

---

## Endpoints

### 1. Helper: Get Available Master Data Types

Get a list of all available master data types that can be queried, including pagination, filtering, and sorting support.

**Endpoint:** `GET /master-data`

**Authorization:** Any authenticated user.

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 15 | Items per page |
| `search` | string | No | - | Fuzzy search by type key or label |
| `filter[is_crud]` | boolean | No | - | Filter by CRUD capability (`true`=database, `false`=static) |
| `sort` | string | No | `label` | Sorting with Spatie format. Allowed fields: `label`, `type`, `count`, `last_updated`. Use `-` prefix for desc. Supports multi-sort via comma, e.g. `sort=label,-count` |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Daftar tipe master data berhasil diambil.",
  "data": [
    {
      "type": "user-status",
      "label": "User Status",
      "is_crud": true,
      "count": 1,
      "last_updated": "2026-01-20 23:27:46"
    },
    {
      "type": "categories",
      "label": "Kategori",
      "is_crud": true,
      "count": 7,
      "last_updated": "2026-01-19 07:13:51"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 33,
      "last_page": 3,
      "from": 1,
      "to": 15,
      "has_next": true,
      "has_prev": false
    },
    "path": "..."
  }
}
```

---

### 2. Helper: Get All Items by Type (Dynamic)

Quickly retrieve all items for a specific master data type (e.g., for dropdowns).

**Endpoint:** `GET /master-data/{type}`

**Authorization:** Any authenticated user.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | The master data key (e.g., `user-status`) |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": [
    {
      "value": "active",
      "label": "Active",
      "is_active": true,
      "metadata": null
    },
    {
      "value": "inactive",
      "label": "Inactive",
      "is_active": true,
      "metadata": null
    }
  ]
}
```

**Error Responses:**
- `404` - Type not found or not supported.

---

### 3. List Master Data Items (Pagination)

Paginated list of master data items for management or detailed viewing.

**Endpoint:** `GET /master-data/{type}/items`

**Authorization:** Any authenticated user.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | The master data key |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | int | No | Page number (default: 1) |
| `per_page` | int | No | Items per page (default: 15) |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": [
    {
      "id": 1,
      "value": "active",
      "label": "Active",
      "is_active": true,
      "sort_order": 1,
      "metadata": null,
      "created_at": "2026-01-20T10:00:00.000000Z",
      "updated_at": "2026-01-20T10:00:00.000000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 10,
      "last_page": 1
    }
  }
}
```

---

### 4. Show Master Data Item

Get details of a specific master data item.

**Endpoint:** `GET /master-data/{type}/items/{id}`

**Authorization:** Any authenticated user.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | The master data key |
| `id` | int | The ID of the item |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": {
    "id": 1,
    "value": "active",
    "label": "Active",
    "is_active": true,
    "sort_order": 1,
    "metadata": { "color": "green" }
  }
}
```

**Error Responses:**
- `404` - Item not found

---

### 5. Create Master Data Item

Create a new scalable master data item.

**Endpoint:** `POST /master-data/{type}/items`

**Authorization:** **Superadmin** only.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | The master data key |

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `value` | string | Yes | Unique identifier/slug (max: 255) |
| `label` | string | Yes | Human-readable label (max: 255) |
| `is_active` | boolean | No | Default: `true` |
| `sort_order` | int | No | Ordering priority |
| `metadata` | json/array | No | Additional data |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data berhasil dibuat.",
  "data": {
    "id": 12,
    "value": "new_status",
    "label": "New Status",
    "is_active": true
  }
}
```

---

### 6. Update Master Data Item

Update an existing master data item.

**Endpoint:** `PUT /master-data/{type}/items/{id}`

**Authorization:** **Superadmin** only.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | The master data key |
| `id` | int | The ID of the item |

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `value` | string | No | Unique identifier/slug |
| `label` | string | No | Human-readable label |
| `is_active` | boolean | No | Active status |
| `sort_order` | int | No | Ordering priority |
| `metadata` | json/array | No | Additional data |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data berhasil diperbarui.",
  "data": {
    "id": 12,
    "value": "updated_status",
    "label": "Updated Status"
  }
}
```

---

### 7. Delete Master Data Item

Permanently remove a master data item.

**Endpoint:** `DELETE /master-data/{type}/items/{id}`

**Authorization:** **Superadmin** only.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | The master data key |
| `id` | int | The ID of the item |

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Data berhasil dihapus.",
  "data": null
}
```

---

## Tag Management

Manage system tags via the Master Data API.

### 8. List Tags

**Endpoint:** `GET /master-data/tags`

**Authorization:** Any authenticated user.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Daftar tag berhasil diambil.",
  "data": [
    {
      "id": 1,
      "name": "PHP",
      "slug": "php",
      "created_at": "...",
      "updated_at": "..."
    }
  ]
}
```

### 9. Show Tag

**Endpoint:** `GET /master-data/tags/{slug}`

**Authorization:** Any authenticated user.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | The slug of the tag |

**Response:** `200 OK`

### 10. Create Tag

**Endpoint:** `POST /master-data/tags`

**Authorization:** **Superadmin** only.

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Name for the tag |

**Response:** `201 Created`

### 11. Update Tag

**Endpoint:** `PUT /master-data/tags/{slug}`

**Authorization:** **Superadmin** only.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | The slug of the tag to update |

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | New name for the tag |

**Response:** `200 OK`

### 12. Delete Tag

**Endpoint:** `DELETE /master-data/tags/{slug}`

**Authorization:** **Superadmin** only.

**Response:** `200 OK`

---

## Activity Logs

View system-wide activity logs.

### 13. List Activity Logs

**Endpoint:** `GET /activity-logs`

**Authorization:** **Superadmin** only.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | int | No | Page number |
| `per_page` | int | No | Items per page (default: 15) |
| `search` | string | No | Search query (uses Scout/Meilisearch if enabled) |
| `filter[log_name]` | string | No | Filter by log name (see allowed values) |
| `filter[event]` | string | No | Filter by event type (`created`, `updated`, `deleted`, or custom) |
| `filter[subject_type]` | string | No | Filter by subject model FQCN |
| `filter[subject_id]` | int | No | Filter by subject ID |
| `filter[causer_type]` | string | No | Filter by causer model FQCN |
| `filter[causer_id]` | int | No | Filter by causer ID |
| `filter[device_type]` | string | No | Filter by device type (`desktop`, `mobile`, `tablet`) |
| `filter[ip_address]` | string | No | Exact match on IP address |
| `filter[browser]` | string | No | Filter by browser name (see metadata for options) |
| `filter[platform]` | string | No | Filter by platform (see metadata for options) |
| `filter[created_at][from]`| date | No | Logs on/after this date (YYYY-MM-DD; start of day) |
| `filter[created_at][to]` | date | No | Logs on/before this date (YYYY-MM-DD; end of day) |
| `sort` | string | No | Allowed sorts: `id`, `created_at`, `event`, `log_name`. Prefix with `-` for desc. Default: `-created_at` |

**Allowed filter values**
- `filter[log_name]`, `filter[browser]`, `filter[platform]`: available in response `meta.filters` options.
- `filter[subject_type]`, `filter[causer_type]`: available in response `meta.filters` options.
- `filter[event]`: typically `created`, `updated`, `deleted`.
- `filter[device_type]`: `desktop`, `mobile`, `tablet`.
- `filter[created_at][from|to]`: dates parsed as start/end of day in server timezone.


**Notes**
- `search` uses Scout/Meilisearch when enabled; if Scout is disabled, search is ignored.
- Sorting supports multi-sort via commas, e.g. `sort=log_name,-created_at`.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Log aktivitas berhasil diambil.",
  "data": [
    {
      "id": 45,
      "log_name": "default",
      "description": "Enrollment telah diperbarui",
      "subject_type": "Modules\\Enrollments\\Models\\Enrollment",
      "subject_id": 3,
      "causer_type": "Modules\\Auth\\Models\\User",
      "causer_id": 2,
      "properties": {
        "attributes": { "status": "cancelled" },
        "old": { "status": "active" }
      },
      "created_at": "2026-01-20T14:48:09.000000Z",
      "event": "updated",
      "browser": "PostmanRuntime 7.51",
      "device_type": "desktop"
    }
  ],
  "meta": {
    "pagination": { "current_page": 1, "per_page": 15, "total": 45, "last_page": 3 },
    "filters": {
      "log_name": {
        "label": "Tipe Aktivitas",
        "type": "select",
        "options": [
          { "value": "auth", "label": "auth" },
          { "value": "default", "label": "default" }
        ]
      },
      "browser": {
        "label": "Browser",
        "type": "select",
        "options": [
          { "value": "PostmanRuntime 7.51", "label": "PostmanRuntime 7.51" }
        ]
      },
      "device_type": {
        "label": "Tipe Perangkat",
        "type": "select",
        "options": [
          { "value": "desktop", "label": "Desktop" },
          { "value": "mobile", "label": "Mobile" },
          { "value": "tablet", "label": "Tablet" }
        ]
      },
      "platform": {
        "label": "Platform",
        "type": "select",
        "options": [
          { "value": "Unknown", "label": "Unknown" }
        ]
      },
      "subject_type": {
        "label": "Subject type",
        "type": "select",
        "options": [
          { "value": "Modules\\Auth\\Models\\User", "label": "Modules\\Auth\\Models\\User" },
          { "value": "Modules\\Schemes\\Models\\Course", "label": "Modules\\Schemes\\Models\\Course" }
        ]
      },
      "causer_type": {
        "label": "Causer type",
        "type": "select",
        "options": [
          { "value": "Modules\\Auth\\Models\\User", "label": "Modules\\Auth\\Models\\User" }
        ]
      }
    }
  }
}
```

### 14. Show Activity Log Detail

**Endpoint:** `GET /activity-logs/{id}`

**Authorization:** **Superadmin** only.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Log aktivitas berhasil diambil.",
  "data": {
    "id": 1,
    "description": "User logged in",
    "properties": []
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "User does not have the right roles."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource tidak ditemukan."
}
```
