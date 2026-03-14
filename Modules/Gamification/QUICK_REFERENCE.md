# 🚀 Quick Reference Card

## 📦 New Services

### EventCounterService
```php
use Modules\Gamification\Services\EventCounterService;

$service = app(EventCounterService::class);

// Increment counter
$service->increment($userId, 'lesson_completed', 'course', $courseId, 'daily');

// Get counter
$count = $service->getCounter($userId, 'lesson_completed', 'course', $courseId, 'daily');

// Get all user counters
$counters = $service->getUserCounters($userId, 'lesson_completed');
```

### EventLoggerService
```php
use Modules\Gamification\Services\EventLoggerService;

$service = app(EventLoggerService::class);

// Log event
$service->log($userId, 'lesson_completed', 'lesson', $lessonId, [
    'score' => 95,
    'attempt' => 1,
]);

// Get history
$history = $service->getUserEventHistory($userId, 'lesson_completed', 100);
```

### BadgeVersionService
```php
use Modules\Gamification\Services\BadgeVersionService;

$service = app(BadgeVersionService::class);

// Create new version
$version = $service->createNewVersion($badgeId, $threshold, $rules);

// Get active version
$version = $service->getActiveVersion($badgeId);
```

---

## 🎯 Common Patterns

### Pattern 1: Event Listener
```php
public function __construct(
    private GamificationService $gamification,
    private EventCounterService $counterService,
    private EventLoggerService $loggerService,
    private BadgeRuleEvaluator $evaluator
) {}

public function handle($event): void
{
    // 1. Award XP
    $this->gamification->awardXp($userId, $xp, 'completion', 'lesson', $lessonId);
    
    // 2. Log event
    $this->loggerService->log($userId, 'lesson_completed', 'lesson', $lessonId, $payload);
    
    // 3. Increment counters
    $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'lifetime');
    $this->counterService->increment($userId, 'lesson_completed', 'global', null, 'daily');
    $this->counterService->increment($userId, 'lesson_completed', 'course', $courseId, 'lifetime');
    
    // 4. Evaluate badges
    $this->evaluator->evaluate($user, 'lesson_completed', $payload);
}
```

### Pattern 2: Badge Rule with Priority
```json
{
  "badge_id": 1,
  "event_trigger": "lesson_completed",
  "conditions": {"min_score": 90},
  "priority": 100,
  "cooldown_seconds": 600,
  "progress_window": "daily"
}
```

### Pattern 3: Counter Windows
```php
// Lifetime (never expires)
$service->increment($userId, 'lesson_completed', 'global', null, 'lifetime');

// Daily (resets every day)
$service->increment($userId, 'lesson_completed', 'global', null, 'daily');

// Weekly (resets every week)
$service->increment($userId, 'lesson_completed', 'global', null, 'weekly');

// Monthly (resets every month)
$service->increment($userId, 'lesson_completed', 'global', null, 'monthly');
```

---

## 🔧 Artisan Commands

```bash
# Warm badge rules cache
php artisan gamification:warm-cache

# Cleanup old event logs (keep 90 days)
php artisan gamification:cleanup-logs --days=90

# Cleanup expired counters
php artisan gamification:cleanup-counters

# Create initial badge versions
php artisan gamification:create-initial-versions
```

---

## 📊 Database Tables

### user_event_counters
```
id, user_id, event_type, scope_type, scope_id, 
counter, window, window_start, window_end, last_increment_at
```

### gamification_event_logs
```
id, user_id, event_type, source_type, source_id, 
payload, created_at
```

### badge_versions
```
id, badge_id, version, threshold, rules, 
effective_from, effective_until, is_active
```

### badge_rule_cooldowns
```
id, user_id, badge_rule_id, last_evaluated_at, can_evaluate_after
```

---

## 🎯 Event Types

- `lesson_completed`
- `unit_completed`
- `course_completed`
- `assignment_submitted`
- `assignment_graded`
- `quiz_graded`
- `login`
- `forum_post_created`
- `forum_reply_created`
- `forum_liked`

---

## 📈 Performance Tips

1. **Always use indexed cache** - Rules are cached by event
2. **Increment counters in batch** - Multiple windows at once
3. **Use appropriate window** - Lifetime for cumulative, daily for challenges
4. **Set priority** - Important badges first (higher number = higher priority)
5. **Use cooldown** - Prevent spam (in seconds)

---

## 🐛 Debugging

```bash
# Check cache
php artisan tinker
>>> Cache::tags(['gamification', 'rules'])->get('gamification.rules_by_event.lesson_completed');

# Check counter
>>> $service = app(\Modules\Gamification\Services\EventCounterService::class);
>>> $service->getCounter(1, 'lesson_completed', 'global', null, 'daily');

# Check logs
>>> \Modules\Gamification\Models\GamificationEventLog::latest()->take(10)->get();

# Check versions
>>> \Modules\Gamification\Models\BadgeVersion::where('is_active', true)->count();
```

---

## ✅ Checklist for New Event

When adding new event type:

- [ ] Create event class
- [ ] Create listener
- [ ] Inject services (counter, logger, evaluator)
- [ ] Award XP
- [ ] Log event
- [ ] Increment counters (lifetime, daily, weekly)
- [ ] Evaluate badges
- [ ] Register in EventServiceProvider
- [ ] Create badge rules
- [ ] Warm cache
- [ ] Test

---

**Quick Reference v1.0** | Last Updated: March 14, 2026
