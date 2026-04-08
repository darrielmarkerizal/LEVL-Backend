# 🚀 VPS Setup Guide - Levl Backend Production

## 📋 Daftar Service yang Harus Disetup

Berdasarkan analisis aplikasi, berikut service yang **WAJIB** disetup di VPS:

### ✅ 1. Laravel Octane (Swoole/FrankenPHP) - SUDAH DISETUP
- **Status:** ✅ Sudah disetup
- **Config:** `OCTANE_SERVER=frankenphp` atau `swoole`
- **Workers:** 2 (sesuai CPU cores)

---

### 🔴 2. **REDIS** - WAJIB!
**Priority: CRITICAL**

Redis digunakan untuk:
- ✅ Cache (`CACHE_STORE=redis`)
- ✅ Queue (`QUEUE_CONNECTION=redis`)
- ✅ Session (optional, saat ini pakai database)

#### Install Redis:
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server -y

# Start & Enable
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test
redis-cli ping
# Output: PONG
```

#### Configure Redis:
```bash
# Edit redis config
sudo nano /etc/redis/redis.conf

# Set password (recommended)
requirepass your_secure_password_here

# Restart
sudo systemctl restart redis-server
```

#### Update .env:
```env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_secure_password_here
REDIS_PORT=6379
```

---

### 🔴 3. **QUEUE WORKER** - WAJIB!
**Priority: CRITICAL**

Aplikasi menggunakan 9 queue dengan prioritas berbeda:

#### Queue List (berdasarkan prioritas):
1. `emails-critical` - Email kritis (password reset, verifikasi)
2. `emails-transactional` - Email transaksional (enrollment, notifikasi)
3. `grading` - Perhitungan nilai dan operasi bulk
4. `notifications` - Notifikasi in-app, gamification XP
5. `file-processing` - Validasi dan storage file
6. `trash` - Operasi trash bin (delete, restore)
7. `logging` - Activity logging dan audit trail
8. `audit` - Audit log grading dan submission
9. `default` - Background tasks umum

#### Setup Queue Worker dengan Supervisor:

**Install Supervisor:**
```bash
sudo apt install supervisor -y
```

**Create Supervisor Config:**
```bash
sudo nano /etc/supervisor/conf.d/levl-queue-worker.conf
```

**Config Content:**
```ini
[program:levl-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/Levl-BE/artisan queue:work redis --queue=emails-critical,emails-transactional,grading,notifications,file-processing,trash,logging,audit,default --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/Levl-BE/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start levl-queue-worker:*

# Check status
sudo supervisorctl status
```

**Monitor Queue:**
```bash
# Check queue status
php artisan queue:monitor redis:emails-critical,redis:emails-transactional,redis:grading

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

### 🔴 4. **CRON SCHEDULER** - WAJIB!
**Priority: CRITICAL**

Aplikasi memiliki scheduled tasks yang harus berjalan:

#### Scheduled Tasks:
1. **Content Publishing** - Setiap 5 menit
2. **Account Cleanup** - Daily
3. **Trash Bin Purge** - Daily
4. **Mark Missing Submissions** - Setiap menit
5. **Post Publishing** - Setiap menit
6. **Orphaned Media Cleanup** - Daily jam 2 pagi

#### Setup Cron:
```bash
# Edit crontab
sudo crontab -e -u www-data

# Add this line:
* * * * * cd /path/to/Levl-BE && php artisan schedule:run >> /dev/null 2>&1
```

**Test Scheduler:**
```bash
# Run manually to test
php artisan schedule:run

# List scheduled tasks
php artisan schedule:list
```

---

### 🟡 5. **POSTGRESQL** - SUDAH ADA
**Priority: MEDIUM**

- **Status:** ✅ Sudah disetup
- **Database:** `levl`
- **Port:** 5432

**Optimizations untuk Production:**
```bash
# Edit postgresql.conf
sudo nano /etc/postgresql/*/main/postgresql.conf

# Recommended settings untuk VPS 2-4GB RAM:
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
work_mem = 2621kB
min_wal_size = 1GB
max_wal_size = 4GB

# Restart
sudo systemctl restart postgresql
```

---

### 🟡 6. **MATERIALIZED VIEW REFRESH** - RECOMMENDED
**Priority: MEDIUM**

Aplikasi menggunakan materialized views untuk leaderboard.

#### Manual Refresh:
```bash
# Refresh leaderboard views
php artisan leaderboard:refresh

# Refresh with concurrent (no locking)
php artisan leaderboard:refresh --concurrent
```

#### Setup Cron untuk Auto-Refresh:
```bash
# Edit crontab
sudo crontab -e -u www-data

# Add this line (refresh every 5 minutes):
*/5 * * * * cd /path/to/Levl-BE && php artisan leaderboard:refresh --concurrent >> /dev/null 2>&1
```

---

### 🟢 7. **MAIL SERVER** - OPTIONAL
**Priority: LOW**

Saat ini config menggunakan SMTP lokal (port 1025 - development).

**Untuk Production, gunakan:**
- **Mailgun** (recommended)
- **SendGrid**
- **Amazon SES**
- **Postmark**

#### Example Mailgun Config:
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

### 🟢 8. **MEILISEARCH** - OPTIONAL
**Priority: LOW**

Untuk full-text search (jika digunakan).

```bash
# Install Meilisearch
curl -L https://install.meilisearch.com | sh

# Run as service
sudo systemctl enable meilisearch
sudo systemctl start meilisearch

# Update .env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_master_key
```

---

## 🔧 Complete Setup Checklist

### Phase 1: Core Services (WAJIB)
- [ ] ✅ Laravel Octane (Swoole/FrankenPHP) - SUDAH
- [ ] 🔴 Redis Server
- [ ] 🔴 Queue Worker (Supervisor)
- [ ] 🔴 Cron Scheduler
- [ ] ✅ PostgreSQL - SUDAH

### Phase 2: Optimization (RECOMMENDED)
- [ ] 🟡 Materialized View Refresh Cron
- [ ] 🟡 PostgreSQL Tuning
- [ ] 🟡 Redis Memory Optimization

### Phase 3: Optional Services
- [ ] 🟢 Mail Server (Mailgun/SendGrid)
- [ ] 🟢 Meilisearch (jika pakai search)

---

## 📝 Environment Variables Checklist

### Production .env Updates:
```env
# App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Cache & Queue (WAJIB REDIS!)
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Redis (WAJIB!)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_secure_password
REDIS_PORT=6379

# Session (bisa tetap database atau pindah ke redis)
SESSION_DRIVER=database  # atau redis untuk performa lebih baik

# Mail (ganti dengan production mail service)
MAIL_MAILER=mailgun
MAIL_FROM_ADDRESS="noreply@your-domain.com"

# Octane
OCTANE_SERVER=frankenphp  # atau swoole
OCTANE_WORKERS=2
OCTANE_MAX_REQUESTS=1000

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
```

---

## 🚨 Critical Commands After Setup

### 1. Clear & Cache Config:
```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Run Migrations:
```bash
php artisan migrate --force
```

### 3. Create Required Tables:
```bash
# Queue tables
php artisan queue:table
php artisan migrate

# Cache table
php artisan cache:table
php artisan migrate

# Session table (jika pakai database)
php artisan session:table
php artisan migrate
```

### 4. Test Queue:
```bash
# Dispatch test job
php artisan tinker
>>> dispatch(function() { logger('Queue is working!'); });

# Check logs
tail -f storage/logs/laravel.log
```

### 5. Test Scheduler:
```bash
php artisan schedule:run
php artisan schedule:list
```

---

## 📊 Monitoring Commands

### Queue Monitoring:
```bash
# Check queue size
php artisan queue:monitor redis:emails-critical,redis:grading

# Check failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all

# Clear failed
php artisan queue:flush
```

### Redis Monitoring:
```bash
# Connect to redis
redis-cli -a your_password

# Check memory usage
INFO memory

# Check connected clients
CLIENT LIST

# Monitor commands
MONITOR
```

### Supervisor Monitoring:
```bash
# Status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart levl-queue-worker:*

# View logs
sudo supervisorctl tail levl-queue-worker:levl-queue-worker_00
```

---

## 🔥 Performance Optimization

### 1. OPcache (PHP):
```bash
# Install
sudo apt install php8.3-opcache

# Configure
sudo nano /etc/php/8.3/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Redis Optimization:
```bash
# Edit redis.conf
sudo nano /etc/redis/redis.conf
```

```conf
maxmemory 512mb
maxmemory-policy allkeys-lru
save ""  # Disable RDB snapshots for cache-only
```

### 3. PostgreSQL Connection Pooling (PgBouncer):
```bash
sudo apt install pgbouncer -y
```

---

## 🛡️ Security Checklist

- [ ] Set strong Redis password
- [ ] Configure firewall (UFW)
- [ ] Enable HTTPS (SSL/TLS)
- [ ] Set `APP_DEBUG=false`
- [ ] Set secure session cookies
- [ ] Restrict database access
- [ ] Setup fail2ban
- [ ] Regular backups

---

## 📞 Troubleshooting

### Queue Not Processing:
```bash
# Check supervisor status
sudo supervisorctl status

# Check logs
tail -f storage/logs/queue-worker.log

# Restart workers
sudo supervisorctl restart levl-queue-worker:*
```

### Redis Connection Failed:
```bash
# Test connection
redis-cli -a your_password ping

# Check if running
sudo systemctl status redis-server

# Check logs
sudo tail -f /var/log/redis/redis-server.log
```

### Scheduler Not Running:
```bash
# Check crontab
sudo crontab -l -u www-data

# Test manually
php artisan schedule:run

# Check logs
tail -f storage/logs/laravel.log
```

---

## 🎯 Summary

### WAJIB Setup (Tidak Bisa Jalan Tanpa Ini):
1. ✅ **Laravel Octane** - Sudah disetup
2. 🔴 **Redis** - WAJIB untuk cache & queue
3. 🔴 **Queue Worker** - WAJIB untuk background jobs
4. 🔴 **Cron Scheduler** - WAJIB untuk scheduled tasks

### Recommended:
5. 🟡 **Materialized View Refresh** - Untuk leaderboard
6. 🟡 **PostgreSQL Tuning** - Untuk performa

### Optional:
7. 🟢 **Mail Service** - Ganti SMTP development
8. 🟢 **Meilisearch** - Jika pakai search

---

**Prioritas Setup:**
1. Redis (CRITICAL)
2. Queue Worker (CRITICAL)
3. Cron Scheduler (CRITICAL)
4. Materialized View Refresh (RECOMMENDED)
5. Mail Service (OPTIONAL)

Tanpa Redis, Queue Worker, dan Cron Scheduler, aplikasi tidak akan berfungsi dengan baik!
