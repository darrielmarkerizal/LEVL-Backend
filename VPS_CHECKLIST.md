# ✅ VPS Setup Checklist - Levl Backend

## 🔴 CRITICAL (Wajib Setup)

### 1. Redis Server
- [ ] Install Redis: `sudo apt install redis-server -y`
- [ ] Set password di `/etc/redis/redis.conf`
- [ ] Start service: `sudo systemctl start redis-server`
- [ ] Enable auto-start: `sudo systemctl enable redis-server`
- [ ] Test: `redis-cli -a password ping` → harus return "PONG"
- [ ] Update `.env`:
  ```env
  CACHE_STORE=redis
  QUEUE_CONNECTION=redis
  REDIS_PASSWORD=your_password
  ```

**Kenapa Wajib?**
- Cache aplikasi pakai Redis
- Queue system pakai Redis
- Tanpa Redis, background jobs tidak jalan!

---

### 2. Queue Worker (Supervisor)
- [ ] Install Supervisor: `sudo apt install supervisor -y`
- [ ] Create config: `/etc/supervisor/conf.d/levl-queue-worker.conf`
- [ ] Config content:
  ```ini
  [program:levl-queue-worker]
  command=php /path/to/artisan queue:work redis --queue=emails-critical,emails-transactional,grading,notifications,file-processing,trash,logging,audit,default
  numprocs=2
  user=www-data
  autostart=true
  autorestart=true
  ```
- [ ] Start: `sudo supervisorctl reread && sudo supervisorctl update`
- [ ] Check status: `sudo supervisorctl status`

**Kenapa Wajib?**
- Email tidak akan terkirim tanpa queue worker
- Grading calculation tidak jalan
- File processing tidak jalan
- Audit logging tidak jalan

**Queue yang digunakan:**
1. `emails-critical` - Password reset, verifikasi
2. `emails-transactional` - Enrollment, notifikasi
3. `grading` - Perhitungan nilai
4. `notifications` - Notifikasi in-app
5. `file-processing` - Upload file
6. `trash` - Delete/restore
7. `logging` - Activity logs
8. `audit` - Audit trail
9. `default` - General tasks

---

### 3. Cron Scheduler
- [ ] Edit crontab: `sudo crontab -e -u www-data`
- [ ] Add line:
  ```bash
  * * * * * cd /path/to/Levl-BE && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] Test: `php artisan schedule:list`

**Kenapa Wajib?**
- Content publishing tidak jalan (setiap 5 menit)
- Account cleanup tidak jalan (daily)
- Trash purge tidak jalan (daily)
- Missing submissions tidak ter-mark (setiap menit)
- Post publishing tidak jalan (setiap menit)
- Orphaned media tidak ter-cleanup (daily)

**Scheduled Tasks:**
- ✅ Content Publishing - Every 5 minutes
- ✅ Account Cleanup - Daily
- ✅ Trash Bin Purge - Daily
- ✅ Mark Missing Submissions - Every minute
- ✅ Post Publishing - Every minute
- ✅ Orphaned Media Cleanup - Daily at 2 AM

---

## 🟡 RECOMMENDED (Sangat Disarankan)

### 4. Materialized View Refresh
- [ ] Add to crontab:
  ```bash
  */5 * * * * cd /path/to/Levl-BE && php artisan leaderboard:refresh --concurrent >> /dev/null 2>&1
  ```
- [ ] Test manual: `php artisan leaderboard:refresh`

**Kenapa Disarankan?**
- Leaderboard data akan stale tanpa refresh
- Performa leaderboard lebih cepat dengan materialized view

---

### 5. PostgreSQL Optimization
- [ ] Edit `/etc/postgresql/*/main/postgresql.conf`
- [ ] Set untuk VPS 2-4GB RAM:
  ```conf
  shared_buffers = 256MB
  effective_cache_size = 1GB
  work_mem = 2621kB
  ```
- [ ] Restart: `sudo systemctl restart postgresql`

---

### 6. OPcache
- [ ] Install: `sudo apt install php8.3-opcache -y`
- [ ] Configure: `/etc/php/8.3/fpm/conf.d/10-opcache.ini`
- [ ] Restart PHP-FPM: `sudo systemctl restart php8.3-fpm`

---

## 🟢 OPTIONAL (Opsional)

### 7. Mail Service
- [ ] Ganti dari SMTP development ke production
- [ ] Options: Mailgun, SendGrid, Amazon SES
- [ ] Update `.env`:
  ```env
  MAIL_MAILER=mailgun
  MAILGUN_DOMAIN=your-domain.com
  MAILGUN_SECRET=your-api-key
  ```

---

### 8. Meilisearch (Full-text Search)
- [ ] Install Meilisearch
- [ ] Update `.env`:
  ```env
  SCOUT_DRIVER=meilisearch
  MEILISEARCH_HOST=http://127.0.0.1:7700
  ```

---

## 🚀 Quick Setup Commands

### One-liner Setup (Run as root):
```bash
# Download and run setup script
wget https://your-repo/VPS_QUICK_SETUP.sh
chmod +x VPS_QUICK_SETUP.sh
sudo bash VPS_QUICK_SETUP.sh
```

### Manual Setup:
```bash
# 1. Install Redis
sudo apt install redis-server -y
sudo systemctl start redis-server
sudo systemctl enable redis-server

# 2. Install Supervisor
sudo apt install supervisor -y

# 3. Setup Queue Worker (create config file first)
sudo supervisorctl reread
sudo supervisorctl update

# 4. Setup Cron
sudo crontab -e -u www-data
# Add: * * * * * cd /path/to/Levl-BE && php artisan schedule:run >> /dev/null 2>&1

# 5. Laravel Setup
cd /path/to/Levl-BE
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

---

## 🧪 Testing

### Test Redis:
```bash
redis-cli -a your_password ping
# Expected: PONG
```

### Test Queue:
```bash
# Dispatch test job
php artisan tinker
>>> dispatch(function() { logger('Queue works!'); });

# Check logs
tail -f storage/logs/laravel.log
```

### Test Scheduler:
```bash
php artisan schedule:run
php artisan schedule:list
```

### Test Queue Worker:
```bash
sudo supervisorctl status
# Expected: levl-queue-worker:levl-queue-worker_00 RUNNING
```

---

## 📊 Monitoring

### Check Queue Status:
```bash
php artisan queue:monitor redis:emails-critical,redis:grading
php artisan queue:failed
```

### Check Supervisor:
```bash
sudo supervisorctl status
sudo supervisorctl tail levl-queue-worker:levl-queue-worker_00
```

### Check Redis:
```bash
redis-cli -a password
> INFO memory
> CLIENT LIST
```

### Check Logs:
```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/queue-worker.log
```

---

## ⚠️ Common Issues

### Queue Not Processing:
```bash
# Restart workers
sudo supervisorctl restart levl-queue-worker:*

# Check logs
tail -f storage/logs/queue-worker.log
```

### Redis Connection Failed:
```bash
# Check if running
sudo systemctl status redis-server

# Test connection
redis-cli -a password ping
```

### Scheduler Not Running:
```bash
# Check crontab
sudo crontab -l -u www-data

# Test manually
php artisan schedule:run
```

---

## 🎯 Priority Order

1. **Redis** (CRITICAL) - Tanpa ini, cache & queue tidak jalan
2. **Queue Worker** (CRITICAL) - Tanpa ini, background jobs tidak jalan
3. **Cron Scheduler** (CRITICAL) - Tanpa ini, scheduled tasks tidak jalan
4. **Materialized View** (RECOMMENDED) - Untuk leaderboard performance
5. **Mail Service** (OPTIONAL) - Untuk production email
6. **Meilisearch** (OPTIONAL) - Jika pakai search feature

---

## 📝 Environment Variables

Update `.env` setelah setup:

```env
# Production Mode
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Redis (WAJIB!)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_PASSWORD=your_secure_password

# Session (optional, bisa tetap database)
SESSION_DRIVER=database  # atau redis

# Mail (ganti dengan production)
MAIL_MAILER=mailgun
MAIL_FROM_ADDRESS="noreply@your-domain.com"

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
```

---

## ✅ Final Checklist

Sebelum go-live, pastikan:

- [ ] Redis running dan password set
- [ ] Queue workers running (check `supervisorctl status`)
- [ ] Cron scheduler configured (check `crontab -l -u www-data`)
- [ ] Materialized view refresh scheduled
- [ ] `.env` updated dengan production values
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] SSL/HTTPS enabled
- [ ] Firewall configured
- [ ] Database backup scheduled
- [ ] Monitoring setup (optional)

---

**🎉 Setelah semua checklist selesai, aplikasi siap production!**
