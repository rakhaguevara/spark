# Owner Parkir Dashboard - Documentation

## 📋 Overview

Owner Dashboard adalah sistem manajemen lengkap untuk pemilik lahan parkir yang terintegrasi dengan platform SPARK. Sistem ini memungkinkan owner untuk mengelola lahan parkir, memvalidasi tiket, memantau slot real-time, dan melihat riwayat transaksi.

---

## 🎯 Fitur Utama

### 1. **Dashboard** (`/owner/dashboard.php`)
- Overview statistik parkir (lokasi aktif, penghasilan, booking total)
- Kartu selamat datang dengan welcome message
- Link cepat ke fitur utama
- Aktivitas terbaru dan status sistem

**Statistik yang ditampilkan:**
- Total lahan parkir terdaftar
- Lahan parkir aktif
- Total penghasilan dari semua booking
- Total transaksi booking

### 2. **Kelola Lahan Parkir** (`/owner/manage-parking.php`)
Sistem CRUD untuk mengelola lokasi parkir.

**Fitur:**
- ✅ Tambah lahan parkir baru
- ✅ Lihat daftar semua lahan
- ✅ Edit informasi lahan (akan datang)
- ✅ Hapus lahan parkir

**Form Input:**
- Nama lahan parkir
- Alamat lokasi
- Jam buka dan tutup
- Harga per jam (Rp)
- Total slot parkir

**Validasi:**
- Semua field wajib diisi
- Harga dan slot harus angka positif
- Ownership verification (hanya owner dapat edit/hapus)

### 3. **Scan Tiket** (`/owner/scan-ticket.php`)
Fitur pemindaian QR code untuk validasi tiket parkir.

**Teknologi:**
- HTML5 getUserMedia API untuk akses kamera
- jsQR library untuk decode QR code
- Real-time video scanning

**Workflow Scan:**
1. Pilih lokasi parkir
2. Klik "Mulai Kamera"
3. Arahkan kamera ke QR code tiket
4. Sistem otomatis membaca dan memvalidasi
5. Tampilkan hasil (valid/invalid)
6. Update status booking (check-in/check-out)

**Data Validation:**
- Verifikasi ownership parkir
- Cek status booking (must be confirmed/checked_in)
- Validasi QR token dengan booking
- Anti-reuse: QR tidak bisa scan 2x untuk check-in

### 4. **Monitoring Real-time** (`/owner/monitoring.php`)
Monitor status slot parkir secara real-time.

**Informasi yang ditampilkan:**
- Total slot parkir
- Slot terisi (active bookings)
- Slot tersedia
- Persentase occupancy dengan progress bar
- Jam operasional
- Tarif per jam

**Refresh Otomatis:** Halaman refresh setiap 5 detik untuk data real-time

### 5. **Riwayat Pemindaian** (`/owner/scan-history.php`)
Tabel lengkap semua aktivitas pemindaian QR.

**Kolom Tabel:**
- Waktu scan (tanggal & jam)
- Lokasi parkir
- Tipe scan (Masuk/Keluar)
- Booking ID
- Status (Valid/Invalid)

**Fitur:**
- Pagination (20 item per halaman)
- Sorting by waktu (newest first)
- Filter by status (via SQL)

### 6. **Pengaturan Akun** (`/owner/settings.php`)
Manajemen profil dan keamanan akun.

**Sections:**
- **Profil Pengguna:** Edit nama, email, nomor telepon
- **Keamanan:** Update password (minimal 6 char)
- **Logout:** Keluar dari sistem

**Validasi:**
- Email uniqueness check
- Password length validation
- CSRF protection via session

---

## 🛡️ Security & Privacy

### Authorization
- ✅ Session-based authentication
- ✅ Ownership verification untuk semua data
- ✅ Only owner dapat akses own parking locations
- ✅ Role-based access control (role_pengguna = 3)

### Data Privacy
- ✅ **Plate numbers TIDAK ditampilkan** (privacy compliance)
- ✅ QR token disimpan hashed dalam database
- ✅ Prepared statements untuk semua queries (SQL injection prevention)
- ✅ Password hashing dengan bcrypt (PASSWORD_DEFAULT)

### QR Code Validation
- ✅ Token validation (anti-fake QR)
- ✅ Timestamp validation (anti-replay)
- ✅ One-time use QR (prevent double scan)
- ✅ Status check (prevent invalid state transitions)

---

## 📊 Database Tables

### tempat_parkir
```sql
- id_tempat (PK)
- id_pengguna (owner FK)
- nama_tempat
- alamat_tempat
- jam_buka
- jam_tutup
- harga_jam
- total_slot
- status_tempat (aktif/nonaktif)
```

### booking_parkir
```sql
- id_booking (PK)
- id_tempat (FK)
- id_pengguna (user FK)
- status_booking (confirmed, checked_in, completed, cancelled)
- total_harga
- qr_secret (for validation)
- waktu_booking
- waktu_masuk
- waktu_keluar
```

### qr_session
```sql
- id_session (PK)
- id_owner (FK)
- id_tempat (FK)
- id_booking (FK)
- tipe_scan (masuk/keluar)
- status_scan (valid/invalid)
- qr_token
- waktu_scan
```

### data_pengguna
```sql
- role_pengguna = 3 (owner)
```

---

## 🔌 API Endpoints

### POST `/owner/api/validate-ticket.php`
Validasi QR code tiket parkir.

**Request:**
```json
{
  "parking_id": 1,
  "booking_id": 123,
  "qr_token": "abc123..."
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Tiket valid - Scan MASUK tercatat",
  "status": "checked_in",
  "type": "CHECK-IN",
  "booking_id": 123,
  "parking_id": 1
}
```

**Response Error:**
```json
{
  "success": false,
  "message": "Token QR tidak valid - Tiket palsu atau expired",
  "status": "invalid"
}
```

---

## 🎨 UI/UX Design

### Theme
- **Primary Color:** #667eea (Purple-Blue)
- **Secondary Color:** #764ba2 (Dark Purple)
- **Accent Color:** #ffc107 (Yellow)
- **Success Color:** #2ecc71 (Green)
- **Danger Color:** #e74c3c (Red)

### Layout
- **Sidebar Navigation:** Fixed left sidebar (260px, responsive to 70px on mobile)
- **Content Area:** Flexible main content with padding
- **Cards:** White background with box-shadow and hover effects
- **Grid System:** Responsive grid layouts (auto-fit)

### Mobile Responsive
- Sidebar collapses to icon-only on <768px
- Grid layouts stack to single column
- Touch-friendly button sizes (≥44px)
- Viewport meta tag for proper scaling

---

## ⚙️ Installation & Setup

### 1. Create Required Tables
```sql
-- tempat_parkir table (if not exists)
CREATE TABLE IF NOT EXISTS tempat_parkir (
    id_tempat INT PRIMARY KEY AUTO_INCREMENT,
    id_pengguna INT NOT NULL,
    nama_tempat VARCHAR(255) NOT NULL,
    alamat_tempat TEXT,
    jam_buka TIME,
    jam_tutup TIME,
    harga_jam DECIMAL(10,2),
    total_slot INT,
    status_tempat ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES data_pengguna(id_pengguna)
);

-- qr_session table
CREATE TABLE IF NOT EXISTS qr_session (
    id_session INT PRIMARY KEY AUTO_INCREMENT,
    id_owner INT NOT NULL,
    id_tempat INT,
    id_booking INT,
    tipe_scan ENUM('masuk', 'keluar'),
    status_scan ENUM('valid', 'invalid'),
    qr_token VARCHAR(255),
    waktu_scan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_owner) REFERENCES data_pengguna(id_pengguna),
    FOREIGN KEY (id_tempat) REFERENCES tempat_parkir(id_tempat),
    FOREIGN KEY (id_booking) REFERENCES booking_parkir(id_booking)
);
```

### 2. Update booking_parkir
```sql
ALTER TABLE booking_parkir ADD COLUMN IF NOT EXISTS qr_secret VARCHAR(255);
```

### 3. Owner Auth Functions
File: `/functions/owner-auth.php`

```php
function requireOwnerLogin()
function getCurrentOwner()
function startSession()
```

### 4. Access URLs
- Dashboard: `http://localhost/owner/dashboard.php`
- Manage Parking: `http://localhost/owner/manage-parking.php`
- Scan Ticket: `http://localhost/owner/scan-ticket.php`
- Monitoring: `http://localhost/owner/monitoring.php`
- History: `http://localhost/owner/scan-history.php`
- Settings: `http://localhost/owner/settings.php`

---

## 📱 Testing

### Manual Test Cases

#### Test 1: Register & Login
1. Go to `/owner/register.php`
2. Fill form dengan data valid
3. Submit → see success badge
4. Redirect ke login
5. Login dengan credentials

#### Test 2: Add Parking Location
1. Dashboard → Kelola Lahan
2. Click "Tambah Lahan"
3. Fill parking info
4. Submit → see success message
5. New parking appears di grid

#### Test 3: QR Scan
1. Create booking di user dashboard
2. Go to owner's `/owner/scan-ticket.php`
3. Select parking location
4. Click "Mulai Kamera"
5. Point camera ke QR code
6. See validation result

#### Test 4: Monitoring
1. Go to `/owner/monitoring.php`
2. See parking cards dengan slot status
3. Make active booking
4. Refresh page → see slot update
5. Auto-refresh every 5 seconds

#### Test 5: Scan History
1. Go to `/owner/scan-history.php`
2. See table dengan scan records
3. Pagination works
4. Each row shows scan details

---

## 🐛 Error Handling

### User-Facing Errors
- Alert boxes dengan clear messages
- Color-coded (red=error, green=success, yellow=warning)
- Dismissible dengan X button

### Logging
- All errors logged ke error_log
- PDOException messages captured
- Helpful for debugging

### Fallback Behaviors
- Camera fails: Alert message, graceful exit
- Invalid QR: Show error, stay on page
- Database error: Transaction rollback, error message

---

## 🚀 Future Enhancements

1. **Edit Parking:** Allow update parking info
2. **Batch QR Export:** Generate multiple QR codes at once
3. **Analytics Dashboard:** Charts and reports
4. **SMS Notifications:** Notify owner on check-in/out
5. **Mobile App:** Native iOS/Android app
6. **Payment Integration:** Direct integration dengan payment gateway
7. **Revenue Reports:** Monthly/yearly income analytics
8. **Staff Management:** Add staff accounts untuk scanning

---

## 📞 Support

**Issues atau pertanyaan?**
- Check logs: `/var/log/php-errors.log`
- Verify database connections
- Ensure proper permissions on directories
- Check CORS headers if using API externally

**Contact:** support@spark-parking.local

---

## ✅ Checklist

- [x] Dashboard dengan statistics
- [x] Manage parking locations (CRUD)
- [x] QR code scanning dengan HTML5 camera
- [x] Real-time monitoring
- [x] Scan history dengan pagination
- [x] Account settings & profile
- [x] Security: authorization & privacy
- [x] Responsive mobile design
- [x] Error handling & validation
- [x] Database integration

---

**Last Updated:** January 5, 2026
**Version:** 1.0
**Status:** Production Ready ✅
