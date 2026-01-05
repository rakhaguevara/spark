# 🚀 SPARK - Quick Reference

## ⚡ Quick Start
```bash
docker-compose up -d
```
Wait 30 seconds, then open: http://localhost:8080

## 🔑 Default Credentials
```
Admin:  admin@spark.com / admin123
DB:     root / rootpassword
```

## 🌐 Access URLs
```
App:        http://localhost:8080
Admin:      http://localhost:8080/admin/login.php
Owner:      http://localhost:8080/owner/login.php
phpMyAdmin: http://localhost:8081
```

## 🛠️ Common Commands

### Start/Stop
```bash
docker-compose up -d        # Start
docker-compose down         # Stop
docker-compose restart      # Restart
```

### Logs
```bash
docker-compose logs -f      # All logs
docker-compose logs -f web  # Web only
docker-compose logs -f db   # Database only
```

### Reset Database
```bash
docker-compose down
docker volume rm spark_db_data
docker-compose up -d
```

### Verify Setup
```bash
# Windows
.\verify-setup.ps1

# Linux/Mac
bash verify-setup.sh
```

## 🐛 Quick Fixes

### Port Already in Use
Edit `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # Change 8080 to another port
```

### Database Not Initialized
```bash
docker-compose down
docker volume rm spark_db_data
docker-compose up -d
```

### Owner Registration Error
Check: http://localhost:8080/database/check-owner-requirements.php

### Container Not Starting
```bash
docker-compose logs -f
```

## 📊 What's Auto-Setup?
- ✅ 18 Database tables
- ✅ 3 Roles (user, admin, owner)
- ✅ Admin account
- ✅ 10 Parking locations
- ✅ Vehicle types

## 📖 Full Documentation
- Setup Guide: `DOCKER_SETUP_GUIDE.md`
- Zero Config: `ZERO_CONFIG_SETUP.md`
- Main README: `README.md`

---
**Version:** 1.0 | **Last Updated:** 2026-01-06
