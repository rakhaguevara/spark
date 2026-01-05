# 🎯 Owner Registration Notification Badge System

## Overview
Sistem notifikasi badge untuk halaman registrasi owner yang menampilkan pesan sukses atau error dengan visual yang menarik dan interaktif.

---

## 📋 Fitur Yang Diimplementasikan

### 1. **Error Badge Notifications (Merah)**
✅ Muncul otomatis ketika ada error dalam proses registrasi
✅ Pesan error spesifik ditampilkan (email duplicate, password tidak match, dll)
✅ Animasi **pulse** untuk menarik perhatian user
✅ **Dismiss button (X)** untuk menutup badge secara manual
✅ Animasi slide-in dari kanan atas

**Error Messages yang Ditampilkan:**
- ❌ "Email sudah terdaftar" - saat email sudah ada di database
- ❌ "Password dan konfirmasi password tidak cocok" - saat password berbeda
- ❌ "Password minimal 6 karakter" - saat password < 6 chars
- ❌ "Semua field wajib diisi" - saat ada field kosong
- ❌ "Terjadi kesalahan sistem. Silakan coba lagi." - saat database error

### 2. **Success Badge Notifications (Hijau)**
✅ Muncul otomatis ketika registrasi berhasil
✅ Menampilkan pesan: "✓ Registrasi Berhasil! Akun Owner Anda telah dibuat"
✅ **Countdown timer** 3 detik sebelum redirect
✅ **Auto-redirect ke login.php** setelah countdown selesai
✅ Animasi smooth slide-in dan fade-out

### 3. **Visual Design**
- **Gradient backgrounds:**
  - Error: Red gradient (#e74c3c → #c0392b)
  - Success: Green gradient (#2ecc71 → #27ae60)
- **Box shadow** untuk depth effect
- **Responsive design** - menyesuaikan dengan ukuran layar mobile
- **Icons (SVG)** - checkmark untuk success, X untuk error
- **Typography:**
  - Title: Font weight 700, size 15px
  - Message: Font weight 500, size 13px
  - Countdown: Font weight normal, size 12px

### 4. **Animasi**
```css
@keyframes slideIn {
  from: translateX(400px), opacity 0
  to: translateX(0), opacity 1
  duration: 0.4s ease-out
}

@keyframes slideOut {
  from: translateX(0), opacity 1
  to: translateX(400px), opacity 0
  duration: 0.4s ease-out
}

@keyframes pulse {
  0%, 100%: box-shadow 0 10px 30px rgba(0,0,0,0.2)
  50%: box-shadow 0 10px 40px rgba(231, 76, 60, 0.4)
  duration: 2s ease-in-out (start after 0.5s)
}
```

---

## 📁 File Yang Dimodifikasi

### `/owner/register.php`
- Menambahkan CSS untuk notification badge styling
- Menambahkan HTML untuk error/success notifications
- Menambahkan JavaScript untuk countdown timer dan redirect
- Menambahkan dismiss button untuk error badge

### `/functions/owner-register-proses.php` (No Changes Needed)
Sudah menggunakan $_SESSION untuk menyimpan pesan error/success:
```php
$_SESSION['error'] = 'Email sudah terdaftar';
// atau
$_SESSION['success'] = 'Registrasi berhasil!';
header('Location: ' . BASEURL . '/owner/register.php');
```

---

## 🧪 Testing Pages

### 1. **owner-test-registration.php** - Basic Test
- Form untuk input data registrasi manual
- Checklist untuk verifikasi semua fitur
- Database info dan links

### 2. **owner-test-error-badges.php** - Error Scenarios
Pre-configured test cases untuk 5 scenarios:
1. **Email Sudah Terdaftar** - menggunakan email `owner@parkir.com`
2. **Password Tidak Cocok** - password berbeda dengan confirm
3. **Password Terlalu Pendek** - password hanya 3 karakter
4. **Field Kosong** - field email kosong
5. **Registrasi Berhasil** - data valid dan email baru

Setiap scenario punya tombol test yang langsung membuka halaman registrasi dengan data terisi.

### 3. **owner-test-flow.php** - System Verification
Halaman verifikasi lengkap untuk:
- Database connection check
- Tables existence check
- Authentication functions check
- Current session status
- Code analysis (registration & login)

---

## 🚀 Cara Menggunakan

### Test Scenario 1: Error Badge (Email Duplicate)
1. Buka: `http://localhost:8080/owner-test-error-badges.php`
2. Klik tombol "Test: Email Sudah Terdaftar"
3. Halaman registrasi terbuka dengan email `owner@parkir.com` (sudah ada)
4. Klik "Daftar sebagai Owner"
5. ✅ **Result:** Badge merah muncul di atas kanan dengan pesan "Email sudah terdaftar"
6. User bisa klik X untuk tutup atau coba lagi

### Test Scenario 2: Success Badge
1. Buka: `http://localhost:8080/owner-test-error-badges.php`
2. Klik tombol "Test: Registrasi Berhasil"
3. Halaman registrasi terbuka dengan data valid + email baru
4. Klik "Daftar sebagai Owner"
5. ✅ **Result:**
   - Badge hijau muncul: "✓ Registrasi Berhasil!"
   - Countdown: "Mengarahkan ke login dalam 3 detik..."
   - Setelah 3 detik → Auto-redirect ke `/owner/login.php`
   - Data tersimpan di database

### Test Scenario 3: Manual Registration
1. Buka: `http://localhost:8080/owner-test-registration.php`
2. Isi form dengan data:
   - Nama Owner: "PT Parkir Baru"
   - Email: "parkir.baru@test.com" (email baru/belum ada)
   - Password: "MyPassword123"
   - Nomor Telepon: "08123456789"
   - Nama Parkir: "Parkir Unit Baru"
3. Klik "Kirim Registrasi"
4. Redirect ke register.php dan badge muncul

---

## 📊 Database Verification

### Check Registered Owners
```sql
SELECT id_pengguna, nama_pengguna, email_pengguna, role_pengguna 
FROM data_pengguna 
WHERE role_pengguna = 3 
ORDER BY id_pengguna DESC;
```

### Check Owner Parking Details
```sql
SELECT 
  op.id_owner_parkir,
  op.id_owner,
  dp.nama_pengguna,
  op.nama_parkir,
  op.status_parkir
FROM owner_parkir op
JOIN data_pengguna dp ON op.id_owner = dp.id_pengguna
ORDER BY op.id_owner_parkir DESC;
```

**phpMyAdmin Access:**
- URL: http://localhost:8081
- Username: root
- Password: rootpassword
- Database: spark

---

## 💡 Key Implementation Details

### Error Message Flow
```
User submits form with email already in database
    ↓
/functions/owner-register-proses.php checks email
    ↓
Email exists → $_SESSION['error'] = 'Email sudah terdaftar'
    ↓
header('Location: /owner/register.php')
    ↓
PHP checks if $_SESSION['error'] exists
    ↓
Renders notification-badge.error with message
    ↓
Animates in → User sees badge with X button
```

### Success Message Flow
```
User submits form with valid new data
    ↓
/functions/owner-register-proses.php processes
    ↓
Data inserted to database successfully
    ↓
$_SESSION['success'] = 'Registrasi berhasil!'
    ↓
header('Location: /owner/register.php')
    ↓
PHP renders notification-badge.success
    ↓
JavaScript countdown starts: 3 → 2 → 1 → 0
    ↓
After countdown, badge fades out
    ↓
Redirects to /owner/login.php
```

### Dismiss Button Implementation
```php
<button type="button" class="notification-close" 
  onclick="document.getElementById('notification-badge')?.remove()">
  ✕
</button>
```
User dapat menutup error badge dengan klik tombol X tanpa harus menunggu atau redirect.

---

## 🎨 CSS Classes Reference

| Class | Purpose |
|-------|---------|
| `.notification-badge` | Container utama badge |
| `.notification-badge.success` | Green gradient style |
| `.notification-badge.error` | Red gradient style with pulse animation |
| `.notification-content` | Flex container untuk content |
| `.notification-title` | Title text (bold) |
| `.notification-message` | Message text |
| `.notification-countdown` | Countdown timer text |
| `.notification-close` | Dismiss button (X) |
| `.notification-badge.fadeOut` | Fade out animation |

---

## ✅ Testing Checklist

- [x] Error badge muncul dengan animasi pulse
- [x] Error message spesifik ditampilkan
- [x] Dismiss button (X) berfungsi untuk close badge
- [x] Success badge muncul dengan countdown timer
- [x] Auto-redirect ke login setelah countdown
- [x] Data tersimpan di database
- [x] Responsive design (mobile friendly)
- [x] Smooth animations
- [x] Session management berfungsi
- [x] Password hashing dengan bcrypt
- [x] SQL injection protection (prepared statements)

---

## 🔗 Quick Links

| Link | Purpose |
|------|---------|
| `/owner/register.php` | Halaman registrasi dengan notification badge |
| `/owner/login.php` | Halaman login |
| `/owner-test-error-badges.php` | Error scenarios testing |
| `/owner-test-registration.php` | Manual test form |
| `/owner-test-flow.php` | System verification |
| `http://localhost:8081` | phpMyAdmin untuk database |

---

## 📝 Notes

1. **Session Management:** Badge menggunakan $_SESSION untuk persist messages across redirect
2. **Auto-unset:** $_SESSION['error'] dan ['success'] dihapus setelah ditampilkan
3. **No Page Reload:** Error badge bisa ditutup tanpa reload
4. **Mobile Friendly:** Responsive dengan max-width 380px, menyesuaikan dengan viewport
5. **Accessibility:** SVG icons untuk better performance dan scalability

---

## 🐛 Troubleshooting

### Badge tidak muncul?
- Pastikan Docker container `spark-app` sedang running
- Cek error di browser console
- Verify session_start() ada di awal file

### Data tidak tersimpan?
- Check database connection di `/config/database.php`
- Verify `data_pengguna` dan `owner_parkir` tables exist
- Check docker logs: `docker logs spark-app`

### Redirect tidak bekerja?
- Verify `BASEURL` di `/config/app.php` sudah benar
- Check browser console untuk JS errors
- Pastikan tidak ada output buffering

---

## 📞 Support

Untuk testing lebih lanjut atau issue, gunakan:
1. phpMyAdmin: http://localhost:8081
2. Test pages: `/owner-test-*.php`
3. Docker logs: `docker logs spark-app`
4. Browser console: Inspect element → Console tab
