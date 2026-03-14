# ✅ Implementation Checklist: Production-Grade Badge System

## 🎯 Quick Summary

**Goal:** Upgrade dari 7/10 → 9.7/10 (Duolingo-level)

**Critical Fixes:**
1. Event Counter System (prevent hotspot)
2. Rules-by-Event Cache (90% CPU reduction)
3. Event Log Table (prevent metadata bloat)
4. Badge Versioning (handle rule changes)
5. Rule Priority & Cooldown (optimization)

**Timeline:** 5 weeks
**Team:** 2 developers
**Estimated Cost:** $20,000

---

## 📋 Phase 1: Critical Fixes (Week 1-2)

### Day 1-2: Event Counter System

- [ ] Create migration `user_event_counters`
- [ ] Create model `UserEventCounter`
- [ ] Create service `EventCounterService`
- [ ] Update all event listeners to increment counters
- [ ] Write unit tests for counter service
- [ ] Test window expiration logic

**Files to Create:**
```
database/migrations/YYYY_MM_DD_create_user_event_counters_table.php
app/Models/UserEventCounter.php
app/Services/EventCounterService.php
tests/Unit/Services/EventCounterServiceTest.php
```

**Files to Update:**
```
app/Listeners/AwardXpForLessonCompleted.php
app/Listeners/AwardXpForUnitCompleted.php
app/Listeners/AwardBadgeForCourseCompleted.php
app/Listeners/AwardXpForGradeReleased.php
```

### Day 3-4: Rules-by-Event Cache

- [ ] Update `BadgeRuleEvaluator` to use indexed cache
- [ ] Create command `WarmBadgeRulesCache`
- [ ] Update `BadgeService` cache invalidation
- [ ] Add cache warming to deployment script
- [ ] Write tests for cache behavior

**Files to Create:**
```
app/Console/Commands/WarmBadgeRulesCache.php
```

**Files to Update:**
```
app/Services/Support/BadgeRuleEvaluator.php
app/Services/BadgeService.php
```

### Day 5-6: Event Log Table

- [ ] Create migration `gamification_event_logs`
- [ ] Create model `GamificationEventLog`
- [ ] Create service `EventLoggerService`
- [ ] Update listeners to log events
- [ ] Create cleanup command
- [ ] Schedule cleanup in Kernel

**Files to Create:**
```
database/migrations/YYYY_MM_DD_create_gamification_event_logs_table.php
app/Models/GamificationEventLog.php
app/Services/EventLoggerService.php
app/Console/Commands/CleanupOldEventLogs.php
```

**Files to Update:**
```
app/Console/Kernel.php (add schedule)
```

### Day 7-8: Badge Versioning

- [ ] Create migration `badge_versions`
- [ ] Create model `BadgeVersion`
- [ ] Create service `BadgeVersionService`
- [ ] Update `user_badge_progress` to reference version
- [ ] Create migration command
- [ ] Write tests for version management

**Files to Create:**
```
database/migrations/YYYY_MM_DD_create_badge_versions_table.php
app/Models/BadgeVersion.php
app/Services/BadgeVersionService.php
app/Console/Commands/MigrateProgressToNewVersion.php
```

### Day 9-10: Rule Priority & Cooldown

- [ ] Add `priority` and `cooldown_seconds` to `badge_rules`
- [ ] Create migration `badge_rule_cooldowns`
- [ ] Create model `BadgeRuleCooldown`
- [ ] Update `BadgeRuleEvaluator` with priority logic
- [ ] Update seeder with priorities
- [ ] Write tests for cooldown behavior

**Files to Create:**
```
database/migrations/YYYY_MM_DD_add_priority_cooldown_to_badge_rules.php
database/migrations/YYYY_MM_DD_create_badge_rule_cooldowns_table.php
app/Models/BadgeRuleCooldown.php
```

**Files to Update:**
```
app/Services/Support/BadgeRuleEvaluator.php
database/seeders/BadgeSeeder.php
```

---

## 📋 Phase 2: Testing & Optimization (Week 3)

### Day 11-12: Unit Tests

- [ ] Test `EventCounterService`
  - [ ] Increment counter
  - [ ] Window expiration
  - [ ] Multiple scopes
- [ ] Test `BadgeRuleEvaluator`
  - [ ] Priority ordering
  - [ ] Cooldown enforcement
  - [ ] Counter-based evaluation
- [ ] Test `BadgeVersionService`
  - [ ] Version creation
  - [ ] Version activation
  - [ ] Progress migration

**Target Coverage:** 90%+

### Day 13-14: Integration Tests

- [ ] Test complete badge flow
  - [ ] Event → Counter → Evaluation → Award
- [ ] Test window-based badges
  - [ ] Daily challenges
  - [ ] Weekly challenges
- [ ] Test tier progression
- [ ] Test repeatable badges

### Day 15: Performance Testing

- [ ] Load test: 1,000 events/sec
- [ ] Stress test: 5,000 events/sec
- [ ] Database query profiling
- [ ] Cache hit rate analysis
- [ ] Queue processing speed

**Tools:**
- Apache JMeter
- Laravel Telescope
- New Relic / Datadog

---

## 📋 Phase 3: Deployment (Week 4)

### Day 16-17: Staging Deployment

- [ ] Deploy to staging environment
- [ ] Run migration scripts
- [ ] Warm caches
- [ ] Seed test data
- [ ] Manual QA testing

### Day 18-19: Canary Deployment

- [ ] Deploy to 5% production users
- [ ] Monitor error rates
- [ ] Monitor performance metrics
- [ ] Monitor queue depth
- [ ] Collect user feedback

**Rollback Criteria:**
- Error rate > 1%
- Response time > 200ms
- Queue depth > 10,000

### Day 20: Full Deployment

- [ ] Deploy to 100% users
- [ ] Monitor for 24 hours
- [ ] Document any issues
- [ ] Create incident response plan

---

## 📋 Phase 4: Monitoring & Optimization (Week 5)

### Day 21-22: Monitoring Setup

- [ ] Setup Prometheus metrics
  - [ ] Badge evaluation time
  - [ ] Counter increment time
  - [ ] Queue processing time
  - [ ] Cache hit rate
- [ ] Create Grafana dashboards
- [ ] Setup alert rules
  - [ ] High error rate
  - [ ] Slow queries
  - [ ] Queue backlog

### Day 23-24: Documentation

- [ ] Update API documentation
- [ ] Write deployment guide
- [ ] Write troubleshooting guide
- [ ] Create runbook for ops team

### Day 25: Post-Launch Review

- [ ] Analyze metrics
- [ ] Identify bottlenecks
- [ ] Plan optimizations
- [ ] Celebrate success! 🎉

---

## 🔍 Testing Checklist

### Unit Tests (90%+ coverage)

- [ ] `EventCounterService::increment()`
- [ ] `EventCounterService::getCounter()`
- [ ] `EventCounterService::getWindowBounds()`
- [ ] `BadgeRuleEvaluator::evaluate()`
- [ ] `BadgeRuleEvaluator::canEvaluate()`
- [ ] `BadgeVersionService::createNewVersion()`
- [ ] `EventLoggerService::log()`

### Integration Tests

- [ ] Complete badge award flow
- [ ] Window-based progress tracking
- [ ] Tier progression
- [ ] Repeatable badges
- [ ] Badge versioning migration

### Performance Tests

- [ ] 1,000 events/sec sustained
- [ ] 5,000 events/sec burst
- [ ] Database query < 50ms
- [ ] Cache hit rate > 95%
- [ ] Queue processing < 100ms

---

## 🚨 Rollback Plan

### If Issues Detected

**Step 1: Stop Deployment**
```bash
# Revert to previous version
git revert HEAD
php artisan deploy:rollback
```

**Step 2: Database Rollback**
```bash
# Rollback migrations
php artisan migrate:rollback --step=5
```

**Step 3: Cache Clear**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**Step 4: Restart Services**
```bash
# Restart queue workers
php artisan queue:restart

# Restart application
sudo systemctl restart php-fpm
```

---

## 📊 Success Metrics

### Technical Metrics

| Metric | Before | Target | Actual |
|--------|--------|--------|--------|
| Badge evaluation time | 100ms | 15ms | ___ |
| Counter increment time | 50ms | 5ms | ___ |
| Rules loaded per event | 500 | 10 | ___ |
| Max throughput | 200/sec | 2,000/sec | ___ |
| Cache hit rate | 80% | 95% | ___ |

### Business Metrics

| Metric | Before | Target | Actual |
|--------|--------|--------|--------|
| Daily active users | Baseline | +30% | ___ |
| Course completion | 45% | 65% | ___ |
| 30-day retention | 60% | 85% | ___ |
| Session duration | 15min | 22min | ___ |

---

## 🛠️ Required Tools & Services

### Development
- [ ] PHP 8.2+
- [ ] Laravel 11+
- [ ] PostgreSQL 15+
- [ ] Redis 7+
- [ ] Composer

### Testing
- [ ] PHPUnit
- [ ] Laravel Telescope
- [ ] Apache JMeter
- [ ] Postman

### Monitoring
- [ ] Prometheus
- [ ] Grafana
- [ ] New Relic / Datadog
- [ ] Sentry

### Infrastructure
- [ ] Queue workers (3+ instances)
- [ ] Redis cluster
- [ ] Database read replicas
- [ ] Load balancer

---

## 👥 Team Responsibilities

### Backend Developer 1
- Event counter system
- Rules-by-event cache
- Unit tests

### Backend Developer 2
- Event log table
- Badge versioning
- Integration tests

### DevOps Engineer
- Deployment automation
- Monitoring setup
- Performance testing

### QA Engineer
- Manual testing
- Load testing
- Bug reporting

---

## 📞 Support Contacts

**Technical Lead:** _______________
**DevOps Lead:** _______________
**Product Manager:** _______________
**On-Call Engineer:** _______________

---

## ✅ Final Sign-Off

- [ ] All tests passing
- [ ] Code review completed
- [ ] Documentation updated
- [ ] Monitoring configured
- [ ] Rollback plan tested
- [ ] Team trained
- [ ] Stakeholders informed

**Approved by:**
- Technical Lead: _______________ Date: ___________
- Product Manager: _______________ Date: ___________
- DevOps Lead: _______________ Date: ___________

---

**Last Updated:** March 14, 2026
**Version:** 1.0
**Status:** Ready for Implementation
