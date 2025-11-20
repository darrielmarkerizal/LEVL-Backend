#!/bin/bash

# Quick test script untuk debugging DO Spaces upload
# Usage: ./quick-test.sh

BASE_URL="https://152-42-175-255.nip.io/api/v1/file-test"

echo "=========================================="
echo "DO Spaces Upload Quick Test"
echo "=========================================="
echo ""

# Test 1: Check configuration
echo "Test 1: Checking configuration..."
echo "----------------------------------------"
CONFIG=$(curl -s "${BASE_URL}/config")
echo "$CONFIG" | jq '.'
echo ""

# Check if credentials are configured
HAS_KEY=$(echo "$CONFIG" | jq -r '.disk_config.has_key')
HAS_SECRET=$(echo "$CONFIG" | jq -r '.disk_config.has_secret')

if [ "$HAS_KEY" != "true" ] || [ "$HAS_SECRET" != "true" ]; then
    echo "❌ ERROR: DO Spaces credentials not configured!"
    echo ""
    echo "Add to .env:"
    echo "DO_ACCESS_KEY_ID=your_key"
    echo "DO_SECRET_ACCESS_KEY=your_secret"
    echo ""
    exit 1
fi

echo "✅ Credentials configured"
echo ""

# Test 2: Test S3 operations
echo "Test 2: Testing S3 operations..."
echo "----------------------------------------"
S3_TEST=$(curl -s "${BASE_URL}/test-s3")
echo "$S3_TEST" | jq '.'
echo ""

SUCCESS=$(echo "$S3_TEST" | jq -r '.success')
ACCESSIBLE=$(echo "$S3_TEST" | jq -r '.tests.url_accessible.accessible')
HTTP_CODE=$(echo "$S3_TEST" | jq -r '.tests.url_accessible.http_code')

if [ "$SUCCESS" != "true" ]; then
    echo "❌ S3 operations test failed!"
    echo ""
    ERROR=$(echo "$S3_TEST" | jq -r '.error')
    echo "Error: $ERROR"
    echo ""
    echo "Check Laravel logs: tail -f storage/logs/laravel.log"
    exit 1
fi

if [ "$ACCESSIBLE" != "true" ]; then
    echo "⚠️  File uploaded but not accessible (HTTP $HTTP_CODE)"
    echo ""
    if [ "$HTTP_CODE" == "403" ]; then
        echo "This is an ACL issue. Run:"
        echo "  php artisan spaces:fix-acl"
        echo ""
    fi
    exit 1
fi

echo "✅ S3 operations working correctly"
echo ""

# Test 3: Upload test file
echo "Test 3: Uploading test file..."
echo "----------------------------------------"

# Create test file
TEST_FILE="/tmp/test-upload-$(date +%s).txt"
echo "Test upload at $(date)" > "$TEST_FILE"

UPLOAD=$(curl -s -X POST "${BASE_URL}/upload" \
    -F "file=@${TEST_FILE}" \
    -F "directory=test/quick-test")

echo "$UPLOAD" | jq '.'
echo ""

UPLOAD_SUCCESS=$(echo "$UPLOAD" | jq -r '.success')
if [ "$UPLOAD_SUCCESS" != "true" ]; then
    echo "❌ Upload failed!"
    rm -f "$TEST_FILE"
    exit 1
fi

FILE_PATH=$(echo "$UPLOAD" | jq -r '.data.path')
FILE_URL=$(echo "$UPLOAD" | jq -r '.data.url')

echo "✅ Upload successful"
echo "   Path: $FILE_PATH"
echo "   URL: $FILE_URL"
echo ""

# Clean up local test file
rm -f "$TEST_FILE"

# Test 4: Check file accessibility
echo "Test 4: Checking file accessibility..."
echo "----------------------------------------"
HTTP_CHECK=$(curl -s -I "$FILE_URL" | head -n 1)
echo "$HTTP_CHECK"
echo ""

if echo "$HTTP_CHECK" | grep -q "200"; then
    echo "✅ File is publicly accessible!"
else
    echo "❌ File is NOT accessible"
    echo ""
    echo "Run ACL fix:"
    echo "  php artisan spaces:fix-acl test/quick-test"
fi
echo ""

# Test 5: Clean up
echo "Test 5: Cleaning up test file..."
echo "----------------------------------------"
DELETE=$(curl -s -X DELETE "${BASE_URL}/delete" \
    -H "Content-Type: application/json" \
    -d "{\"path\": \"$FILE_PATH\"}")

echo "$DELETE" | jq '.'
echo ""

DELETE_SUCCESS=$(echo "$DELETE" | jq -r '.data.deleted')
if [ "$DELETE_SUCCESS" == "true" ]; then
    echo "✅ Test file deleted"
else
    echo "⚠️  Could not delete test file"
fi
echo ""

echo "=========================================="
echo "Test completed!"
echo "=========================================="
echo ""
echo "Summary:"
echo "  ✅ Configuration: OK"
echo "  ✅ S3 Operations: OK"
echo "  ✅ Upload: OK"
echo "  ✅ File Accessibility: Check above"
echo ""
echo "If all tests passed, the upload system is working correctly."
echo "If file accessibility failed, run:"
echo "  php artisan spaces:fix-acl"
echo ""
