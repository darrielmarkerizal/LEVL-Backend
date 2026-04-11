#!/bin/bash

# Blackbox Testing Comparison Script
# This script runs blackbox tests and compares results before/after fixes

set -e

echo "🚀 Blackbox Testing Comparison Script"
echo "======================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Generate new test cases
echo "📝 Step 1: Generating new test cases..."
php artisan blackbox:generate --output=blackbox_tests_v2.json

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Test cases generated successfully${NC}"
else
    echo -e "${RED}❌ Failed to generate test cases${NC}"
    exit 1
fi

echo ""

# Step 2: Run blackbox tests
echo "🧪 Step 2: Running blackbox tests..."
echo "This may take a few minutes..."
echo ""

php artisan blackbox:run blackbox_tests_v2.json

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Tests completed successfully${NC}"
else
    echo -e "${YELLOW}⚠️  Tests completed with failures${NC}"
fi

echo ""

# Step 3: Find the latest result file
echo "📊 Step 3: Analyzing results..."
LATEST_RESULT=$(ls -t storage/app/blackbox-results/blackbox-run-*.json | head -1)

if [ -z "$LATEST_RESULT" ]; then
    echo -e "${RED}❌ No result file found${NC}"
    exit 1
fi

echo "Latest result: $LATEST_RESULT"
echo ""

# Step 4: Extract and display summary
echo "📈 Test Results Summary:"
echo "======================="

# Extract summary using jq
PASS=$(jq -r '.summary.pass' "$LATEST_RESULT")
FAIL=$(jq -r '.summary.fail' "$LATEST_RESULT")
SKIP=$(jq -r '.summary.skip' "$LATEST_RESULT")
TOTAL=$(jq -r '.summary.total' "$LATEST_RESULT")
DURATION=$(jq -r '.duration_seconds' "$LATEST_RESULT")

# Calculate percentages
PASS_PCT=$(awk "BEGIN {printf \"%.1f\", ($PASS/$TOTAL)*100}")
FAIL_PCT=$(awk "BEGIN {printf \"%.1f\", ($FAIL/$TOTAL)*100}")

echo ""
echo -e "${GREEN}✅ PASS:${NC}  $PASS / $TOTAL ($PASS_PCT%)"
echo -e "${RED}❌ FAIL:${NC}  $FAIL / $TOTAL ($FAIL_PCT%)"
echo -e "${YELLOW}⏭️  SKIP:${NC}  $SKIP / $TOTAL"
echo "⏱️  Duration: ${DURATION}s"
echo ""

# Step 5: Compare with old results
echo "📊 Comparison with Previous Run:"
echo "================================"

OLD_RESULT="blackbox-run-20260411_144340-804.json"

if [ -f "$OLD_RESULT" ]; then
    OLD_PASS=$(jq -r '.summary.pass' "$OLD_RESULT")
    OLD_FAIL=$(jq -r '.summary.fail' "$OLD_RESULT")
    OLD_TOTAL=$(jq -r '.summary.total' "$OLD_RESULT")
    
    OLD_PASS_PCT=$(awk "BEGIN {printf \"%.1f\", ($OLD_PASS/$OLD_TOTAL)*100}")
    OLD_FAIL_PCT=$(awk "BEGIN {printf \"%.1f\", ($OLD_FAIL/$OLD_TOTAL)*100}")
    
    PASS_DIFF=$((PASS - OLD_PASS))
    FAIL_DIFF=$((FAIL - OLD_FAIL))
    PCT_DIFF=$(awk "BEGIN {printf \"%.1f\", $PASS_PCT - $OLD_PASS_PCT}")
    
    echo "Old Results:"
    echo "  ✅ PASS: $OLD_PASS / $OLD_TOTAL ($OLD_PASS_PCT%)"
    echo "  ❌ FAIL: $OLD_FAIL / $OLD_TOTAL ($OLD_FAIL_PCT%)"
    echo ""
    echo "Improvement:"
    
    if [ $PASS_DIFF -gt 0 ]; then
        echo -e "  ${GREEN}✅ +$PASS_DIFF more tests passing${NC}"
    elif [ $PASS_DIFF -lt 0 ]; then
        echo -e "  ${RED}❌ $PASS_DIFF fewer tests passing${NC}"
    else
        echo "  ➡️  No change in passing tests"
    fi
    
    if [ $FAIL_DIFF -lt 0 ]; then
        echo -e "  ${GREEN}✅ $FAIL_DIFF fewer tests failing${NC}"
    elif [ $FAIL_DIFF -gt 0 ]; then
        echo -e "  ${RED}❌ +$FAIL_DIFF more tests failing${NC}"
    else
        echo "  ➡️  No change in failing tests"
    fi
    
    echo ""
    echo -e "  Pass Rate Change: ${PCT_DIFF}%"
else
    echo -e "${YELLOW}⚠️  Old result file not found for comparison${NC}"
fi

echo ""

# Step 6: Show top failures
echo "🔍 Top 10 Failure Categories:"
echo "============================"
jq -r '.failures[] | .api' "$LATEST_RESULT" | sort | uniq -c | sort -rn | head -10

echo ""

# Step 7: Generate detailed report
echo "📄 Generating detailed report..."
REPORT_FILE="blackbox-comparison-report-$(date +%Y%m%d_%H%M%S).txt"

cat > "$REPORT_FILE" << EOF
Blackbox Testing Comparison Report
Generated: $(date)
=====================================

CURRENT RUN:
-----------
Pass:     $PASS / $TOTAL ($PASS_PCT%)
Fail:     $FAIL / $TOTAL ($FAIL_PCT%)
Skip:     $SKIP / $TOTAL
Duration: ${DURATION}s
File:     $LATEST_RESULT

EOF

if [ -f "$OLD_RESULT" ]; then
    cat >> "$REPORT_FILE" << EOF
PREVIOUS RUN:
------------
Pass:     $OLD_PASS / $OLD_TOTAL ($OLD_PASS_PCT%)
Fail:     $OLD_FAIL / $OLD_TOTAL ($OLD_FAIL_PCT%)
File:     $OLD_RESULT

IMPROVEMENT:
-----------
Pass Diff:  $PASS_DIFF tests
Fail Diff:  $FAIL_DIFF tests
Rate Diff:  ${PCT_DIFF}%

EOF
fi

cat >> "$REPORT_FILE" << EOF
TOP FAILURE CATEGORIES:
----------------------
EOF

jq -r '.failures[] | .api' "$LATEST_RESULT" | sort | uniq -c | sort -rn | head -20 >> "$REPORT_FILE"

echo -e "${GREEN}✅ Report saved to: $REPORT_FILE${NC}"
echo ""

# Step 8: Summary
echo "🎯 Summary:"
echo "=========="

if [ $PASS_DIFF -gt 0 ]; then
    echo -e "${GREEN}✅ Fixes are working! $PASS_DIFF more tests passing.${NC}"
elif [ $PASS_DIFF -eq 0 ]; then
    echo -e "${YELLOW}⚠️  No improvement detected. Further investigation needed.${NC}"
else
    echo -e "${RED}❌ Regression detected! $PASS_DIFF fewer tests passing.${NC}"
fi

echo ""
echo "📁 Files generated:"
echo "  - Test cases: blackbox_tests_v2.json"
echo "  - Results: $LATEST_RESULT"
echo "  - Report: $REPORT_FILE"
echo ""
echo "✅ Done!"
