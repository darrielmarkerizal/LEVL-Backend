# Points History API - Quick Reference

## Endpoint
```
GET /api/v1/user/points-history
```

## Quick Examples

```bash
# Default (15 items, newest first)
GET /api/v1/user/points-history

# Filter by lesson only
GET /api/v1/user/points-history?filter[source_type]=lesson

# This month only
GET /api/v1/user/points-history?filter[period]=this_month

# Highest points first
GET /api/v1/user/points-history?sort=-points

# Date range
GET /api/v1/user/points-history?filter[date_from]=2026-03-01&filter[date_to]=2026-03-15

# Combined filters
GET /api/v1/user/points-history?filter[source_type]=lesson&filter[period]=this_week&sort=-points
```

## Available Filters

| Filter | Values | Example |
|--------|--------|---------|
| `source_type` | lesson, assignment, course, unit | `filter[source_type]=lesson` |
| `reason` | lesson_completed, assignment_submitted, perfect_score | `filter[reason]=lesson_completed` |
| `period` | today, this_week, this_month, this_year | `filter[period]=this_month` |
| `date_from` | YYYY-MM-DD | `filter[date_from]=2026-03-01` |
| `date_to` | YYYY-MM-DD | `filter[date_to]=2026-03-15` |
| `points_min` | integer | `filter[points_min]=50` |
| `points_max` | integer | `filter[points_max]=100` |

## Available Sorts

| Sort | Description |
|------|-------------|
| `-created_at` | Newest first (default) |
| `created_at` | Oldest first |
| `-points` | Highest points first |
| `points` | Lowest points first |
| `-source_type` | Source type Z-A |
| `source_type` | Source type A-Z |
| `-reason` | Reason Z-A |
| `reason` | Reason A-Z |

## Pagination

```bash
per_page=20    # Items per page (max: 100)
page=2         # Page number
```
