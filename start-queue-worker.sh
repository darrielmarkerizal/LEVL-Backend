#!/bin/bash

# LEVL Queue Worker Starter Script
# Jalankan semua queue workers dengan 1 perintah

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  LEVL Queue Worker Starter${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Cek apakah Redis berjalan
echo -e "${YELLOW}Checking Redis connection...${NC}"
if redis-cli ping > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Redis is running${NC}"
else
    echo -e "${RED}✗ Redis is not running!${NC}"
    echo -e "${RED}  Please start Redis first: brew services start redis${NC}"
    exit 1
fi

# Cek apakah ada worker yang sudah berjalan
EXISTING_WORKERS=$(ps aux | grep "queue:work" | grep -v grep | wc -l)
if [ $EXISTING_WORKERS -gt 0 ]; then
    echo -e "${YELLOW}⚠ Found $EXISTING_WORKERS existing queue worker(s)${NC}"
    echo -e "${YELLOW}  Do you want to stop them first? (y/n)${NC}"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        echo -e "${YELLOW}Stopping existing workers...${NC}"
        pkill -f "queue:work"
        sleep 2
        echo -e "${GREEN}✓ Existing workers stopped${NC}"
    fi
fi

# Dapatkan path absolut ke direktori script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Jalankan queue worker di background
echo ""
echo -e "${YELLOW}Starting queue worker...${NC}"
echo -e "${YELLOW}Queue priority: emails-critical → emails-transactional → grading → notifications → file-processing → trash → logging → audit → default${NC}"
echo ""

nohup php "$SCRIPT_DIR/artisan" queue:work redis \
    --queue=emails-critical,emails-transactional,grading,notifications,file-processing,trash,logging,audit,default \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --timeout=900 \
    > "$SCRIPT_DIR/storage/logs/queue-worker.log" 2>&1 &

WORKER_PID=$!

# Tunggu sebentar untuk memastikan worker start
sleep 2

# Cek apakah worker berhasil start
if ps -p $WORKER_PID > /dev/null; then
    echo -e "${GREEN}✓ Queue worker started successfully!${NC}"
    echo -e "${GREEN}  PID: $WORKER_PID${NC}"
    echo ""
    echo -e "${YELLOW}Useful commands:${NC}"
    echo -e "  View logs:        tail -f $SCRIPT_DIR/storage/logs/queue-worker.log"
    echo -e "  Stop worker:      kill $WORKER_PID"
    echo -e "  Stop all workers: pkill -f 'queue:work'"
    echo -e "  Check status:     ps aux | grep queue:work"
    echo ""
    echo -e "${GREEN}Queue worker is now running in background!${NC}"
else
    echo -e "${RED}✗ Failed to start queue worker${NC}"
    echo -e "${RED}  Check logs: tail -f $SCRIPT_DIR/storage/logs/queue-worker.log${NC}"
    exit 1
fi
