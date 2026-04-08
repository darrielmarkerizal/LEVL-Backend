#!/bin/bash

# ============================================
# LEVL Backend - VPS Quick Setup Script
# ============================================
# This script automates the setup of required services
# Run with: sudo bash VPS_QUICK_SETUP.sh

set -e

echo "🚀 Starting Levl Backend VPS Setup..."
echo "======================================"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables (EDIT THESE!)
APP_PATH="/var/www/levl-backend"
APP_USER="www-data"
REDIS_PASSWORD="your_secure_redis_password_here"
DOMAIN="your-domain.com"

# ============================================
# 1. Install Redis
# ============================================
echo ""
echo "${GREEN}[1/5] Installing Redis...${NC}"
apt update
apt install redis-server -y

# Configure Redis
echo "requirepass ${REDIS_PASSWORD}" >> /etc/redis/redis.conf
echo "maxmemory 512mb" >> /etc/redis/redis.conf
echo "maxmemory-policy allkeys-lru" >> /etc/redis/redis.conf

# Start Redis
systemctl start redis-server
systemctl enable redis-server

# Test Redis
if redis-cli -a ${REDIS_PASSWORD} ping | grep -q "PONG"; then
    echo "${GREEN}✓ Redis installed and running${NC}"
else
    echo "${RED}✗ Redis installation failed${NC}"
    exit 1
fi

# ============================================
# 2. Install Supervisor
# ============================================
echo ""
echo "${GREEN}[2/5] Installing Supervisor...${NC}"
apt install supervisor -y

# Create Queue Worker Config
cat > /etc/supervisor/conf.d/levl-queue-worker.conf <<EOF
[program:levl-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_PATH}/artisan queue:work redis --queue=emails-critical,emails-transactional,grading,notifications,file-processing,trash,logging,audit,default --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_PATH}/storage/logs/queue-worker.log
stopwaitsecs=3600
EOF

# Start Supervisor
supervisorctl reread
supervisorctl update
supervisorctl start levl-queue-worker:*

echo "${GREEN}✓ Supervisor installed and queue workers started${NC}"

# ============================================
# 3. Setup Cron Scheduler
# ============================================
echo ""
echo "${GREEN}[3/5] Setting up Cron Scheduler...${NC}"

# Add Laravel Scheduler to crontab
(crontab -u ${APP_USER} -l 2>/dev/null; echo "* * * * * cd ${APP_PATH} && php artisan schedule:run >> /dev/null 2>&1") | crontab -u ${APP_USER} -

# Add Materialized View Refresh (every 5 minutes)
(crontab -u ${APP_USER} -l 2>/dev/null; echo "*/5 * * * * cd ${APP_PATH} && php artisan leaderboard:refresh --concurrent >> /dev/null 2>&1") | crontab -u ${APP_USER} -

echo "${GREEN}✓ Cron scheduler configured${NC}"

# ============================================
# 4. Update .env File
# ============================================
echo ""
echo "${GREEN}[4/5] Updating .env configuration...${NC}"

cd ${APP_PATH}

# Backup original .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update Redis configuration
sed -i "s/^REDIS_PASSWORD=.*/REDIS_PASSWORD=${REDIS_PASSWORD}/" .env
sed -i "s/^CACHE_STORE=.*/CACHE_STORE=redis/" .env
sed -i "s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/" .env

# Update production settings
sed -i "s/^APP_ENV=.*/APP_ENV=production/" .env
sed -i "s/^APP_DEBUG=.*/APP_DEBUG=false/" .env

echo "${GREEN}✓ .env file updated${NC}"

# ============================================
# 5. Run Laravel Setup Commands
# ============================================
echo ""
echo "${GREEN}[5/5] Running Laravel setup commands...${NC}"

cd ${APP_PATH}

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Create required tables
php artisan queue:table
php artisan cache:table
php artisan session:table

# Run migrations
php artisan migrate --force

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R ${APP_USER}:${APP_USER} ${APP_PATH}
chmod -R 755 ${APP_PATH}
chmod -R 775 ${APP_PATH}/storage
chmod -R 775 ${APP_PATH}/bootstrap/cache

echo "${GREEN}✓ Laravel setup completed${NC}"

# ============================================
# 6. Install OPcache (Optional but Recommended)
# ============================================
echo ""
echo "${YELLOW}[OPTIONAL] Installing OPcache...${NC}"
apt install php8.3-opcache -y

cat > /etc/php/8.3/fpm/conf.d/10-opcache.ini <<EOF
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
EOF

systemctl restart php8.3-fpm

echo "${GREEN}✓ OPcache installed${NC}"

# ============================================
# Summary
# ============================================
echo ""
echo "======================================"
echo "${GREEN}🎉 Setup Complete!${NC}"
echo "======================================"
echo ""
echo "Services Status:"
echo "  ✓ Redis: $(systemctl is-active redis-server)"
echo "  ✓ Supervisor: $(systemctl is-active supervisor)"
echo "  ✓ Queue Workers: $(supervisorctl status levl-queue-worker:* | wc -l) workers running"
echo ""
echo "Next Steps:"
echo "  1. Update .env with your actual values:"
echo "     - APP_URL=${DOMAIN}"
echo "     - MAIL_* settings"
echo "     - DO_* settings (DigitalOcean Spaces)"
echo ""
echo "  2. Test Queue:"
echo "     php artisan queue:monitor redis:emails-critical,redis:grading"
echo ""
echo "  3. Test Scheduler:"
echo "     php artisan schedule:list"
echo ""
echo "  4. Monitor Logs:"
echo "     tail -f ${APP_PATH}/storage/logs/laravel.log"
echo "     tail -f ${APP_PATH}/storage/logs/queue-worker.log"
echo ""
echo "  5. Check Supervisor:"
echo "     sudo supervisorctl status"
echo ""
echo "======================================"
echo "${YELLOW}⚠️  IMPORTANT: Update REDIS_PASSWORD in this script before running!${NC}"
echo "======================================"
