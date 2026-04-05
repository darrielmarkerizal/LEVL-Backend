#!/bin/bash

# Storage Bucket Diagnostic Script
# Run this on your VPS to diagnose image storage issues

set -e

echo "=================================="
echo "Storage Bucket Diagnostic Tool"
echo "=================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Change to project directory
cd "$PROJECT_DIR"

echo -e "${BLUE}Project Directory:${NC} $PROJECT_DIR"
echo ""

# 1. Check if .env exists
echo -e "${BLUE}[1/7] Checking .env file...${NC}"
if [ ! -f .env ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ .env file exists${NC}"
echo ""

# 2. Extract and display storage configuration
echo -e "${BLUE}[2/7] Storage Configuration:${NC}"
echo "─────────────────────────────────"

# Function to safely get env value
get_env() {
    grep "^$1=" .env | cut -d '=' -f2- | tr -d '"' || echo ""
}

FILESYSTEM_DISK=$(get_env "FILESYSTEM_DISK")
DO_BUCKET=$(get_env "DO_BUCKET")
DO_REGION=$(get_env "DO_DEFAULT_REGION")
DO_ENDPOINT=$(get_env "DO_ENDPOINT")
DO_CDN_URL=$(get_env "DO_CDN_URL")
DO_ACCESS_KEY=$(get_env "DO_ACCESS_KEY_ID")

echo "FILESYSTEM_DISK: ${FILESYSTEM_DISK:-<not set>}"
echo "DO_BUCKET: ${DO_BUCKET:-<not set>}"
echo "DO_DEFAULT_REGION: ${DO_REGION:-<not set>}"
echo "DO_ENDPOINT: ${DO_ENDPOINT:-<not set>}"
echo "DO_CDN_URL: ${DO_CDN_URL:-<not set>}"
echo "DO_ACCESS_KEY_ID: ${DO_ACCESS_KEY:+<configured>}"
echo ""

# 3. Check database connection
echo -e "${BLUE}[3/7] Testing database connection...${NC}"
if php artisan db:show > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connection successful${NC}"
else
    echo -e "${RED}✗ Cannot connect to database${NC}"
    exit 1
fi
echo ""

# 4. Check media records in database
echo -e "${BLUE}[4/7] Checking media records in database...${NC}"
echo "─────────────────────────────────"

# Count total media
MEDIA_COUNT=$(php artisan tinker --execute="echo DB::table('media')->count();" 2>/dev/null || echo "0")
echo "Total media records: $MEDIA_COUNT"

# Check for bucket references
echo ""
echo "Checking for bucket references..."
LEVL_ASSETS_COUNT=$(php artisan tinker --execute="echo DB::table('media')->where(DB::raw('custom_properties::text'), 'like', '%levl-assets%')->count();" 2>/dev/null || echo "0")
PREP_LSP_COUNT=$(php artisan tinker --execute="echo DB::table('media')->where(DB::raw('custom_properties::text'), 'like', '%prep-lsp%')->count();" 2>/dev/null || echo "0")

echo "  - levl-assets references: $LEVL_ASSETS_COUNT"
echo "  - prep-lsp references: $PREP_LSP_COUNT"
echo ""

# 5. Test bucket connectivity with curl
echo -e "${BLUE}[5/7] Testing bucket connectivity...${NC}"
echo "─────────────────────────────────"

# Test current bucket
if [ -n "$DO_CDN_URL" ]; then
    echo "Testing CDN URL: $DO_CDN_URL"
    if curl -s -I "$DO_CDN_URL" | head -n 1 | grep -q "HTTP"; then
        STATUS=$(curl -s -I "$DO_CDN_URL" | head -n 1)
        echo -e "${GREEN}✓ CDN URL is reachable${NC}"
        echo "  Status: $STATUS"
    else
        echo -e "${RED}✗ Cannot reach CDN URL${NC}"
    fi
else
    echo -e "${YELLOW}⚠ DO_CDN_URL not configured${NC}"
fi
echo ""

# Test alternative buckets
echo "Testing alternative buckets..."
for BUCKET in "levl-assets" "prep-lsp"; do
    TEST_URL="https://${BUCKET}.sgp1.cdn.digitaloceanspaces.com"
    echo -n "  - $BUCKET: "
    
    if curl -s -I "$TEST_URL" --max-time 5 | head -n 1 | grep -q "HTTP"; then
        STATUS=$(curl -s -I "$TEST_URL" --max-time 5 | head -n 1 | awk '{print $2}')
        if [ "$STATUS" = "200" ] || [ "$STATUS" = "403" ]; then
            echo -e "${GREEN}✓ Accessible (Status: $STATUS)${NC}"
        else
            echo -e "${YELLOW}⚠ Status: $STATUS${NC}"
        fi
    else
        echo -e "${RED}✗ Not accessible${NC}"
    fi
done
echo ""

# 6. Run Laravel diagnostic command
echo -e "${BLUE}[6/7] Running Laravel storage diagnostics...${NC}"
echo "─────────────────────────────────"
php artisan storage:diagnose
echo ""

# 7. Recommendations
echo -e "${BLUE}[7/7] Recommendations:${NC}"
echo "─────────────────────────────────"

if [ "$LEVL_ASSETS_COUNT" -gt "0" ] && [ "$DO_BUCKET" != "levl-assets" ]; then
    echo -e "${YELLOW}⚠ MISMATCH DETECTED:${NC}"
    echo "  Database has $LEVL_ASSETS_COUNT references to 'levl-assets'"
    echo "  But .env is configured to use '$DO_BUCKET'"
    echo ""
    echo -e "${GREEN}RECOMMENDED ACTION:${NC}"
    echo "  Update .env to use the correct bucket:"
    echo "  DO_BUCKET=levl-assets"
    echo "  DO_CDN_URL=https://levl-assets.sgp1.cdn.digitaloceanspaces.com"
    echo ""
    echo "  Then restart the application:"
    echo "  php artisan octane:reload"
    echo "  # or"
    echo "  sudo systemctl restart levl-backend"
elif [ "$PREP_LSP_COUNT" -gt "0" ] && [ "$DO_BUCKET" != "prep-lsp" ]; then
    echo -e "${YELLOW}⚠ MISMATCH DETECTED:${NC}"
    echo "  Database has $PREP_LSP_COUNT references to 'prep-lsp'"
    echo "  But .env is configured to use '$DO_BUCKET'"
    echo ""
    echo -e "${GREEN}RECOMMENDED ACTION:${NC}"
    echo "  Update .env to use the correct bucket:"
    echo "  DO_BUCKET=prep-lsp"
    echo "  DO_CDN_URL=https://prep-lsp.sgp1.cdn.digitaloceanspaces.com"
else
    echo -e "${GREEN}✓ Configuration looks correct${NC}"
    echo "  Database and .env are using the same bucket"
fi

echo ""
echo "=================================="
echo "Diagnostic Complete"
echo "=================================="
echo ""
echo "For detailed documentation, see:"
echo "  $PROJECT_DIR/PRODUCTION_IMAGE_FIX.md"
