#!/bin/bash

# ========================================
# SPARK Database Backup Script
# ========================================
# Run: bash backup.sh

set -e

echo "💾 SPARK Database Backup"
echo "================================"

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Create backup directory
BACKUP_DIR="backups"
mkdir -p "$BACKUP_DIR"

# Generate backup filename with timestamp
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/spark_backup_$BACKUP_DATE.sql"

# Create backup
echo "Creating backup..."
docker exec spark-db mysqldump \
    -u root \
    -p${DB_PASS:-rootpassword} \
    ${DB_NAME:-spark} \
    > "$BACKUP_FILE"

# Compress backup
echo "Compressing backup..."
gzip "$BACKUP_FILE"

echo "✓ Backup created: ${BACKUP_FILE}.gz"

# Clean old backups (keep last 7 days)
RETENTION_DAYS=${BACKUP_RETENTION_DAYS:-7}
echo "Cleaning backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR" -name "spark_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "✓ Backup complete!"
echo ""
echo "Backup location: ${BACKUP_FILE}.gz"
echo "Backup size: $(du -h ${BACKUP_FILE}.gz | cut -f1)"
