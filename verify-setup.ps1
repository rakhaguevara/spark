# ========================================
# SPARK Docker Setup Verification Script (Windows)
# ========================================

Write-Host "🔍 SPARK Docker Setup Verification" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

# Check if Docker is running
Write-Host "1. Checking Docker..." -ForegroundColor Yellow
try {
    docker info | Out-Null
    Write-Host "✅ Docker is running" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker is not running!" -ForegroundColor Red
    Write-Host "   Please start Docker Desktop first." -ForegroundColor Red
    exit 1
}

# Check if containers are running
Write-Host ""
Write-Host "2. Checking containers..." -ForegroundColor Yellow

$webRunning = docker ps | Select-String "spark-app"
if ($webRunning) {
    Write-Host "✅ Web container (spark-app) is running" -ForegroundColor Green
} else {
    Write-Host "❌ Web container is not running" -ForegroundColor Red
    Write-Host "   Run: docker-compose up -d" -ForegroundColor Yellow
}

$dbRunning = docker ps | Select-String "spark-db"
if ($dbRunning) {
    Write-Host "✅ Database container (spark-db) is running" -ForegroundColor Green
} else {
    Write-Host "❌ Database container is not running" -ForegroundColor Red
    Write-Host "   Run: docker-compose up -d" -ForegroundColor Yellow
}

$pmaRunning = docker ps | Select-String "spark-pma"
if ($pmaRunning) {
    Write-Host "✅ phpMyAdmin container (spark-pma) is running" -ForegroundColor Green
} else {
    Write-Host "⚠️  phpMyAdmin container is not running" -ForegroundColor Yellow
}

# Check database initialization
Write-Host ""
Write-Host "3. Checking database..." -ForegroundColor Yellow

try {
    $tables = docker exec spark-db mysql -uroot -prootpassword spark -e "SHOW TABLES;" 2>$null
    $tableCount = ($tables -split "`n").Count - 1
    
    if ($tableCount -gt 15) {
        Write-Host "✅ Database initialized ($tableCount tables found)" -ForegroundColor Green
        
        # Check owner_parkir table
        try {
            docker exec spark-db mysql -uroot -prootpassword spark -e "DESCRIBE owner_parkir;" 2>$null | Out-Null
            Write-Host "✅ Table 'owner_parkir' exists" -ForegroundColor Green
        } catch {
            Write-Host "❌ Table 'owner_parkir' NOT FOUND!" -ForegroundColor Red
            Write-Host "   This will cause owner registration to fail." -ForegroundColor Red
        }
        
        # Check owner role
        $ownerRole = docker exec spark-db mysql -uroot -prootpassword spark -e "SELECT * FROM role_pengguna WHERE nama_role='owner';" 2>$null
        if ($ownerRole -match "owner") {
            Write-Host "✅ Role 'owner' exists" -ForegroundColor Green
        } else {
            Write-Host "❌ Role 'owner' NOT FOUND!" -ForegroundColor Red
        }
        
    } else {
        Write-Host "❌ Database not initialized (only $tableCount tables found)" -ForegroundColor Red
        Write-Host "   Expected at least 15 tables." -ForegroundColor Red
        Write-Host "   Try: docker-compose down; docker volume rm spark_db_data; docker-compose up -d" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Cannot connect to database" -ForegroundColor Red
}

# Check web server
Write-Host ""
Write-Host "4. Checking web server..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 5 2>$null
    Write-Host "✅ Web server is accessible at http://localhost:8080" -ForegroundColor Green
} catch {
    Write-Host "❌ Web server is not accessible" -ForegroundColor Red
    Write-Host "   Check if port 8080 is available" -ForegroundColor Yellow
}

# Check phpMyAdmin
Write-Host ""
Write-Host "5. Checking phpMyAdmin..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8081" -UseBasicParsing -TimeoutSec 5 2>$null
    Write-Host "✅ phpMyAdmin is accessible at http://localhost:8081" -ForegroundColor Green
} catch {
    Write-Host "⚠️  phpMyAdmin is not accessible" -ForegroundColor Yellow
}

# Summary
Write-Host ""
Write-Host "====================================" -ForegroundColor Cyan
Write-Host "📊 Summary" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Access URLs:"
Write-Host "  🌐 Main App:     http://localhost:8080"
Write-Host "  🔐 Admin Panel:  http://localhost:8080/admin/login.php"
Write-Host "  👤 Owner Login:  http://localhost:8080/owner/login.php"
Write-Host "  📊 phpMyAdmin:   http://localhost:8081"
Write-Host ""
Write-Host "Default Credentials:"
Write-Host "  Admin Email:     admin@spark.com"
Write-Host "  Admin Password:  admin123"
Write-Host ""
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

# Final status
if ($webRunning -and $dbRunning -and $tableCount -gt 15) {
    Write-Host "🎉 All checks passed! Your SPARK installation is ready!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:"
    Write-Host "  1. Open http://localhost:8080"
    Write-Host "  2. Try registering as owner (should work now!)"
    Write-Host "  3. Login as admin to manage the system"
} else {
    Write-Host "⚠️  Some checks failed. Please review the errors above." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Common fixes:"
    Write-Host "  • Start Docker: docker-compose up -d"
    Write-Host "  • Reset database: docker-compose down; docker volume rm spark_db_data; docker-compose up -d"
    Write-Host "  • View logs: docker-compose logs -f"
}

Write-Host ""
