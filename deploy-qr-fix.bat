@echo off
REM ========================================
REM DEPLOY QR FIX TO VPS (Windows)
REM ========================================

echo.
echo ========================================
echo    DEPLOY QR CODE FIX TO VPS
echo ========================================
echo.

REM Step 1: Git add, commit, push
echo [1/3] Adding files to Git...
git add api/fix-missing-qr-tokens.php
git add pages/my-ticket.php
git add QR_CODE_FIX_GUIDE.md
git add deploy-qr-fix.bat
git add deploy-qr-fix.sh

echo.
echo [2/3] Committing changes...
git commit -m "Fix: QR code generation issue - auto-fix missing tokens"

echo.
echo [3/3] Pushing to remote repository...
git push origin main

echo.
echo ========================================
echo    GIT PUSH COMPLETED!
echo ========================================
echo.
echo NEXT STEPS - Run on VPS:
echo.
echo 1. SSH to VPS: ssh user@72.62.125.127
echo 2. cd /path/to/spark
echo 3. git pull origin main
echo 4. docker-compose restart web
echo.
echo Then test: http://72.62.125.127:8080/pages/my-ticket.php
echo.
echo ========================================
pause
