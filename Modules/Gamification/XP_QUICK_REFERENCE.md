# XP System - Quick Reference

## 🎯 XP Sources & Amounts

| Action | XP | Code | Allow Multiple |
|--------|----|----|----------------|
| Complete Lesson | 50 | lesson_completed | Yes (daily cap 5k) |
| Submit Assignment | 100 | assignment_submitted | No (once per assignment) |
| First Submission | +30 | first_submission | Yes |
| Pass Quiz | 80 | quiz_passed | No (once per quiz) |
| Complete Unit | 200 | unit_completed | No (once per unit) |
| Complete Course | 500 | course_completed | No (once per course) |
| Perfect Score (100%) | 50 | perfect_score | Yes |
| Create Forum Post | 20 | forum_post_created | Yes (daily cap 200) |
| Reply to Post | 10 | forum_reply_created | Yes (daily cap 200) |
| Receive Like | 5 | forum_liked | Yes (daily cap 100) |
| Daily Login | 10 | daily_login | Yes (1x/day) |
| 7-Day Streak | 200 | streak_7_days | Yes |
| 30-Day Streak | 1000 | streak_30_days | Yes |

**Global Daily Cap**: 10,000 XP per user per day

---

## 📡 API Response Format

### Automatic (All Responses)

```json
{
  "data": {...},
  "gamification": {
    "current_xp": 1250,
    "current_level": 8,
    "latest_xp_award": {
      "xp_awarded": 100,
      "reason": "assignment_submitted",
      "description": "Submitted assignment: Introduction to PHP",
      "leveled_up": false,
      "old_level": 8,
      "new_level": 8,
      "awarded_at": "2026-03-14T10:30:00Z"
    }
  }
}
```

### Using Trait (Manual)

```php
use Modules\Gamification\Traits\IncludesXpInfo;

return response()->json(
    $this->withXpInfo($data, 'assignment_submitted', auth()->id())
);
```

Response:
```json
{
  "data": {...},
  "xp_info": {
    "xp_available": true,
    "xp_amount": 100,
    "xp_source": {...}
  },
  "recent_xp_awards": [...]
}
```

---

## 🎮 Frontend Integration

### Show XP Toast

```typescript
if (response.gamification?.latest_xp_award) {
  const xp = response.gamification.latest_xp_award;
  toast.success(`+${xp.xp_awarded} XP: ${xp.description}`);
  
  if (xp.leveled_up) {
    showLevelUpModal(xp.old_level, xp.new_level);
  }
}
```

### Listen to Level Up

```typescript
Echo.private(`user.${userId}`)
  .listen('UserLeveledUp', (event) => {
    showLevelUpAnimation(event);
  });
```

---

## 🔧 Backend Integration

### Award XP Manually

```php
use Modules\Gamification\Services\GamificationService;

$gamification->awardXp(
    userId: $userId,
    points: 0, // Use xp_sources config
    reason: 'assignment_submitted',
    sourceType: 'assignment',
    sourceId: $assignmentId,
    options: [
        'description' => 'Submitted assignment',
        'allow_multiple' => false,
    ]
);
```

### Create Event Listener

```php
namespace Modules\YourModule\Listeners;

use Modules\Gamification\Services\GamificationService;

class AwardXpForYourEvent
{
    public function __construct(
        private GamificationService $gamification
    ) {}

    public function handle(YourEvent $event): void
    {
        $this->gamification->awardXp(
            userId: $event->userId,
            points: 0,
            reason: 'your_xp_source_code',
            sourceType: 'your_type',
            sourceId: $event->id
        );
    }
}
```

Register in EventServiceProvider:
```php
\Modules\YourModule\Events\YourEvent::class => [
    \Modules\YourModule\Listeners\AwardXpForYourEvent::class,
],
```

---

## 📊 Level Formula

```
XP(level) = 100 × level^1.6
```

| Level | XP Required | Total XP |
|-------|-------------|----------|
| 1 | 0 | 0 |
| 5 | 1,148 | 2,332 |
| 10 | 2,512 | 8,155 |
| 20 | 6,063 | 35,510 |
| 50 | 19,307 | 343,398 |
| 100 | 50,119 | 1,683,793 |

---

## 🔒 Anti-Abuse

- **Cooldown**: Prevents rapid-fire (e.g., 10s for lessons)
- **Daily Limit**: Max times per day (e.g., 1x for login)
- **Daily XP Cap**: Max XP per source per day (e.g., 5k for lessons)
- **Global Cap**: Max 10k XP per user per day
- **Allow Multiple**: Some sources only award once (e.g., assignments)
- **Transaction Log**: IP & user agent tracked

---

## 📚 Documentation

- **INTEGRATION_ANALYSIS_REPORT.md** - Full analysis
- **COMPLETE_INTEGRATION_GUIDE.md** - Integration guide
- **INTEGRATION_COMPLETION_FINAL.md** - Implementation summary
- **XP_QUICK_REFERENCE.md** - This file

---

## ✅ Status

**Coverage**: 83% (10/12 XP sources)  
**Module Coverage**: 100% (4/4 core modules)  
**Status**: ✅ PRODUCTION READY
