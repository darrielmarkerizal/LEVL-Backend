# Badge Management - Quick Reference

Quick reference untuk Badge Management API. Untuk dokumentasi lengkap, lihat `PANDUAN_BADGE_MANAGEMENT_LENGKAP_V2.md`.

---

## 📍 Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/badges` | All | List all badges |
| GET | `/api/v1/badges/{id}` | All | Show badge detail |
| POST | `/api/v1/badges` | Superadmin | Create badge |
| PUT | `/api/v1/badges/{id}` | Superadmin | Update badge |
| DELETE | `/api/v1/badges/{id}` | Superadmin | Delete badge |
| GET | `/api/v1/user/badges` | All | Get my badges |

---

## 🎨 Badge Rarity

| Rarity | Color | XP Range |
|--------|-------|----------|
| Common | Gray (#9CA3AF) | 10-50 XP |
| Uncommon | Green (#10B981) | 50-100 XP |
| Rare | Blue (#3B82F6) | 100-200 XP |
| Epic | Purple (#8B5CF6) | 200-500 XP |
| Legendary | Gold (#F59E0B) | 500-1000 XP |

---

## 📋 Badge Fields

### Required (Create)
- `code` - Unique identifier (max 50)
- `name` - Display name (max 255)
- `type` - achievement, milestone, completion
- `icon` - File upload (max 2MB)

### Optional
- `description` - Text (max 1000)
- `category` - String (max 50)
- `rarity` - Enum (default: common)
- `xp_reward` - Integer 0-10000 (default: 0)
- `active` - Boolean (default: true)
- `threshold` - Integer
- `is_repeatable` - Boolean (default: false)
- `max_awards_per_user` - Integer
- `rules` - Array of rules

---

## 🔧 Badge Rules

### Rule Fields
- `event_trigger` - String (required if rules exist)
- `conditions` - JSON object (optional)
- `priority` - Integer (default: 0)
- `cooldown_seconds` - Integer (optional)
- `rule_enabled` - Boolean (default: true)

### Event Triggers
- `lesson_completed`
- `unit_completed`
- `course_completed`
- `assignment_graded`
- `quiz_completed`
- `login`
- `forum_post_created`
- `forum_reply_created`
- `forum_liked`

---

## 📝 Quick Examples

### List Badges
```bash
GET /api/v1/badges?filter[rarity]=rare&sort=-xp_reward&per_page=20
```

### Create Badge
```bash
POST /api/v1/badges
Content-Type: multipart/form-data

code: first_lesson
name: First Lesson
type: completion
rarity: common
xp_reward: 50
icon: [FILE]
rules[0][event_trigger]: lesson_completed
rules[0][priority]: 10
```

### Update Badge
```bash
PUT /api/v1/badges/1
Content-Type: application/json

{
  "rarity": "rare",
  "xp_reward": 200
}
```

### Get My Badges
```bash
GET /api/v1/user/badges
```

---

## 🎯 Gamification Response

```json
{
  "success": true,
  "data": { ... },
  "gamification": {
    "xp_awarded": 130,
    "leveled_up": false,
    "badges_awarded": [
      {
        "badge_id": 8,
        "name": "Assignment Starter",
        "icon_url": "...",
        "rarity": "common",
        "xp_reward": 50
      }
    ],
    "current_xp": 1450,
    "current_level": 8
  }
}
```

---

## 🔐 Authorization

| Operation | Student | Instructor | Admin | Superadmin |
|-----------|---------|------------|-------|------------|
| List | ✅ | ✅ | ✅ | ✅ |
| Detail | ✅ | ✅ | ✅ | ✅ |
| Create | ❌ | ❌ | ❌ | ✅ |
| Update | ❌ | ❌ | ❌ | ✅ |
| Delete | ❌ | ❌ | ❌ | ✅ |
| My Badges | ✅ | ✅ | ✅ | ✅ |

---

## 🎨 CSS Colors

```css
/* Rarity Colors */
.rarity-common { background: #9CA3AF; }
.rarity-uncommon { background: #10B981; }
.rarity-rare { background: #3B82F6; }
.rarity-epic { background: #8B5CF6; }
.rarity-legendary { 
  background: linear-gradient(135deg, #F59E0B 0%, #DC2626 100%);
  animation: shimmer 2s infinite;
}

/* Type Colors */
.type-achievement { background: #F59E0B; }
.type-milestone { background: #3B82F6; }
.type-completion { background: #10B981; }
```

---

## ⚠️ Common Errors

| Code | Error | Solution |
|------|-------|----------|
| 422 | Code already taken | Use unique code |
| 422 | Icon too large | Max 2MB |
| 422 | Invalid rarity | Use valid enum |
| 403 | Unauthorized | Login as Superadmin |
| 404 | Badge not found | Check badge ID |

---

## 📚 Full Documentation

- **Complete Guide**: `PANDUAN_BADGE_MANAGEMENT_LENGKAP_V2.md`
- **Implementation**: `BADGE_ENHANCEMENT_SUMMARY.md`
- **Completion Status**: `BADGE_DOCUMENTATION_COMPLETION_SUMMARY.md`

---

**Version**: 2.0  
**Last Updated**: 14 Maret 2026
