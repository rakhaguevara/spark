# 🐳 SPARK - Docker Setup Guide

## 🚀 Quick Start (Zero Configuration!)

### **Langkah 1: Clone Project**
```bash
git clone <repo-url> spark
cd spark
```

### **Langkah 2: Start Docker**
```bash
docker-compose up -d
```

### **Langkah 3: Tunggu Initialization (30 detik)**
Database akan otomatis:
- ✅ Membuat semua tabel yang diperlukan
- ✅ Menambahkan role (user, admin, owner)
- ✅ Membuat tabel `owner_parkir` untuk registrasi owner
- ✅ Import dummy data (10 lokasi parkir)
- ✅ Membuat admin default

### **Langkah 4: Akses Aplikasi**
```
🌐 Main App:     http://localhost:8080
🔐 Admin Panel:  http://localhost:8080/admin/login.php
📊 phpMyAdmin:   http://localhost:8081
```

---

## 🔑 Default Credentials

### **Admin Login**
```
Email:    admin@spark.com
Password: admin123
```

### **Database (phpMyAdmin)**
```
Host:     db (atau localhost dari luar Docker)
Port:     3308
User:     root
Password: rootpassword
Database: spark
```

---

## 🎯 Apa yang Otomatis Ter-Setup?

### **1. Database Tables (18 tables)**
- ✅ `role_pengguna` - Roles (user, admin, owner)
- ✅ `data_pengguna` - User accounts
- ✅ `owner_parkir` - Owner parking data ⭐ **PENTING!**
- ✅ `tempat_parkir` - Parking locations
- ✅ `slot_parkir` - Parking slots
- ✅ `booking_parkir` - Bookings
- ✅ `qr_session` - QR codes
- ✅ `scan_history` - Scan logs
- ✅ `harga_parkir` - Pricing
- ✅ `jenis_kendaraan` - Vehicle types
- ✅ `kendaraan_pengguna` - User vehicles
- ✅ `notifikasi_pengguna` - Notifications
- ✅ `pembayaran_booking` - Payments
- ✅ `contacts` - Contact messages

### **2. Default Data**
- ✅ 3 Roles (user, admin, owner)
- ✅ 1 Admin account (admin@spark.com)
- ✅ 10 Parking locations (Jakarta, Bandung, Surabaya, dll)
- ✅ 2 Vehicle types (Motor, Mobil)
- ✅ Parking slots untuk setiap lokasi

---

## 🔄 Reset Database (Fresh Start)

Jika ingin reset database ke kondisi awal:

```bash
# Stop containers
docker-compose down

# Remove database volume
docker volume rm spark_db_data

# Start again (akan auto-initialize)
docker-compose up -d
```

---

## 🛠️ Development Mode

### **Live Reload**
File di local folder otomatis sync ke container:
```yaml
volumes:
  - ./:/var/www/html  # Auto-sync semua file
```

Jadi kamu bisa edit file di local, langsung refresh browser!

### **View Logs**
```bash
# Semua logs
docker-compose logs -f

# Database logs saja
docker-compose logs -f db

# Web server logs saja
docker-compose logs -f web
```

### **Masuk ke Container**
```bash
# Masuk ke web container
docker exec -it spark-app bash

# Masuk ke database container
docker exec -it spark-db bash

# Run MySQL command
docker exec -it spark-db mysql -uroot -prootpassword spark
```

---

## 📁 File Structure

```
spark/
├── database/
│   ├── 00-init-complete.sql    ← Initialization script (auto-run)
│   ├── dummy_data.sql           ← Sample data (auto-run)
│   └── *.sql                    ← Other migrations
├── docker-compose.yml           ← Docker configuration
├── Dockerfile                   ← Web server image
└── ...
```

---

## 🐛 Troubleshooting

### **Port sudah digunakan**
```bash
# Ubah port di docker-compose.yml
ports:
  - "8081:80"  # Ubah 8080 ke port lain
```

### **Database tidak ter-initialize**
```bash
# Cek logs
docker-compose logs db

# Jika ada error, reset:
docker-compose down
docker volume rm spark_db_data
docker-compose up -d
```

### **Permission denied (uploads/)**
```bash
# Di dalam container
docker exec -it spark-app bash
chmod -R 777 /var/www/html/uploads
```

### **Tabel owner_parkir tidak ada**
Ini seharusnya **TIDAK TERJADI LAGI** karena `00-init-complete.sql` otomatis membuat tabel ini.

Jika masih terjadi:
```bash
# Reset database
docker-compose down
docker volume rm spark_db_data
docker-compose up -d
```

---

## 🎨 Customization

### **Ubah Database Credentials**
Edit `docker-compose.yml`:
```yaml
environment:
  - MYSQL_ROOT_PASSWORD=your_password
  - MYSQL_DATABASE=your_db_name
```

### **Tambah Init Scripts**
Taruh file `.sql` di `database/` dan mount di docker-compose:
```yaml
volumes:
  - ./database/my-script.sql:/docker-entrypoint-initdb.d/02-my-script.sql
```

Scripts akan dijalankan **alphabetically** (00, 01, 02, dst).

---

## 🚢 Deploy ke Production

### **1. Build Image**
```bash
docker build -t spark-app:latest .
```

### **2. Push ke Registry**
```bash
docker tag spark-app:latest your-registry/spark-app:latest
docker push your-registry/spark-app:latest
```

### **3. Deploy**
```bash
# Di server production
docker-compose -f docker-compose.prod.yml up -d
```

---

## 📊 Database Backup & Restore

### **Backup**
```bash
docker exec spark-db mysqldump -uroot -prootpassword spark > backup.sql
```

### **Restore**
```bash
docker exec -i spark-db mysql -uroot -prootpassword spark < backup.sql
```

---

## ✅ Checklist - Setelah Setup

- [ ] Buka http://localhost:8080 - Homepage muncul
- [ ] Login admin dengan admin@spark.com / admin123
- [ ] Coba registrasi user baru
- [ ] Coba registrasi owner baru ⭐ **HARUS BERHASIL!**
- [ ] Lihat 10 parking locations di homepage
- [ ] Buka phpMyAdmin dan cek tabel `owner_parkir` ada

---

## 💡 Tips

1. **Jangan commit `docker-compose.yml` dengan password production**
2. **Gunakan `.env` file untuk credentials**
3. **Backup database secara berkala**
4. **Monitor logs dengan `docker-compose logs -f`**
5. **Restart containers jika ada perubahan config: `docker-compose restart`**

---

## 🎉 Keuntungan Setup Ini

✅ **Zero Manual Setup** - Langsung jalan tanpa konfigurasi  
✅ **Portable** - Clone & run di mana saja  
✅ **Consistent** - Sama persis di semua environment  
✅ **No More "Works on My Machine"** - Docker guarantee!  
✅ **Auto Database Init** - Semua tabel & data otomatis ter-setup  
✅ **Owner Registration Works** - Tabel `owner_parkir` otomatis ada  

---

**Last Updated:** 2026-01-06  
**Docker Version:** 3.8  
**MariaDB Version:** 10.4  
**PHP Version:** 8.2
