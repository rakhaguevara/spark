#!/bin/bash

# ========================================
# SPARK Docker Setup Verification Script
# ========================================

echo "🔍 SPARK Docker Setup Verification"
echo "===================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is running
echo "1. Checking Docker..."
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running!${NC}"
    echo "   Please start Docker Desktop first."
    exit 1
else
    echo -e "${GREEN}✅ Docker is running${NC}"
fi

# Check if containers are running
echo ""
echo "2. Checking containers..."
if docker ps | grep -q "spark-app"; then
    echo -e "${GREEN}✅ Web container (spark-app) is running${NC}"
else
    echo -e "${RED}❌ Web container is not running${NC}"
    echo "   Run: docker-compose up -d"
fi

if docker ps | grep -q "spark-db"; then
    echo -e "${GREEN}✅ Database container (spark-db) is running${NC}"
else
    echo -e "${RED}❌ Database container is not running${NC}"
    echo "   Run: docker-compose up -d"
fi

if docker ps | grep -q "spark-pma"; then
    echo -e "${GREEN}✅ phpMyAdmin container (spark-pma) is running${NC}"
else
    echo -e "${YELLOW}⚠️  phpMyAdmin container is not running${NC}"
fi

# Check database initialization
echo ""
echo "3. Checking database..."
TABLES=$(docker exec spark-db mysql -uroot -prootpassword spark -e "SHOW TABLES;" 2>/dev/null | wc -l)

if [ $TABLES -gt 15 ]; then
    echo -e "${GREEN}✅ Database initialized ($TABLES tables found)${NC}"
    
    # Check specific important tables
    if docker exec spark-db mysql -uroot -prootpassword spark -e "DESCRIBE owner_parkir;" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Table 'owner_parkir' exists${NC}"
    else
        echo -e "${RED}❌ Table 'owner_parkir' NOT FOUND!${NC}"
        echo "   This will cause owner registration to fail."
    fi
    
    if docker exec spark-db mysql -uroot -prootpassword spark -e "SELECT * FROM role_pengguna WHERE nama_role='owner';" 2>/dev/null | grep -q "owner"; then
        echo -e "${GREEN}✅ Role 'owner' exists${NC}"
    else
        echo -e "${RED}❌ Role 'owner' NOT FOUND!${NC}"
    fi
    
else
    echo -e "${RED}❌ Database not initialized (only $TABLES tables found)${NC}"
    echo "   Expected at least 15 tables."
    echo "   Try: docker-compose down && docker volume rm spark_db_data && docker-compose up -d"
fi

# Check web server
echo ""
echo "4. Checking web server..."
if curl -s http://localhost:8080 > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Web server is accessible at http://localhost:8080${NC}"
else
    echo -e "${RED}❌ Web server is not accessible${NC}"
    echo "   Check if port 8080 is available"
fi

# Check phpMyAdmin
echo ""
echo "5. Checking phpMyAdmin..."
if curl -s http://localhost:8081 > /dev/null 2>&1; then
    echo -e "${GREEN}✅ phpMyAdmin is accessible at http://localhost:8081${NC}"
else
    echo -e "${YELLOW}⚠️  phpMyAdmin is not accessible${NC}"
fi

# Summary
echo ""
echo "===================================="
echo "📊 Summary"
echo "===================================="
echo ""
echo "Access URLs:"
echo "  🌐 Main App:     http://localhost:8080"
echo "  🔐 Admin Panel:  http://localhost:8080/admin/login.php"
echo "  👤 Owner Login:  http://localhost:8080/owner/login.php"
echo "  📊 phpMyAdmin:   http://localhost:8081"
echo ""
echo "Default Credentials:"
echo "  Admin Email:     admin@spark.com"
echo "  Admin Password:  admin123"
echo ""
echo "===================================="
echo ""

# Check if everything is OK
if docker ps | grep -q "spark-app" && docker ps | grep -q "spark-db" && [ $TABLES -gt 15 ]; then
    echo -e "${GREEN}🎉 All checks passed! Your SPARK installation is ready!${NC}"
    echo ""
    echo "Next steps:"
    echo "  1. Open http://localhost:8080"
    echo "  2. Try registering as owner (should work now!)"
    echo "  3. Login as admin to manage the system"
else
    echo -e "${RED}⚠️  Some checks failed. Please review the errors above.${NC}"
    echo ""
    echo "Common fixes:"
    echo "  • Start Docker: docker-compose up -d"
    echo "  • Reset database: docker-compose down && docker volume rm spark_db_data && docker-compose up -d"
    echo "  • View logs: docker-compose logs -f"
fi

echo ""
