#!/bin/bash

echo "🚀 COMPREHENSIVE TESTING SUITE"
echo "=============================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test results
UNIT_PASSED=false
INTEGRATION_PASSED=false
STRESS_PASSED=false

echo "📋 Starting comprehensive test suite..."
echo ""

# Check if PHP server is running
echo "🔍 Checking if PHP server is running..."
if curl -s http://localhost:8000 > /dev/null; then
    echo -e "${GREEN}✅ PHP server is running on localhost:8000${NC}"
else
    echo -e "${YELLOW}⚠️  Starting PHP server...${NC}"
    php -S localhost:8000 > /dev/null 2>&1 &
    SERVER_PID=$!
    sleep 3
    
    if curl -s http://localhost:8000 > /dev/null; then
        echo -e "${GREEN}✅ PHP server started successfully${NC}"
    else
        echo -e "${RED}❌ Failed to start PHP server${NC}"
        exit 1
    fi
fi

echo ""

# Run Unit Tests
echo "1️⃣ RUNNING UNIT TESTS"
echo "===================="
if php unit-tests.php; then
    echo -e "${GREEN}✅ Unit tests passed${NC}"
    UNIT_PASSED=true
else
    echo -e "${RED}❌ Unit tests failed${NC}"
    UNIT_PASSED=false
fi

echo ""

# Run Integration Tests
echo "2️⃣ RUNNING INTEGRATION TESTS"
echo "============================"
if php integration-tests.php; then
    echo -e "${GREEN}✅ Integration tests passed${NC}"
    INTEGRATION_PASSED=true
else
    echo -e "${RED}❌ Integration tests failed${NC}"
    INTEGRATION_PASSED=false
fi

echo ""

# Run Stress Tests
echo "3️⃣ RUNNING STRESS TESTS"
echo "======================="
if php stress-tests.php; then
    echo -e "${GREEN}✅ Stress tests passed${NC}"
    STRESS_PASSED=true
else
    echo -e "${RED}❌ Stress tests failed${NC}"
    STRESS_PASSED=false
fi

echo ""

# Final Results
echo "📊 FINAL TEST RESULTS"
echo "===================="
echo "Unit Tests:        $([ "$UNIT_PASSED" = true ] && echo -e "${GREEN}PASSED${NC}" || echo -e "${RED}FAILED${NC}")"
echo "Integration Tests: $([ "$INTEGRATION_PASSED" = true ] && echo -e "${GREEN}PASSED${NC}" || echo -e "${RED}FAILED${NC}")"
echo "Stress Tests:      $([ "$STRESS_PASSED" = true ] && echo -e "${GREEN}PASSED${NC}" || echo -e "${RED}FAILED${NC}")"

echo ""

# Overall result
if [ "$UNIT_PASSED" = true ] && [ "$INTEGRATION_PASSED" = true ] && [ "$STRESS_PASSED" = true ]; then
    echo -e "${GREEN}🎉 ALL TESTS PASSED - READY FOR DEPLOYMENT!${NC}"
    echo ""
    echo "🚀 Pushing to production..."
    cd ..
    git add tests/
    git commit -m "Add comprehensive test suite

✅ Unit Tests - Database, auth, calculations
✅ Integration Tests - Page loading, URLs, APIs  
✅ Stress Tests - Concurrent requests, performance
✅ All tests passing - Ready for deployment"
    git push origin main
    echo -e "${GREEN}✅ Code pushed to production!${NC}"
    exit 0
else
    echo -e "${RED}❌ TESTS FAILED - DO NOT DEPLOY${NC}"
    echo ""
    echo "Fix the failing tests before deployment."
    exit 1
fi

# Clean up server if we started it
if [ ! -z "$SERVER_PID" ]; then
    kill $SERVER_PID 2>/dev/null
fi