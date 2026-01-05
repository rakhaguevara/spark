# SETUP OWNER PARKIR MODULE

Dokumentasi setup untuk mengimplementasikan modul Owner Parkir di SPARK.

## 📋 Daftar File yang Dibuat

### 1. **Login Owner** (`/owner/login.php`)
- Halaman login khusus untuk pemilik parkir
- Desain mirip dengan admin dan user login
- Pesan selamat datang: "Selamat Datang Owner Parkir! Urus parkiran mu lebih mudah dan fleksibel."

### 2. **Register Owner** (`/owner/register.php`)
- Formulir pendaftaran untuk pemilik parkir baru
- Field: Nama, Email, Password, No. HP, dan Nama Parkir
- Desain konsisten dengan halaman login

### 3. **Login Process** (`/functions/owner-login-proses.php`)
- Validasi login owner
- Verifikasi email dan password
- Session management

### 4. **Register Process** (`/functions/owner-register-proses.php`)
- Validasi data registrasi
- Hash password
- Create owner record di database
- Create initial parking location data

### 5. **Auth Functions** (`/functions/owner-auth.php`)
- `isOwnerLoggedIn()` - Cek apakah owner sudah login
- `getCurrentOwner()` - Ambil data owner yang sedang login
- `requireOwnerLogin()` - Redirect jika belum login
- `logoutOwner()` - Logout owner

### 6. **Dashboard Owner** (`/owner/dashboard.php`)
- Dashboard sederhana untuk owner parkir
- Tampilan statistik:
  - Total lokasi parkir
  - Parkir aktif
  - Total penghasilan
  - Total booking
- Action buttons (akan dikembangkan)
- Aktivitas terbaru

### 7. **Logout Owner** (`/owner/logout.php`)
- Handle logout owner

### 8. **Database Migration** (`/database/owner_parkir.sql`)
- Tabel `owner_parkir` untuk menyimpan data parkiran pemilik
- Foreign key ke `data_pengguna`

## 🗄️ Database Setup

### Jalankan SQL Migration:

1. Buka phpMyAdmin atau terminal MySQL
2. Select database `spark`
3. Jalankan query dari `/database/owner_parkir.sql`:

```sql
-- Create owner_parkir table
CREATE TABLE IF NOT EXISTS `owner_parkir` (
  `id_owner_parkir` int(11) NOT NULL AUTO_INCREMENT,
  `id_owner` int(11) NOT NULL,
  `nama_parkir` varchar(255) NOT NULL,
  `deskripsi_parkir` text,
  `lokasi_parkir` varchar(255),
  `latitude` decimal(10, 8),
  `longitude` decimal(11, 8),
  `total_slot` int(11) DEFAULT 0,
  `slot_tersedia` int(11) DEFAULT 0,
  `harga_per_jam` decimal(10,2) DEFAULT 0,
  `jam_buka` time,
  `jam_tutup` time,
  `foto_parkir` varchar(255),
  `status_parkir` enum('aktif','nonaktif','maintenance') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_owner_parkir`),
  KEY `id_owner` (`id_owner`),
  CONSTRAINT `owner_parkir_ibfk_1` FOREIGN KEY (`id_owner`) REFERENCES `data_pengguna` (`id_pengguna`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure owner role exists
INSERT INTO `role_pengguna` (`nama_role`) VALUES ('owner')
ON DUPLICATE KEY UPDATE `nama_role` = `nama_role`;
```

## 🔑 Role Configuration

Role untuk owner harus memiliki `id_role = 3`. Pastikan di tabel `role_pengguna`:

```sql
SELECT * FROM role_pengguna;
-- Seharusnya ada:
-- id_role: 1, nama_role: user
-- id_role: 2, nama_role: admin
-- id_role: 3, nama_role: owner
```

## 🧪 Testing

### 1. Akses Login Owner:
```
http://localhost/spark/owner/login.php
```

### 2. Akses Register Owner:
```
http://localhost/spark/owner/register.php
```

### 3. Test Register:
- Nama: Test Owner
- Email: owner@test.com
- Password: 123456
- No. HP: 081234567890
- Nama Parkir: Parkir Pusat Kota

### 4. Test Login:
- Email: owner@test.com
- Password: 123456

### 5. Akses Dashboard:
Setelah login berhasil, akan redirect ke:
```
http://localhost/spark/owner/dashboard.php
```

## 📊 Dashboard Features (Saat Ini)

- ✅ Welcome message dengan nama owner
- ✅ Statistik: Total lokasi, parkir aktif, penghasilan, booking
- ✅ Action buttons (placeholder untuk fitur berikutnya)
- ✅ Aktivitas terbaru
- ✅ User profile di navbar
- ✅ Logout functionality

## 🚀 Fitur yang Akan Dikembangkan

1. **Kelola Parkir**
   - Tambah/edit/hapus lokasi parkir
   - Atur harga per jam
   - Manage parking slots

2. **Laporan & Analitik**
   - Grafik penghasilan
   - Statistik booking
   - Laporan transaksi

3. **Manajemen Booking**
   - Lihat booking yang masuk
   - Konfirmasi/tolak booking
   - Riwayat booking

4. **Pengaturan**
   - Edit profil owner
   - Ubah password
   - Pengaturan notifikasi

## 📝 Notes

- Password sudah di-hash menggunakan `password_hash()` dengan PASSWORD_DEFAULT
- Session management menggunakan PHP session
- Semua input sudah di-sanitize dengan `htmlspecialchars()` dan `trim()`
- Database connection menggunakan PDO

## 🔒 Security Considerations

- ✅ Password hashing
- ✅ SQL Injection prevention (prepared statements)
- ✅ XSS prevention (htmlspecialchars)
- ✅ Session validation
- ✅ Required login checks

---

**Dibuat untuk SPARK Parking Management System**
