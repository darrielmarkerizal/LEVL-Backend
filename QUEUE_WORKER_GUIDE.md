# Queue Worker Guide

## Masalah yang Ditemukan

Queue worker tidak berjalan secara otomatis, sehingga job yang di-dispatch (seperti `DeleteCourseJob`) tidak diproses dan tertunda di Redis.

## Diagnosis

### 1. Cek Queue Worker yang Berjalan
```bash
ps aux | grep "queue:work"
```

### 2. Cek Job yang Tertunda di Redis
```bash
# Lihat semua queue
redis-cli KEYS "*queue*"

# Cek jumlah job di queue tertentu
redis-cli LLEN "levl-backend-database-queues:schemes"

# Lihat isi job
redis-cli LRANGE "levl-backend-database-queues:schemes" 0 -1
```

### 3. Cek Failed Jobs
```bash
php artisan queue:failed
```

## Solusi

### Menjalankan Queue Worker Secara Manual

```bash
php artisan queue:work redis --queue=schemes,default,mail,logging,audit --tries=3 --timeout=900
```

### Menjalankan Queue Worker di Background (Development)

```bash
# Menggunakan nohup
nohup php artisan queue:work redis --queue=schemes,default,mail,logging,audit --tries=3 --timeout=900 > storage/logs/queue-worker.log 2>&1 &

# Atau menggunakan screen
screen -dmS queue-worker php artisan queue:work redis --queue=schemes,default,mail,logging,audit --tries=3 --timeout=900
```

### Menjalankan Queue Worker di Production

Gunakan **Supervisor** untuk memastikan queue worker selalu berjalan dan restart otomatis jika crash.

#### Install Supervisor (Ubuntu/Debian)
```bash
sudo apt-get install supervisor
```

#### Konfigurasi Supervisor

Buat file `/etc/supervisor/conf.d/levl-queue-worker.conf`:

```ini
[program:levl-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/levl-backend/artisan queue:work redis --queue=schemes,default,mail,logging,audit --tries=3 --timeout=900 --sleep=3 --max-jobs=1000
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/levl-backend/storage/logs/queue-worker.log
stopwaitsecs=3600
```

#### Reload Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start levl-queue-worker:*
```

#### Monitoring Supervisor
```bash
# Status semua worker
sudo supervisorctl status

# Restart worker
sudo supervisorctl restart levl-queue-worker:*

# Stop worker
sudo supervisorctl stop levl-queue-worker:*
```

## Queue yang Digunakan

Aplikasi menggunakan beberapa queue:

1. **schemes** - Job terkait course/scheme (create, update, delete)
2. **default** - Job umum
3. **mail** - Job pengiriman email
4. **logging** - Job logging aktivitas
5. **audit** - Job audit trail

## Job yang Menggunakan Queue

### DeleteCourseJob
- **Queue**: schemes
- **Timeout**: 900 detik (15 menit)
- **Tries**: 3
- **Fungsi**: Menghapus course secara soft delete dan mencatat aktivitas

### BulkRestoreTrashBinsJob
- **Queue**: schemes
- **Timeout**: 1800 detik (30 menit)
- **Tries**: 3
- **Fungsi**: Restore multiple items dari trash

## Monitoring Queue

### Melihat Job yang Sedang Diproses
```bash
php artisan queue:monitor redis:schemes,redis:default,redis:mail
```

### Melihat Statistik Queue (dengan Horizon)
Jika menggunakan Laravel Horizon:
```bash
php artisan horizon
```
Akses dashboard di: `http://your-app.test/horizon`

## Troubleshooting

### Job Tidak Diproses
1. Pastikan queue worker berjalan
2. Cek Redis connection: `redis-cli ping`
3. Cek log: `tail -f storage/logs/laravel.log`

### Job Gagal Terus
1. Cek failed jobs: `php artisan queue:failed`
2. Lihat detail error: `php artisan queue:failed-table`
3. Retry failed job: `php artisan queue:retry {id}`
4. Retry semua: `php artisan queue:retry all`

### Queue Worker Crash
1. Cek memory limit di `php.ini`
2. Gunakan `--max-jobs=1000` untuk restart worker setelah 1000 jobs
3. Gunakan `--max-time=3600` untuk restart worker setelah 1 jam
4. Pastikan supervisor configured untuk auto-restart

## Best Practices

1. **Selalu gunakan Supervisor di production** untuk memastikan worker selalu berjalan
2. **Monitor queue metrics** menggunakan Horizon atau monitoring tools
3. **Set timeout yang sesuai** untuk setiap job berdasarkan kompleksitas
4. **Implement retry logic** dengan backoff strategy
5. **Log semua error** untuk debugging
6. **Gunakan multiple workers** untuk queue yang sibuk
7. **Restart worker secara berkala** untuk mencegah memory leak

## Alternatif: Sync Queue (Development Only)

Untuk development, bisa menggunakan sync queue agar job langsung diproses tanpa worker:

```env
QUEUE_CONNECTION=sync
```

⚠️ **Jangan gunakan sync di production** karena akan memblokir request HTTP!
