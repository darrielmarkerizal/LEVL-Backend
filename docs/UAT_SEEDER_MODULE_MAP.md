# UAT seeder module map

| Module | Tables / purpose | Seeder |
| --- | --- | --- |
| Notifications | `notifications`, `user_notifications` | `NotificationsDatabaseSeeder` |
| Search | `search_history` | `SearchDatabaseSeeder` |
| Dashboard | No dedicated seed tables; aggregates live data | `DashboardDatabaseSeeder` (no-op note) |
| Mail | No outbound log table in schema | `MailDatabaseSeeder` (no-op note) |
| Operations | `certificates`, `reports`, `system_audits` | `OperationsDatabaseSeeder` |
| Enrollments | `enrollment_activities` (timeline) | `EnrollmentActivityTimelineSeeder` |
| Gamification | `points`, `user_badges`, `user_gamification_stats`, `gamification_event_logs` | `LedgerPointsFromLearningFactsSeeder`, `AwardBadgesFromUATMetricsSeeder`, `ReconcileUserGamificationStatsSeeder` |

Default seeding (`SEEDING_MODE` omitted) uses the realistic pipeline: timeline, point ledger, reconcile, badges. Set `SEEDING_MODE=full` in `.env` only if you need the legacy random `GamificationDataSeeder` (load-style data).

**UAT personas** (password `password`):

- `uat.artanto@demo.levl.id` — full timeline (enrollment, lesson, quiz 85+, assignment submitted)
- `uat.steady@demo.levl.id` — one completed lesson
- `uat.struggling@demo.levl.id` — enrolled only
