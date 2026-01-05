# 🚀 SPARK VPS Deployment Guide

Complete guide for deploying SPARK to a production VPS server.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [VPS Setup](#vps-setup)
3. [Deployment Steps](#deployment-steps)
4. [Post-Deployment](#post-deployment)
5. [Maintenance](#maintenance)
6. [Troubleshooting](#troubleshooting)

---

## 🔧 Prerequisites

### VPS Requirements:
- **OS:** Ubuntu 20.04/22.04 LTS or Debian 11/12
- **RAM:** Minimum 2GB (4GB recommended)
- **Storage:** Minimum 20GB
- **CPU:** 2 cores recommended

### Software Required:
- Docker 20.10+
- Docker Compose 2.0+
- Git
- SSH access

---

## 🖥️ VPS Setup

### 1. Connect to VPS

```bash
ssh root@your-vps-ip
```

### 2. Update System

```bash
apt update && apt upgrade -y
```

### 3. Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Start Docker service
systemctl start docker
systemctl enable docker

# Verify installation
docker --version
```

### 4. Install Docker Compose

```bash
# Download Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

# Make executable
chmod +x /usr/local/bin/docker-compose

# Verify installation
docker-compose --version
```

### 5. Install Git

```bash
apt install git -y
```

### 6. Configure Firewall

```bash
# Install UFW
apt install ufw -y

# Allow SSH (IMPORTANT!)
ufw allow 22/tcp

# Allow HTTP/HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Enable firewall
ufw enable

# Check status
ufw status
```

---

## 📦 Deployment Steps

### 1. Clone Repository

```bash
# Navigate to web directory
cd /var/www

# Clone your repository
git clone https://github.com/yourusername/spark.git
cd spark
```

### 2. Configure Environment

```bash
# Copy production environment template
cp .env.production .env

# Edit environment file
nano .env
```

**CRITICAL: Update these values in .env:**

```bash
# Database
DB_NAME=spark_production
DB_USER=spark_user
DB_PASS=YOUR_STRONG_PASSWORD_HERE  # Generate strong password!

# Application
BASEURL=https://yourdomain.com  # Your actual domain
APP_ENV=production
APP_DEBUG=false

# Security
SECRET_SALT=GENERATE_RANDOM_64_CHAR_STRING  # Use: openssl rand -base64 48

# Admin
ADMIN_EMAIL=admin@yourdomain.com
ADMIN_PASSWORD=STRONG_ADMIN_PASSWORD  # Change after first login!
```

**Generate secure passwords:**

```bash
# Generate database password
openssl rand -base64 32

# Generate secret salt
openssl rand -base64 48
```

### 3. Set Permissions

```bash
# Create uploads directory
mkdir -p uploads
chmod 755 uploads

# Create backups directory
mkdir -p backups
chmod 755 backups

# Make scripts executable
chmod +x deploy.sh backup.sh
```

### 4. Deploy Application

```bash
# Run deployment script
bash deploy.sh
```

The script will:
- ✅ Check prerequisites
- ✅ Backup existing data
- ✅ Stop old containers
- ✅ Pull latest images
- ✅ Build application
- ✅ Start containers
- ✅ Verify deployment

### 5. Verify Deployment

```bash
# Check running containers
docker ps

# Check logs
docker-compose -f docker-compose.production.yml logs -f

# Test database
docker exec spark-db mysql -u spark_user -p -e "SHOW DATABASES;"
```

---

## 🔒 Post-Deployment

### 1. Change Default Passwords

**Immediately after deployment:**

1. Login as admin: `https://yourdomain.com/admin/login.php`
2. Go to Settings → Change Password
3. Update admin password
4. Repeat for owner and user accounts

### 2. Configure SSL/HTTPS (Recommended)

#### Option A: Using Certbot (Let's Encrypt)

```bash
# Install Certbot
apt install certbot python3-certbot-nginx -y

# Get SSL certificate
certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
certbot renew --dry-run
```

#### Option B: Using Nginx Reverse Proxy

```bash
# Install Nginx
apt install nginx -y

# Create Nginx config
nano /etc/nginx/sites-available/spark
```

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Enable site
ln -s /etc/nginx/sites-available/spark /etc/nginx/sites-enabled/

# Test configuration
nginx -t

# Restart Nginx
systemctl restart nginx
```

### 3. Set Up Automated Backups

```bash
# Create cron job for daily backups
crontab -e

# Add this line (backup at 2 AM daily)
0 2 * * * cd /var/www/spark && bash backup.sh >> /var/log/spark-backup.log 2>&1
```

### 4. Configure Monitoring (Optional)

```bash
# Install monitoring tools
apt install htop nethogs -y

# Monitor resources
htop

# Monitor network
nethogs
```

---

## 🔄 Maintenance

### Update Application

```bash
cd /var/www/spark

# Pull latest code
git pull origin main

# Redeploy
bash deploy.sh
```

### Manual Backup

```bash
# Run backup script
bash backup.sh

# Or manual backup
docker exec spark-db mysqldump -u spark_user -p spark_production > backup_$(date +%Y%m%d).sql
```

### Restore from Backup

```bash
# Stop application
docker-compose -f docker-compose.production.yml down

# Restore database
docker-compose -f docker-compose.production.yml up -d db
docker exec -i spark-db mysql -u spark_user -p spark_production < backup_20260106.sql

# Start application
docker-compose -f docker-compose.production.yml up -d
```

### View Logs

```bash
# All logs
docker-compose -f docker-compose.production.yml logs -f

# Specific service
docker-compose -f docker-compose.production.yml logs -f web
docker-compose -f docker-compose.production.yml logs -f db

# Last 100 lines
docker-compose -f docker-compose.production.yml logs --tail=100
```

### Restart Services

```bash
# Restart all
docker-compose -f docker-compose.production.yml restart

# Restart specific service
docker-compose -f docker-compose.production.yml restart web
docker-compose -f docker-compose.production.yml restart db
```

---

## 🐛 Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose -f docker-compose.production.yml logs

# Check container status
docker ps -a

# Rebuild containers
docker-compose -f docker-compose.production.yml down
docker-compose -f docker-compose.production.yml build --no-cache
docker-compose -f docker-compose.production.yml up -d
```

### Database Connection Error

```bash
# Check database container
docker exec spark-db mysql -u root -p

# Check environment variables
docker exec spark-app env | grep DB_

# Recreate database
docker-compose -f docker-compose.production.yml down
docker volume rm spark_db_data
docker-compose -f docker-compose.production.yml up -d
```

### Permission Issues

```bash
# Fix uploads directory
chmod -R 755 uploads
chown -R www-data:www-data uploads

# Fix backups directory
chmod -R 755 backups
```

### Out of Disk Space

```bash
# Check disk usage
df -h

# Clean Docker
docker system prune -a

# Remove old backups
find backups/ -name "*.sql.gz" -mtime +30 -delete
```

### High Memory Usage

```bash
# Check memory
free -h

# Restart containers
docker-compose -f docker-compose.production.yml restart

# Limit container resources (edit docker-compose.production.yml)
# Add under each service:
#   deploy:
#     resources:
#       limits:
#         memory: 512M
```

---

## 📝 Quick Reference

### Useful Commands

```bash
# Start application
docker-compose -f docker-compose.production.yml up -d

# Stop application
docker-compose -f docker-compose.production.yml down

# Restart application
docker-compose -f docker-compose.production.yml restart

# View logs
docker-compose -f docker-compose.production.yml logs -f

# Backup database
bash backup.sh

# Deploy updates
bash deploy.sh

# Access database
docker exec -it spark-db mysql -u spark_user -p

# Check container health
docker ps
docker stats
```

### Important URLs

- **Application:** https://yourdomain.com
- **Admin Panel:** https://yourdomain.com/admin/login.php
- **Owner Panel:** https://yourdomain.com/owner/login.php
- **User Dashboard:** https://yourdomain.com/pages/login.php

### Default Credentials (CHANGE IMMEDIATELY!)

```
Admin:  admin@spark.com / admin123
Owner:  owner.jakarta@spark.com / owner123
User:   budi.santoso@email.com / user123
```

---

## 🔐 Security Checklist

- [ ] Changed all default passwords
- [ ] Generated strong SECRET_SALT
- [ ] Configured SSL/HTTPS
- [ ] Set APP_DEBUG=false
- [ ] Disabled phpMyAdmin in production
- [ ] Configured firewall (UFW)
- [ ] Set up automated backups
- [ ] Restricted database access
- [ ] Updated BASEURL to actual domain
- [ ] Configured proper file permissions

---

## 📞 Support

If you encounter issues:

1. Check logs: `docker-compose -f docker-compose.production.yml logs`
2. Verify environment: `cat .env`
3. Check container status: `docker ps -a`
4. Review this guide's troubleshooting section

---

**Last Updated:** 2026-01-06  
**Version:** 1.0  
**Status:** Production Ready ✅
