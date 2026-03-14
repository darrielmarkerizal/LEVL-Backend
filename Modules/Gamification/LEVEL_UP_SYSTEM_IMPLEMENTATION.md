# Level Up System Implementation

## Overview

Sistem Level Up yang lengkap dengan event broadcasting, XP source management, dan anti-abuse mechanism.

---

## 1. Level Up Event System

### Event: `UserLeveledUp`

Event ini di-trigger otomatis saat user naik level.

**Event Data:**
```php
[
    'event' => 'level_up',
    'user_id' => 123,
    'old_level' => 14,
    'new_level' => 15,
    'total_xp' => 50000,
    'rewards' => [
        'badge' => 'level_15_milestone',
        'bonus_xp' => 200,
        'title' => 'Intermediate Master'
    ],
    'timestamp' => '2026-03-14T10:30:00Z'
]
```

### Broadcasting

Event di-broadcast ke channel `user.{userId}` dengan nama `level.up`.

**Frontend Listening (Laravel Echo):**
```javascript
Echo.channel(`user.${userId}`)
    .listen('.level.up', (event) => {
        console.log('User leveled up!', event);
        
        // Show notification
        showLevelUpNotification({
            oldLevel: event.old_level,
            newLevel: event.new_level,
            rewards: event.rewards
        });
        
        // Play animation
        playLevelUpAnimation();
        
        // Refresh user stats
        refetchUserLevel();
    });
```

### Event Listener: `HandleLevelUp`

Listener ini handle rewards saat user level up:

1. **Award Milestone Badge** - Jika ada badge di rewards
2. **Award Bonus XP** - Jika ada bonus_xp di rewards
3. **Log Event** - Log untuk monitoring
4. **Additional Rewards** - Extensible untuk rewards lainnya

---

## 2. XP Source Management

### Table: `xp_sources`

Tabel konfigurasi untuk semua sumber XP di sistem.

**Columns:**
- `code` - Unique identifier (e.g., 'lesson_completed')
- `name` - Display name
- `description` - Description
- `xp_amount` - XP yang diberikan
- `cooldown_seconds` - Cooldown antar action yang sama
- `daily_limit` - Max berapa kali per hari (null = unlimited)
- `daily_xp_cap` - Max XP per hari dari source ini
- `allow_multiple` - Bisa earn multiple times dari source_id yang sama
- `is_active` - Active/inactive
- `metadata` - Additional config (JSON)

### Default XP Sources

| Code | XP | Cooldown | Daily Limit | Daily XP Cap | Allow Multiple |
|------|----|---------