# FILTER QUICK REFERENCE - GAMIFICATION API
**Updated**: 15 Maret 2026

---

## 📋 POINTS HISTORY FILTERS

**Endpoint**: `GET /api/v1/user/points-history`

| Filter | Type | Example | Description |
|--------|------|---------|-------------|
| `filter[source_type]` | string | `lesson` | lesson, assignment, course, unit, grade, attempt |
| `filter[reason]` | string | `lesson_completed` | lesson_completed, assignment_submitted, quiz_completed, etc. |
| `filter[period]` | string | `this_month` | today, this_week, this_month, this_year |
| `filter[month]` | string | `2026-01` | ✨ NEW: Specific month (YYYY-MM) |
| `filter[date_from]` | date | `2026-03-01` | Start date (Y-m-d) |
| `filter[date_to]` | date | `2026-03-15` | End date (Y-m-d) |
| `filter[points_min]` | integer | `50` | Minimum points |
| `filter[points_max]` | integer | `100` | Maximum points |
| `sort` | string | `-created_at` | created_at, points, source_type, reason |
| `per_page` | integer | `20` | Items per page (max: 100) |
| `page` | integer | `1` | Page number |

### Examples

```bash
# All transactions
GET /api/v1/user/points-history

# Filter by source type
GET /api/v1/user/points-history?filter[source_type]=lesson

# Filter by specific month (January 2026)
GET /api/v1/user/points-history?filter[month]=2026-01

# Filter by date range
GET /api/v1/user/points-history?filter[date_from]=2026-03-01&filter[date_to]=2026-03-15

# Filter by points range
GET /api/v1/user/points-history?filter[points_min]=50&filter[points_max]=100

# Combine multiple filters
GET /api/v1/user/points-history?filter[source_type]=lesson&filter[month]=2026-01&sort=-points
```

---

## 🏆 LEADERBOARD FILTERS

**Endpoint**: `GET /api/v1/leaderboards`

| Filter | Type | Example | Description |
|--------|------|---------|-------------|
| `filter[period]` | string | `all_time` | today, this_week, this_month, this_year, all_time |
| `filter[month]` | string | `2026-01` | ✨ NEW: Specific month (YYYY-MM) |
| `search` | string | `Ahmad` | Search by user name |
| `per_page` | integer | `20` | Items per page (max: 100) |
| `page` | integer | `1` | Page number |

### Examples

```bash
# Global leaderboard (all time)
GET /api/v1/leaderboards?filter[period]=all_time

# Leaderboard for this month
GET /api/v1/leaderboards?filter[period]=this_month

# Leaderboard for specific month (January 2026)
GET /api/v1/leaderboards?filter[month]=2026-01

# Leaderboard with search
GET /api/v1/leaderboards?filter[month]=2026-02&search=Ahmad

# This week leaderboard
GET /api/v1/leaderboards?filter[period]=this_week&per_page=20
```

---

## 📊 MY RANK FILTERS

**Endpoint**: `GET /api/v1/user/rank`

| Filter | Type | Example | Description |
|--------|------|---------|-------------|
| `filter[period]` | string | `all_time` | today, this_week, this_month, this_year, all_time |
| `filter[month]` | string | `2026-01` | ✨ NEW: Specific month (YYYY-MM) |

### Examples

```bash
# My rank (all time)
GET /api/v1/user/rank?filter[period]=all_time

# My rank this month
GET /api/v1/user/rank?filter[period]=this_month

# My rank for specific month (January 2026)
GET /api/v1/user/rank?filter[month]=2026-01

# My rank for February 2026
GET /api/v1/user/rank?filter[month]=2026-02
```

---

## 🏅 BADGES FILTERS

### My Badges
**Endpoint**: `GET /api/v1/user/badges`

| Filter | Type | Example | Description |
|--------|------|---------|-------------|
| `filter[type]` | string | `milestone` | completion, quality, speed, habit, social, milestone, hidden |
| `filter[rarity]` | string | `rare` | common, uncommon, rare, epic, legendary |
| `sort` | string | `-earned_at` | earned_at, progress |
| `per_page` | integer | `15` | Items per page (max: 100) |
| `page` | integer | `1` | Page number |

### Available Badges
**Endpoint**: `GET /api/v1/badges/available`

| Filter | Type | Example | Description |
|--------|------|---------|-------------|
| `filter[type]` | string | `milestone` | completion, quality, speed, habit, social, milestone, hidden |
| `filter[rarity]` | string | `rare` | common, uncommon, rare, epic, legendary |
| `filter[earned]` | boolean | `false` | ✅ VERIFIED: true (earned), false (not earned) |
| `search` | string | `master` | Search by name, code, description |
| `sort` | string | `name` | name, rarity, xp_reward, created_at |
| `per_page` | integer | `15` | Items per page (max: 100) |
| `page` | integer | `1` | Page number |

### Examples

```bash
# My badges
GET /api/v1/user/badges

# My badges by type
GET /api/v1/user/badges?filter[type]=milestone

# All available badges
GET /api/v1/badges/available

# Only earned badges
GET /api/v1/badges/available?filter[earned]=true

# Only not earned badges
GET /api/v1/badges/available?filter[earned]=false

# Not earned milestone badges
GET /api/v1/badges/available?filter[earned]=false&filter[type]=milestone

# Rare badges not yet earned
GET /api/v1/badges/available?filter[earned]=false&filter[rarity]=rare

# Search badges
GET /api/v1/badges/available?search=master

# Combine all filters
GET /api/v1/badges/available?filter[earned]=false&filter[type]=milestone&filter[rarity]=rare&search=level
```

---

## 🎯 LEVELS FILTERS

**Endpoint**: `GET /api/v1/levels`

| Filter | Type | Example | Description |
|--------|------|---------|-------------|
| `filter[level]` | integer | `5` | Exact level |
| `filter[level_min]` | integer | `1` | Minimum level |
| `filter[level_max]` | integer | `10` | Maximum level |
| `filter[xp_min]` | integer | `0` | Minimum XP required |
| `filter[xp_max]` | integer | `500` | Maximum XP required |
| `sort` | string | `level` | level, xp_required, bonus_xp |
| `per_page` | integer | `20` | Items per page (max: 100) |
| `page` | integer | `1` | Page number |

### Examples

```bash
# All levels
GET /api/v1/levels

# Levels 1-10
GET /api/v1/levels?filter[level_min]=1&filter[level_max]=10

# Levels by XP range
GET /api/v1/levels?filter[xp_min]=0&filter[xp_max]=500

# Specific level
GET /api/v1/levels?filter[level]=5

# Sort by bonus XP
GET /api/v1/levels?sort=-bonus_xp
```

---

## ⚡ QUICK TIPS

### Month Filter Priority
```bash
# If both month and period are present, month takes precedence
GET /api/v1/user/points-history?filter[month]=2026-01&filter[period]=this_week
# Result: Shows January 2026 data (period ignored)
```

### Month Format
```bash
# Valid formats
2026-01 ✅
2026-12 ✅

# Invalid formats
2026-1  ❌ (missing leading zero)
26-01   ❌ (year must be 4 digits)
2026/01 ❌ (wrong separator)
```

### Earned Filter Values
```bash
# For TRUE (earned badges)
filter[earned]=true
filter[earned]=1

# For FALSE (not earned badges)
filter[earned]=false
filter[earned]=0
```

### Combining Filters
```bash
# You can combine multiple filters
GET /api/v1/user/points-history?filter[source_type]=lesson&filter[month]=2026-01&filter[points_min]=50&sort=-points&per_page=20

GET /api/v1/badges/available?filter[earned]=false&filter[type]=milestone&filter[rarity]=rare&search=level&per_page=20

GET /api/v1/leaderboards?filter[month]=2026-01&search=Ahmad&per_page=20
```

---

## 🔍 FILTER VALIDATION

### Points History
- `source_type`: Must be one of: lesson, assignment, course, unit, grade, attempt
- `reason`: Must be valid PointReason enum value
- `period`: Must be one of: today, this_week, this_month, this_year
- `month`: Must match format YYYY-MM
- `date_from/date_to`: Must be valid date (Y-m-d)
- `points_min/points_max`: Must be integer

### Leaderboard
- `period`: Must be one of: today, this_week, this_month, this_year, all_time
- `month`: Must match format YYYY-MM
- `search`: Any string

### Badges
- `type`: Must be one of: completion, quality, speed, habit, social, milestone, hidden
- `rarity`: Must be one of: common, uncommon, rare, epic, legendary
- `earned`: Must be boolean (true, false, 1, 0)
- `search`: Any string

### Levels
- `level`: Must be integer
- `level_min/level_max`: Must be integer
- `xp_min/xp_max`: Must be integer

---

## 📱 POSTMAN QUICK IMPORT

```javascript
// Points History - Filter by Month
GET {{base_url}}/user/points-history?filter[month]=2026-01&per_page=20

// Leaderboard - Filter by Month
GET {{base_url}}/leaderboards?filter[month]=2026-01&per_page=20

// My Rank - Filter by Month
GET {{base_url}}/user/rank?filter[month]=2026-01

// Badges - Only Not Earned
GET {{base_url}}/badges/available?filter[earned]=false&per_page=20

// Badges - Rare Milestone Not Earned
GET {{base_url}}/badges/available?filter[earned]=false&filter[type]=milestone&filter[rarity]=rare
```

---

**Last Updated**: 15 Maret 2026  
**Version**: 1.0  
**Status**: ✅ Production Ready
