# Level Up System Implementation Summary

## ✅ Completed Implementation

### 1. Level Up Event System

**Files Created:**
- `app/Events/UserLeveledUp.php` - Event yang di-trigger saat user level up
- `app/Listeners/HandleLevelUp.php` - Listener untuk handle rewards

**Features:**
- Real-time broadcasting ke channel `user.{userId}`
- Event name: `level.up`
- Automatic reward processing (badges, bonus XP)
- Event logging untuk monitoring

**Event Data:**
```json
{
  "event": "level_up",
  "user_id": 123,
  "old_level": 14,
  "new_level": 15,
  "total_xp": 50000,
  "rewards": {
    "badge": "level_15_milestone",
    "bonus_xp": 200
  }
}
```

---

### 2. XP Source Management

**Files Created:**
- `database/migrations/2026_03_14_110000_create_xp_sources_table.php`
- `app/Models/XpSource.php`
- `database/seeders/XpSourceSeeder.php`

**Features:**
- Centralized XP configuration
- 15 default XP sources (learning, engagement, social, quality)
- Flexible configuration per source

**XP Sources:**
- Learning: lesson_completed (50 XP), assignment_submitted (100 XP), quiz_passed (80 XP)
- Engagement: daily_login (10 XP), streak_7_days (200 XP), streak_30_days (1000 XP)
- Social: forum_post_created (20 XP), forum_reply_created (10 XP), forum_liked (5 XP)
- Quality: perfect_score (50 XP), first_submission (30 XP)

---

### 3. Anti-Abuse Mechanisms

**Updated Files:**
- `app/Services/Support/PointManager.php` - Enhanced dengan XP source integration

**Mechanisms:**

1. **Cooldown System**
   - Prevents spam by limiting action frequency
   - Example: lesson_completed has 10 second cooldown

2. **Daily Limit**
   - Limits how many times user can earn XP per day
   - Example: daily_login limited to 1 time per day

3. **Daily XP Cap**
   - Limits total XP from specific source per day
   - Example: lesson_completed capped at 5,000 XP/day

4. **Allow Multiple**
   - Controls if user can earn XP multiple times from same source_id
   - Example: assignment_submitted = false (once per assignment)

---

### 4. Event Registration

**Updated Files:**
- `app/Providers/EventServiceProvider.php` - Added UserLeveledUp event listener

---

### 5. Database Seeder

**Updated Files:**
- `database/seeders/GamificationDatabaseSeeder.php` - Added XpSourceSeeder

**Seeder Order:**
1. LevelConfigSeeder
2. XpSourceSeeder (NEW)
3. MilestoneSeeder
4. BadgeSeeder

---

### 6. Documentation

**Updated Files:**
- `PANDUAN_LEVEL_MANAGEMENT_LENGKAP.md` - Added sections 9 & 10

**New Sections:**
- Section 9: Level Up Event System
- Section 10: XP Source Management
- Enhanced UI/UX notes with real-time event handling

---

## 🔄 How It Works

### XP Award Flow

```
1. User completes activity (e.g., lesson)
   ↓
2. System calls PointManager->awardXp()
   ↓
3. Look up XP source config by reason code
   ↓
4. Apply anti-abuse checks:
   - Cooldown check
   - Daily limit check
   - Daily XP cap check
   - Allow multiple check
   ↓
5. If all checks pass:
   - Get user's old level
   - Award XP
   - Calculate new level
   ↓
6. If level increased:
   - Get level config rewards
   - Dispatch UserLeveledUp event
   - Broadcast to frontend
   - Award milestone rewards
   ↓
7. Frontend receives event:
   - Show level up notification
   - Play celebration animation
   - Refresh user stats
```

---

## 📊 Database Schema

### xp_sources Table

```sql
CREATE TABLE xp_sources (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR UNIQUE,
    name VARCHAR,
    description TEXT,
    xp_amount INTEGER DEFAULT 0,
    cooldown_seconds INTEGER DEFAULT 0,
    daily_limit INTEGER,
    daily_xp_cap INTEGER,
    allow_multiple BOOLEAN DEFAULT true,
    is_active BOOLEAN DEFAULT true,
    metadata JSONB,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🚀 Next Steps

### To Deploy:

1. **Run Migrations:**
```bash
php artisan migrate
```

2. **Run Seeders:**
```bash
php artisan db:seed --class=Modules\\Gamification\\Database\\Seeders\\XpSourceSeeder
```

3. **Configure Broadcasting:**
- Ensure Laravel Echo is configured
- Set up Pusher/Redis broadcasting
- Configure channels in `broadcasting.php`

4. **Frontend Integration:**
- Install Laravel Echo
- Subscribe to user channel
- Listen to `level.up` event
- Implement level up notification UI

---

## 🧪 Testing

### Test Level Up Event:

```php
// Award enough XP to trigger level up
$pointManager->awardXp(
    userId: 1,
    points: 0,
    reason: 'lesson_completed',
    sourceType: 'lesson',
    sourceId: 123
);

// Check if event was dispatched
Event::assertDispatched(UserLeveledUp::class);
```

### Test Anti-Abuse:

```php
// Test cooldown
$pointManager->awardXp(1, 0, 'lesson_completed', 'lesson', 1);
$pointManager->awardXp(1, 0, 'lesson_completed', 'lesson', 2); // Should fail (cooldown)

// Test daily limit
for ($i = 0; $i < 5; $i++) {
    $pointManager->awardXp(1, 0, 'daily_login', 'system', null);
}
// Second attempt should fail (daily limit = 1)
```

---

## 📝 Configuration Examples

### Adjust XP Amount:

```sql
UPDATE xp_sources 
SET xp_amount = 75 
WHERE code = 'lesson_completed';
```

### Remove Daily Cap:

```sql
UPDATE xp_sources 
SET daily_xp_cap = NULL 
WHERE code = 'assignment_submitted';
```

### Disable Source:

```sql
UPDATE xp_sources 
SET is_active = false 
WHERE code = 'forum_post_created';
```

---

## 🎯 Key Benefits

1. **Centralized Configuration** - All XP sources in one place
2. **Anti-Abuse Built-in** - Multiple layers of protection
3. **Real-time Feedback** - Instant level up notifications
4. **Flexible Rewards** - Easy to customize per level
5. **Scalable** - Easy to add new XP sources
6. **Monitoring Ready** - Event logging for analytics

---

**Implementation Date**: 14 Maret 2026  
**Status**: ✅ Complete  
**Version**: 1.0
