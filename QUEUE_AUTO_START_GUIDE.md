# Queue Auto Start Guide

Panduan untuk menjalankan queue worker secara otomatis dengan 1 perintah.

## Quick Start (Development)

### 1. Berikan Permission ke Script

```bash
cd Levl-BE
chmod +x start-queue-worker.sh
chmod +x stop-queue-worker.sh
```

### 2. Jalankan Queue Worker

```bash
./start-queue-worker.sh
```

Script akan:
- ✅ Cek koneksi Redis
- ✅ Cek worker yang sudah berjalan
- ✅ Start worker di background
- ✅ Tampilkan PID dan command untuk monitoring

### 3. Stop Queue Worker

```bash
./stop-queue-worker.sh
```

### 4. Monitor Queue Worker

```bash
# Lihat log real-time
tail -f storage/logs/queue-worker.log

# Cek status worker
ps aux | grep queue:work

# Lihat job di Redis
redis-cli KEYS "*queue*"
```

---

## Production Setup (Supervisor)

### 1. Install Supervisor

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install supervisor
```

**macOS:**
```bash
brew install supervisor
brew services start supervisor
```

### 2. Copy Konfigurasi

```bash
# Edit path di file supervisor-queue-worker.conf
# Ganti /path/to/Levl-BE dengan path absolut Anda

# Ubuntu/Debian
sudo cp supervisor-queue-worker.conf /etc/supervisor/conf.d/levl-queue-worker.conf

# macOS
cp supervisor-queue-worker.conf /usr/local/etc/supervisor.d/levl-queue-worker.ini
```

### 3. Update Path di Konfigurasi

Edit file konfigurasi dan ganti:
- `/path/to/Levl-BE` → Path absolut ke project Anda
- `www-data` → User yang menjalankan aplikasi (di macOS biasanya `_www` atau user Anda)

### 4. Reload Supervisor

**Ubuntu/Debian:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start levl-queue-worker:*
```

**macOS:**
```bash
brew services restart supervisor
supervisorctl reread
supervisorctl update
supervisorctl start levl-queue-worker:*
```

### 5. Monitoring Supervisor

```bash
# Status semua worker
sudo supervisorctl status

# Restart worker
sudo supervisorctl restart levl-queue-worker:*

# Stop worker
sudo supervisorctl stop levl-queue-worker:*

# Lihat log
sudo supervisorctl tail -f levl-queue-worker:levl-queue-worker_00 stdout
```

---

## Alternatif: systemd (Linux Only)

### 1. Buat Service File

```bash
sudo nano /etc/systemd/system/levl-queue-worker.service
```

### 2. Isi dengan:

```ini
[Unit]
Description=LEVL Queue Worker
After=network.target redis.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/Levl-BE
ExecStart=/usr/bin/php /path/to/Levl-BE/artisan queue:work redis --queue=emails-critical,emails-transactional,grading,notifications,file-processing,trash,logging,audit,default --sleep=3 --tries=3 --max-time=3600 --timeout=900
Restart=always
RestartSec=3
StandardOutput=append:/path/to/Levl-BE/storage/logs/queue-worker.log
StandardError=append:/path/to/Levl-BE/storage/logs/queue-worker-error.log

[Install]
WantedBy=multi-user.target
```

### 3. Enable dan Start Service

```bash
sudo systemctl daemon-reload
sudo systemctl enable levl-queue-worker
sudo systemctl start levl-queue-worker
```

### 4. Monitoring systemd

```bash
# Status
sudo systemctl status levl-queue-worker

# Restart
sudo systemctl restart levl-queue-worker

# Stop
sudo systemctl stop levl-queue-worker

# Lihat log
sudo journalctl -u levl-queue-worker -f
```

---

## Troubleshooting

### Worker Tidak Start

1. **Cek Redis:**
   ```bash
   redis-cli ping
   # Harus return: PONG
   ```

2. **Cek Permission:**
   ```bash
   ls -la start-queue-worker.sh
   # Harus ada 'x' (executable)
   ```

3. **Cek Log:**
   ```bash
   tail -f storage/logs/queue-worker.log
   ```

### Worker Crash Terus

1. **Cek Memory:**
   ```bash
   php -i | grep memory_limit
   # Minimal 256M
   ```

2. **Tambahkan Max Jobs:**
   Edit script, tambahkan `--max-jobs=1000`

3. **Cek Failed Jobs:**
   ```bash
   php artisan queue:failed
   ```

### Job Tidak Diproses

1. **Cek Worker Berjalan:**
   ```bash
   ps aux | grep queue:work
   ```

2. **Cek Queue di Redis:**
   ```bash
   redis-cli LLEN "queues:emails-critical"
   redis-cli LLEN "queues:default"
   ```

3. **Restart Worker:**
   ```bash
   ./stop-queue-worker.sh
   ./start-queue-worker.sh
   ```

---

## Best Practices

### Development
- ✅ Gunakan `start-queue-worker.sh`
- ✅ Monitor log dengan `tail -f`
- ✅ Restart worker setelah code changes

### Production
- ✅ Gunakan Supervisor atau systemd
- ✅ Set `numprocs=2` atau lebih untuk high traffic
- ✅ Monitor dengan tools seperti Horizon
- ✅ Setup alerting untuk failed jobs
- ✅ Backup failed jobs table

### Performance
- ✅ Gunakan `--max-time=3600` untuk restart berkala
- ✅ Gunakan `--max-jobs=1000` untuk mencegah memory leak
- ✅ Set `--sleep=3` untuk balance antara responsiveness dan CPU usage
- ✅ Pisahkan worker untuk queue yang sibuk

---

## Queue Priority

Worker memproses queue dengan prioritas:

1. **emails-critical** (Highest) - Auth emails
2. **emails-transactional** - Enrollment, notifications
3. **grading** - Grade calculations
4. **notifications** - In-app notifications
5. **file-processing** - File uploads
6. **trash** - Trash operations
7. **logging** - Activity logs
8. **audit** - Audit trails
9. **default** (Lowest) - General tasks

Worker akan selalu cek queue dengan prioritas lebih tinggi terlebih dahulu.

---

## Monitoring Commands

```bash
# Cek worker status
ps aux | grep queue:work

# Lihat log real-time
tail -f storage/logs/queue-worker.log

# Cek job di Redis
redis-cli KEYS "*queue*"

# Cek failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# Monitor queue (jika pakai Horizon)
php artisan horizon
```

---

## Auto Start on Boot

### macOS (launchd)

1. Buat file `~/Library/LaunchAgents/com.levl.queue-worker.plist`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.levl.queue-worker</string>
    <key>ProgramArguments</key>
    <array>
        <string>/path/to/Levl-BE/start-queue-worker.sh</string>
    </array>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <true/>
    <key>StandardOutPath</key>
    <string>/path/to/Levl-BE/storage/logs/queue-worker.log</string>
    <key>StandardErrorPath</key>
    <string>/path/to/Levl-BE/storage/logs/queue-worker-error.log</string>
</dict>
</plist>
```

2. Load:
```bash
launchctl load ~/Library/LaunchAgents/com.levl.queue-worker.plist
```

### Linux (systemd)

Sudah dijelaskan di section "Alternatif: systemd" di atas.

---

## Summary

**Development:**
```bash
./start-queue-worker.sh  # Start
./stop-queue-worker.sh   # Stop
```

**Production:**
```bash
sudo supervisorctl start levl-queue-worker:*   # Start
sudo supervisorctl stop levl-queue-worker:*    # Stop
sudo supervisorctl status                       # Status
```

**Monitoring:**
```bash
tail -f storage/logs/queue-worker.log  # Logs
ps aux | grep queue:work               # Status
php artisan queue:failed               # Failed jobs
```
