#!/bin/bash

# LEVL Queue Worker Stopper Script

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  LEVL Queue Worker Stopper${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Cek apakah ada worker yang berjalan
EXISTING_WORKERS=$(ps aux | grep "queue:work" | grep -v grep | wc -l)

if [ $EXISTING_WORKERS -eq 0 ]; then
    echo -e "${YELLOW}No queue workers are currently running${NC}"
    exit 0
fi

echo -e "${YELLOW}Found $EXISTING_WORKERS queue worker(s) running${NC}"
echo ""

# Tampilkan worker yang berjalan
echo -e "${YELLOW}Running workers:${NC}"
ps aux | grep "queue:work" | grep -v grep | awk '{print "  PID: " $2 " | User: " $1}'
echo ""

# Konfirmasi
echo -e "${RED}Do you want to stop all queue workers? (y/n)${NC}"
read -r response

if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo -e "${YELLOW}Stopping all queue workers...${NC}"
    pkill -f "queue:work"
    sleep 2
    
    # Cek apakah masih ada yang berjalan
    REMAINING=$(ps aux | grep "queue:work" | grep -v grep | wc -l)
    
    if [ $REMAINING -eq 0 ]; then
        echo -e "${GREEN}✓ All queue workers stopped successfully${NC}"
    else
        echo -e "${RED}⚠ Some workers are still running. Forcing stop...${NC}"
        pkill -9 -f "queue:work"
        sleep 1
        echo -e "${GREEN}✓ All queue workers force stopped${NC}"
    fi
else
    echo -e "${YELLOW}Operation cancelled${NC}"
fi
