# 🔧 Panduan Memperbaiki Error Registrasi Owner

## ❌ Masalah
Ketika mencoba registrasi owner, muncul error:
```
Registrasi Gagal
Terjadi kesalahan sistem. Silakan coba lagi.
```

## 🔍 Penyebab
Error ini terjadi karena:
1. **Tabel `owner_parkir` belum ada** di database
2. **Role 'owner' (ID = 3) belum terdaftar** di tabel `role_pengguna`

## ✅ Solusi

### **Langkah 1: Cek Status Requirement**

Buka browser dan akses:
```
http://localhost:8080/database/check-owner-requirements.php
```

Script ini akan menampilkan:
- ✅ Status tabel `role_pengguna`
- ✅ Apakah role 'owner' sudah ada
- ✅ Status tabel `owner_parkir`
- ✅ Struktur tabel yang diperlukan

---

### **Langkah 2: Jalankan Fix Script**

Jika ada masalah, jalankan:
```
http://localhost:8080/database/fix-owner-registration.php
```

Script ini akan:
1. ✅ Menambahkan role 'owner' dengan ID = 3
2. ✅ Membuat tabel `owner_parkir` dengan struktur lengkap
3. ✅ Verifikasi semua requirement terpenuhi
4. ✅ Test query untuk memastikan semuanya berfungsi

---

### **Langkah 3: Coba Registrasi Lagi**

Setelah fix script berhasil, coba registrasi owner lagi:
```
http://localhost:8080/owner/register.php
```

Isi form dengan:
- **Nama Pemilik**: Nama Anda
- **Email**: email@example.com
- **Password**: minimal 6 karakter
- **Konfirmasi Password**: sama dengan password
- **Nomor Telepon**: 081234567890
- **Nama Parkir**: Nama lokasi parkir Anda

---

## 🐛 Jika Masih Error

### **Lihat Error Detail**

Sekarang error message akan menampilkan detail masalahnya. Contoh:
```
Terjadi kesalahan sistem: Table 'spark.owner_parkir' doesn't exist
```

### **Solusi Berdasarkan Error:**

#### Error: "Table 'spark.owner_parkir' doesn't exist"
**Solusi:** Jalankan `fix-owner-registration.php` lagi

#### Error: "Cannot add foreign key constraint"
**Solusi:** 
1. Pastikan tabel `data_pengguna` sudah ada
2. Jalankan `fix-owner-registration.php` yang akan drop dan recreate tabel

#### Error: "Duplicate entry '3' for key 'PRIMARY'"
**Solusi:** Role owner sudah ada, ini bukan masalah. Lanjutkan saja.

#### Error: "Unknown column 'role_pengguna' in 'field list'"
**Solusi:** 
1. Cek struktur tabel `data_pengguna`
2. Pastikan ada kolom `role_pengguna`

---

## 📊 File yang Dibuat

### 1. `check-owner-requirements.php`
Script untuk mengecek semua requirement registrasi owner

### 2. `fix-owner-registration.php`
Script untuk memperbaiki database (menambah role & tabel)

### 3. `fix-owner-registration.sql`
SQL manual jika ingin run via phpMyAdmin

---

## 🎯 Struktur Database yang Diperlukan

### Tabel: `role_pengguna`
```sql
INSERT INTO role_pengguna (id_role, nama_role) VALUES (3, 'owner');
```

### Tabel: `owner_parkir`
```sql
CREATE TABLE owner_parkir (
  id_owner_parkir INT AUTO_INCREMENT PRIMARY KEY,
  id_owner INT NOT NULL,
  nama_parkir VARCHAR(255) NOT NULL,
  deskripsi_parkir TEXT,
  lokasi_parkir VARCHAR(255),
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  total_slot INT DEFAULT 0,
  slot_tersedia INT DEFAULT 0,
  harga_per_jam DECIMAL(10,2) DEFAULT 0,
  jam_buka TIME,
  jam_tutup TIME,
  foto_parkir VARCHAR(255),
  status_parkir ENUM('aktif','nonaktif','maintenance') DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_owner) REFERENCES data_pengguna(id_pengguna) ON DELETE CASCADE
);
```

---

## 💡 Tips

1. **Selalu jalankan `check-owner-requirements.php` terlebih dahulu** untuk melihat status
2. **Jika fix script gagal**, coba jalankan SQL manual via phpMyAdmin
3. **Setelah fix berhasil**, error message akan lebih jelas jika ada masalah lain
4. **Backup database** sebelum menjalankan fix script

---

## 📞 Bantuan Lebih Lanjut

Jika masih ada masalah:
1. Screenshot error message yang muncul
2. Jalankan `check-owner-requirements.php` dan screenshot hasilnya
3. Cek PHP error log di server

---

**Dibuat:** 2026-01-06  
**Status:** ✅ READY TO USE
