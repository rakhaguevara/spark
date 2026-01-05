#!/bin/bash

# ========================================
# SPARK VPS Deployment Script
# ========================================
# This script automates the deployment process
# Run: bash deploy.sh

set -e  # Exit on error

echo "🚀 SPARK Deployment Script"
echo "================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ========================================
# 1. PRE-DEPLOYMENT CHECKS
# ========================================
echo -e "\n${YELLOW}[1/7] Running pre-deployment checks...${NC}"

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}ERROR: .env file not found!${NC}"
    echo "Please copy .env.production to .env and configure it:"
    echo "  cp .env.production .env"
    echo "  nano .env"
    exit 1
fi

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}ERROR: Docker is not installed!${NC}"
    echo "Please install Docker first: https://docs.docker.com/engine/install/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}ERROR: Docker Compose is not installed!${NC}"
    echo "Please install Docker Compose first"
    exit 1
fi

echo -e "${GREEN}✓ Pre-deployment checks passed${NC}"

# ========================================
# 2. BACKUP EXISTING DATA (if exists)
# ========================================
echo -e "\n${YELLOW}[2/7] Backing up existing data...${NC}"

if [ -d "backups" ]; then
    BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
    mkdir -p "backups/pre-deploy-$BACKUP_DATE"
    
    # Backup database if container is running
    if docker ps | grep -q spark-db; then
        echo "Creating database backup..."
        docker exec spark-db mysqldump -u root -p${DB_PASS:-rootpassword} spark > "backups/pre-deploy-$BACKUP_DATE/database.sql" 2>/dev/null || true
    fi
    
    echo -e "${GREEN}✓ Backup created in backups/pre-deploy-$BACKUP_DATE${NC}"
else
    mkdir -p backups
    echo "No existing data to backup"
fi

# ========================================
# 3. STOP EXISTING CONTAINERS
# ========================================
echo -e "\n${YELLOW}[3/7] Stopping existing containers...${NC}"

if docker ps | grep -q spark; then
    docker-compose -f docker-compose.production.yml down
    echo -e "${GREEN}✓ Containers stopped${NC}"
else
    echo "No running containers found"
fi

# ========================================
# 4. PULL LATEST IMAGES
# ========================================
echo -e "\n${YELLOW}[4/7] Pulling latest Docker images...${NC}"
docker-compose -f docker-compose.production.yml pull
echo -e "${GREEN}✓ Images updated${NC}"

# ========================================
# 5. BUILD APPLICATION
# ========================================
echo -e "\n${YELLOW}[5/7] Building application...${NC}"
docker-compose -f docker-compose.production.yml build --no-cache
echo -e "${GREEN}✓ Application built${NC}"

# ========================================
# 6. START CONTAINERS
# ========================================
echo -e "\n${YELLOW}[6/7] Starting containers...${NC}"
docker-compose -f docker-compose.production.yml up -d
echo -e "${GREEN}✓ Containers started${NC}"

# ========================================
# 7. VERIFY DEPLOYMENT
# ========================================
echo -e "\n${YELLOW}[7/7] Verifying deployment...${NC}"

# Wait for containers to be healthy
echo "Waiting for containers to be ready..."
sleep 10

# Check if containers are running
if docker ps | grep -q spark-app && docker ps | grep -q spark-db; then
    echo -e "${GREEN}✓ All containers are running${NC}"
else
    echo -e "${RED}ERROR: Some containers failed to start${NC}"
    echo "Check logs with: docker-compose -f docker-compose.production.yml logs"
    exit 1
fi

# Check database connection
echo "Testing database connection..."
if docker exec spark-db mysql -u root -p${DB_PASS:-rootpassword} -e "SHOW DATABASES;" &> /dev/null; then
    echo -e "${GREEN}✓ Database is accessible${NC}"
else
    echo -e "${RED}WARNING: Database connection test failed${NC}"
fi

# ========================================
# DEPLOYMENT COMPLETE
# ========================================
echo -e "\n${GREEN}================================${NC}"
echo -e "${GREEN}🎉 Deployment Complete!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "Application URL: ${BASEURL:-http://localhost}"
echo "Admin Panel: ${BASEURL:-http://localhost}/admin/login.php"
echo "Owner Panel: ${BASEURL:-http://localhost}/owner/login.php"
echo ""
echo "Default Credentials (CHANGE IMMEDIATELY):"
echo "  Admin: admin@spark.com / admin123"
echo "  Owner: owner.jakarta@spark.com / owner123"
echo "  User: budi.santoso@email.com / user123"
echo ""
echo "Useful Commands:"
echo "  View logs: docker-compose -f docker-compose.production.yml logs -f"
echo "  Restart: docker-compose -f docker-compose.production.yml restart"
echo "  Stop: docker-compose -f docker-compose.production.yml down"
echo "  Backup DB: bash backup.sh"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT: Change default passwords immediately!${NC}"
