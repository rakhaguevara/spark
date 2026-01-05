## ✅ PANDUAN IMPLEMENTASI OWNER PARKIR SPARK

### 📌 STEP 1: Jalankan Database Setup

Akses URL berikut di browser untuk membuat tabel dan role di database:

```
http://localhost/spark/database/run-owner-setup.php
```

Anda akan melihat:
- ✓ owner_parkir table created
- ✓ Owner role inserted
- ✓ Setup completed successfully

**PENTING:** URL ini hanya bisa diakses dari localhost. Jika ingin production, gunakan phpMyAdmin atau CLI MySQL.

---

### 🔗 STEP 2: Test Flow

#### A. **Register Owner Baru**
1. Buka: `http://localhost/spark/owner/register.php`
2. Isi form dengan data:
   - Nama Pemilik Parkir: `PT. Parkir Sentral`
   - Email: `owner@parkir.com`
   - Password: `password123`
   - Konfirmasi Password: `password123`
   - Nomor Telepon: `081234567890`
   - Nama Parkir: `Parkir Sentral Mall`
3. Klik "Daftar sebagai Owner"
4. Seharusnya redirect ke login page dengan success message

#### B. **Login sebagai Owner**
1. Buka: `http://localhost/spark/owner/login.php`
2. Isi dengan:
   - Email: `owner@parkir.com`
   - Password: `password123`
3. Klik "Log In"
4. Seharusnya redirect ke dashboard dengan welcome message

#### C. **Dashboard Owner**
1. Otomatis redirect ke: `http://localhost/spark/owner/dashboard.php`
2. Lihat statistik yang ditampilkan:
   - Total Lokasi Parkir
   - Parkir Aktif
   - Total Penghasilan
   - Total Booking
3. Klik "Logout" di navbar

---

### 📁 FILE STRUCTURE

```
/owner/
├── login.php              # Login page owner
├── register.php           # Register page owner
├── dashboard.php          # Dashboard owner
└── logout.php             # Logout handler

/functions/
├── owner-auth.php         # Auth functions (isOwnerLoggedIn, etc)
├── owner-login-proses.php # Login processing
└── owner-register-proses.php # Register processing

/database/
├── owner_parkir.sql       # Create table query
└── run-owner-setup.php    # Auto setup script
```

---

### 🔑 KEY FEATURES YANG SUDAH IMPLEMENTASI

✅ **Login Owner**
- Email & password validation
- Session management
- Error/success messages
- Redirect to dashboard

✅ **Register Owner**
- Form validation
- Password hashing (bcrypt)
- Email duplicate check
- Password strength check (min 6 chars)
- Create owner in data_pengguna table
- Create parking location in owner_parkir table

✅ **Authentication**
- `isOwnerLoggedIn()` - Check login status
- `getCurrentOwner()` - Get owner data & parking info
- `requireOwnerLogin()` - Protect pages
- `logoutOwner()` - Logout with session cleanup

✅ **Dashboard**
- Welcome message dengan nama owner
- Statistics cards (4 metrics)
- Action buttons (placeholder)
- Recent activity log
- User profile navbar
- Responsive design

---

### 🎨 DESIGN CONSISTENCY

Semua halaman menggunakan desain yang konsisten dengan:
- **Color Scheme:** Purple gradient (#667eea → #764ba2)
- **Typography:** Segoe UI, sans-serif
- **Components:** Cards, buttons, alerts
- **Responsive:** Mobile-friendly design

---

### 🔒 SECURITY IMPLEMENTED

- ✅ Password hashing (password_hash / password_verify)
- ✅ SQL Injection prevention (PDO prepared statements)
- ✅ XSS prevention (htmlspecialchars())
- ✅ Session validation (requireOwnerLogin)
- ✅ Data sanitization (trim)

---

### 📊 DATABASE SCHEMA

```sql
CREATE TABLE owner_parkir (
  id_owner_parkir INT PRIMARY KEY AUTO_INCREMENT,
  id_owner INT NOT NULL (FK to data_pengguna),
  nama_parkir VARCHAR(255) NOT NULL,
  deskripsi_parkir TEXT,
  lokasi_parkir VARCHAR(255),
  latitude DECIMAL(10,8),
  longitude DECIMAL(11,8),
  total_slot INT DEFAULT 0,
  slot_tersedia INT DEFAULT 0,
  harga_per_jam DECIMAL(10,2) DEFAULT 0,
  jam_buka TIME,
  jam_tutup TIME,
  foto_parkir VARCHAR(255),
  status_parkir ENUM('aktif','nonaktif','maintenance') DEFAULT 'aktif',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

role_pengguna:
- id_role: 1, nama_role: 'user'
- id_role: 2, nama_role: 'admin'
- id_role: 3, nama_role: 'owner' (baru)
```

---

### 🚀 FITUR YANG AKAN DATANG

Setelah setup berhasil, berikutnya akan dikembangkan:

1. **Kelola Parkir**
   - Tambah/edit/hapus lokasi parkir
   - Upload foto parkir
   - Atur tarif parkir
   - Manage parking slots

2. **Manajemen Booking**
   - Lihat booking yang masuk
   - Konfirmasi/tolak booking
   - Tracking booking status

3. **Laporan & Analitik**
   - Grafik pendapatan
   - Statistik penggunaan
   - Export laporan

4. **Pengaturan**
   - Edit profil owner
   - Ubah password
   - Notification preferences

---

### ❓ TROUBLESHOOTING

**Error: "Email tidak terdaftar atau bukan owner"**
- Pastikan email sudah di-register
- Pastikan role_pengguna untuk owner sudah ada
- Cek di database: `SELECT * FROM data_pengguna WHERE email_pengguna = 'email@anda'`

**Error: "Terjadi kesalahan sistem"**
- Cek koneksi database di `config/database.php`
- Cek error log di server
- Lihat di browser console

**Table owner_parkir tidak ada**
- Jalankan: `http://localhost/spark/database/run-owner-setup.php`
- Atau import dari phpMyAdmin: `/database/owner_parkir.sql`

**Session tidak tersimpan**
- Pastikan session_start() dipanggil di setiap file
- Cek php.ini untuk session.save_path
- Clear cookies browser jika perlu

---

### 📞 SUPPORT

Jika ada pertanyaan atau issue, periksa:
1. Error messages di dashboard
2. Browser console (F12)
3. Server logs di /var/log/php atau Xampp logs
4. Database dengan phpMyAdmin

---

**Selamat menggunakan SPARK Owner Parkir Module!** 🎉
