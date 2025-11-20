#!/bin/bash

# Debug DO Spaces upload issue
# Run this after deploying the updated code

BASE_URL="https://152-42-175-255.nip.io/api/v1/file-test"

echo "=========================================="
echo "DO Spaces Upload Debug"
echo "=========================================="
echo ""

# Step 1: Check configuration
echo "Step 1: Checking configuration..."
echo "=========================================="
curl -s "${BASE_URL}/config" | jq '.'
echo ""

# Step 2: Test Laravel Storage (put operation returns false)
echo "Step 2: Testing Laravel Storage..."
echo "=========================================="
curl -s "${BASE_URL}/test-s3" | jq '.'
echo ""

# Step 3: Test AWS SDK directly (bypass Laravel)
echo "Step 3: Testing AWS SDK directly..."
echo "=========================================="
curl -s "${BASE_URL}/test-aws-sdk" | jq '.'
echo ""

echo "=========================================="
echo "Debug complete!"
echo "=========================================="
echo ""
echo "Analysis:"
echo "--------"
echo "If Step 1 shows has_key=false or has_secret=false:"
echo "  → Check .env for DO_ACCESS_KEY_ID and DO_SECRET_ACCESS_KEY"
echo ""
echo "If Step 2 put_with_options=false:"
echo "  → Laravel Storage failing (config issue)"
echo ""
echo "If Step 3 put_error is not null:"
echo "  → AWS SDK error (check credentials or permissions)"
echo ""
echo "If Step 3 put_object.success=true but url_accessible.accessible=false:"
echo "  → File uploaded but ACL issue (403) or wrong URL (404)"
echo ""
