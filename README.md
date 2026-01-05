# 🅿️ SPARK - Smart Parking System

Modern web-based parking management system with real-time booking, QR code ticketing, and interactive maps.

## 🚀 Quick Start (Choose One)

### ⭐ Option 1: Docker (Recommended - Easiest)

**Requirements:** Docker Desktop installed

```bash
# Clone repository
git clone <repo-url> spark
cd spark

# Start all services (auto-initialize database!)
docker-compose up -d

# Wait 30 seconds for database initialization
# Access application
```

**Access points:**
- 🌐 App: http://localhost:8080
- 🔐 Admin: http://localhost:8080/admin/login.php
- 📊 PhpMyAdmin: http://localhost:8081

**Credentials:**
- Admin Email: `admin@spark.com`
- Admin Password: `admin123`

**✨ What's Auto-Setup:**
- ✅ All 18 database tables (including `owner_parkir`)
- ✅ 3 Roles (user, admin, owner)
- ✅ Default admin account
- ✅ 10 Sample parking locations
- ✅ Vehicle types & pricing

**Stop services:**
```bash
docker-compose down
```

**Reset database (fresh start):**
```bash
docker-compose down
docker volume rm spark_db_data
docker-compose up -d
```

📖 **Detailed Guide:** See [DOCKER_SETUP_GUIDE.md](DOCKER_SETUP_GUIDE.md)

---

### 🔧 Option 2: Manual Setup (Local Install)

**Requirements:**
- PHP 8.0+
- MySQL/MariaDB
- Apache/Nginx

```bash
# Clone repository
git clone <repo-url> spark
cd spark

# Create database
mysql -u root -p
> CREATE DATABASE spark;
> exit;

# Import database
mysql -u root -p spark < database/000-complete-setup.sql

# Update config/database.php with your credentials

# Set folder permissions (Linux/Mac)
chmod -R 777 uploads/
```

Access: http://localhost/spark/public/

---

### 🪟 Option 3: Auto Setup Scripts

**For Linux/Mac:**
```bash
bash setup.sh
```

**For Windows:**
```batch
setup.bat
```

---

## 📖 Full Documentation

See [SETUP_GUIDE.md](SETUP_GUIDE.md) for detailed setup instructions and troubleshooting.

## ✨ Features

- 🗺️ **Interactive Map** - Real-time parking location with Google Maps
- 🎫 **QR Ticketing** - Digital tickets with QR code check-in/out
- 💳 **Payment Integration** - Multiple payment methods support
- 📊 **Admin Dashboard** - Complete analytics with interactive charts
- 🔐 **Secure Authentication** - Role-based access control
- 📱 **Responsive Design** - Works on desktop and mobile
- 🌍 **Multi-location** - Support for multiple parking areas
- ⭐ **Review System** - User ratings and reviews

## 🛠️ Tech Stack

- **Backend:** PHP 8.0+
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Maps:** Google Maps API
- **Charts:** Chart.js
- **QR Code:** PHP QR Code Library

## 📋 Requirements

- PHP >= 8.0
- MySQL >= 5.7 or MariaDB >= 10.2
- Apache/Nginx web server
- Composer (optional, for dependencies)

## 🔧 Configuration

### Database Setup
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spark');
```

### Base URL
Auto-detected, or set in `config/config.php`:
```php
define('BASEURL', 'http://localhost/spark');
```

### Security Salt
Set in `config/config.php` or use environment variable:
```php
define('SECRET_SALT', 'your-random-secret-here');
```

## 🎯 Access URLs

- **User Portal:** `http://localhost/spark`
- **User Login:** `http://localhost/spark/pages/login.php`
- **User Register:** `http://localhost/spark/pages/register.php`
- **Admin Panel:** `http://localhost/spark/admin/login.php`

## 📁 Project Structure

```
spark/
├── actions/          # Form processing scripts
├── admin/           # Admin panel
│   ├── dashboard.php
│   ├── parking.php
│   └── includes/
├── api/             # API endpoints
├── assets/          # CSS, JS, images
├── config/          # Configuration files
├── database/        # SQL files & setup script
├── functions/       # Helper functions
├── includes/        # Reusable components
├── pages/           # User-facing pages
├── uploads/         # User uploads
├── setup.sh         # Quick setup (Linux/Mac)
├── setup.bat        # Quick setup (Windows)
└── SETUP_GUIDE.md   # Detailed setup guide
```

## 🐛 Troubleshooting

### Column not found error
Run the database setup script:
```bash
php database/setup.php
```
Or visit: `http://localhost/spark/database/setup.php`

### Permission denied (uploads)
```bash
chmod -R 777 uploads/
```

### Database connection failed
Check credentials in `config/database.php`

## 🔄 Updates & Migration

When pulling new changes:
```bash
git pull origin main
php database/setup.php  # Auto-updates schema
```

## 📝 License

This project is for educational purposes.

## 👥 Contributing

1. Fork the repository
2. Create your feature branch
3. Run `php database/setup.php` to ensure schema is up-to-date
4. Commit your changes
5. Push to the branch
6. Create a Pull Request

## 📞 Support

For issues and questions:
1. Check [SETUP_GUIDE.md](SETUP_GUIDE.md)
2. Run `database/setup.php`
3. Check PHP error logs
4. Verify database credentials

## 🎉 Credits

Developed with ❤️ for smart parking management.
