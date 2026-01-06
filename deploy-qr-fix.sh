#!/bin/bash

# ========================================
# DEPLOY QR FIX TO VPS
# ========================================

echo "🚀 Deploying QR Code Fix to VPS..."

# Step 1: Git add, commit, push
echo ""
echo "📦 Step 1: Committing changes to Git..."
git add api/fix-missing-qr-tokens.php
git add pages/my-ticket.php
git add QR_CODE_FIX_GUIDE.md
git add deploy-qr-fix.sh

git commit -m "Fix: QR code generation issue - auto-fix missing tokens

- Added api/fix-missing-qr-tokens.php for auto-generating missing QR tokens
- Updated pages/my-ticket.php with auto-fix on page load
- Added manual 'Generate QR Code' button for user control
- Handles bookings without qr_secret field
- Created comprehensive troubleshooting guide"

echo ""
echo "⬆️  Pushing to remote repository..."
git push origin main

echo ""
echo "✅ Git push completed!"
echo ""
echo "========================================"
echo "📋 NEXT STEPS - Run on VPS:"
echo "========================================"
echo ""
echo "ssh user@your-vps-ip"
echo "cd /path/to/spark"
echo "git pull origin main"
echo "docker-compose restart web"
echo ""
echo "Then test: http://your-vps-ip:8080/pages/my-ticket.php"
echo ""
echo "========================================"
