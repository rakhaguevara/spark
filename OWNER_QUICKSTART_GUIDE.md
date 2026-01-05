# 🚀 Owner Dashboard - Quick Start Guide

## Selamat Datang di SPARK Owner System!

Panduan cepat untuk mulai menggunakan dashboard pemilik lahan parkir.

---

## 📦 Apa yang Sudah Siap?

✅ Dashboard dengan statistik real-time  
✅ Kelola multiple lokasi parkir  
✅ Validasi tiket via QR scan  
✅ Monitoring slot parkir  
✅ Riwayat scan lengkap  
✅ Pengaturan akun  
✅ Responsive design (mobile-friendly)  

---

## 🔐 Login & Registrasi

### Registrasi Owner Baru
```
URL: http://localhost:8080/owner/register.php

1. Isi Nama Pemilik Parkir
2. Email yang valid
3. Password (minimal 6 karakter)
4. Nomor Telepon
5. Nama Parkir / Lokasi

✓ Success → Auto redirect ke login
✗ Error → Lihat badge merah dengan pesan error
```

### Login
```
URL: http://localhost:8080/owner/login.php

1. Masukkan Email
2. Masukkan Password
3. Click Login

✓ Success → Auto redirect ke dashboard
✗ Error → Lihat badge merah dengan pesan
```

---

## 🏠 Dashboard Overview

**Lokasi:** `/owner/dashboard.php`

Halaman utama menampilkan:

```
┌─────────────────────────────────────────┐
│  Sidebar         │  Main Content       │
│  - Dashboard ✓   │  ┌──────────────┐   │
│  - Kelola Lahan  │  │ Statistics   │   │
│  - Scan Tiket    │  ├──────────────┤   │
│  - Monitoring    │  │ Quick Links  │   │
│  - History       │  ├──────────────┤   │
│  - Pengaturan    │  │ Activity Log │   │
│                  │  └──────────────┘   │
└─────────────────────────────────────────┘
```

### Statistik yang Ditampilkan
- **Total Lahan Parkir:** Jumlah lokasi yang terdaftar
- **Lahan Aktif:** Lokasi yang sedang beroperasi
- **Total Penghasilan:** Dari semua booking completed
- **Total Booking:** Transaksi parkir

---

## 🏢 Kelola Lahan Parkir

**Lokasi:** `/owner/dashboard.php` → Click "Kelola Lahan"

### Tambah Lahan Parkir Baru

```
Form Input:
├─ Nama Lahan Parkir*
│  Contoh: "Parkir Mal Central"
│
├─ Alamat*
│  Contoh: "Jl. Sudirman No.123, Jakarta"
│
├─ Jam Buka*
│  Format: 08:00
│
├─ Jam Tutup*
│  Format: 22:00
│
├─ Harga per Jam (Rp)*
│  Contoh: 5000
│
└─ Total Slot*
   Contoh: 50
```

### Lihat Lahan Parkir

```
Setiap kartu menampilkan:
├─ Nama lahan
├─ Status (Aktif/Nonaktif)
├─ Alamat (truncated)
├─ Jam operasional
├─ Harga per jam
└─ Total slot
```

### Hapus Lahan Parkir

```
1. Klik tombol "Hapus" pada kartu
2. Konfirmasi penghapusan
3. Lahan akan dihapus dari database

⚠️ Tindakan tidak dapat dibatalkan!
```

---

## 📷 Scan Tiket Parkir

**Lokasi:** `/owner/dashboard.php` → Click "Scan Tiket"

### Step-by-Step Scanning

```
1. PILIH LOKASI PARKIR
   └─ Dropdown untuk memilih lahan mana yang di-scan
   
2. BUKA KAMERA
   └─ Klik tombol "Mulai Kamera"
   └─ Browser akan minta akses kamera
   └─ Allow untuk lanjut
   
3. POSISIKAN KAMERA
   └─ Arahkan ke QR code di tiket parkir
   └─ Letakkan dalam frame
   └─ Pastikan pencahayaan cukup
   
4. OTOMATIS SCAN
   └─ Sistem membaca QR secara real-time
   └─ Tidak perlu tombol "scan"
   
5. LIHAT HASIL
   ├─ ✓ Valid   → Status di-update
   ├─ ✗ Invalid → Lihat pesan error
   └─ Riwayat tersimpan otomatis
```

### QR Code Content (JSON)
```json
{
  "booking_id": 123,
  "qr_token": "abc123xyz...",
  "timestamp": 1704873600,
  "checksum": "hash..."
}
```

### Hasil Scan
- **Status:** Valid atau Invalid
- **ID Booking:** Nomor booking yang di-scan
- **Waktu:** Kapan scan dilakukan
- **Tipe:** CHECK-IN atau CHECK-OUT

### Troubleshooting

```
❌ "Tidak dapat mengakses kamera"
→ Beri akses kamera di browser settings
→ Gunakan HTTPS (bukan HTTP)
→ Periksa permissions di OS

❌ "QR Code tidak valid"
→ QR mungkin rusak atau palsu
→ Coba scan ulang
→ Hubungi customer service

❌ "Tiket sudah selesai"
→ Tiket sudah di-checkout sebelumnya
→ Tidak bisa scan 2x untuk checkout
```

---

## 📊 Monitoring Real-Time

**Lokasi:** `/owner/dashboard.php` → Click "Monitoring"

### Informasi per Lokasi Parkir

```
Setiap kartu menampilkan:
├─ Nama lokasi
├─ Status operasional
├─ Progress bar slot terisi
│  ├─ Slot terisi: 15/50
│  └─ Percentage: 30%
├─ Slot tersedia: 35
├─ Jam operasional: 08:00 - 22:00
└─ Tarif: Rp 5.000/jam
```

### Refresh Otomatis
- Halaman refresh setiap **5 detik**
- Data selalu up-to-date
- Gunakan di perangkat tablet saat shift

### Interpretasi Progress Bar
```
████░░░░░░ 40% Sangat ramai
████████░░ 80% Penuh
██████████ 100% FULL - tutup pintu masuk
██░░░░░░░░ 20% Sepi
```

---

## 📋 Riwayat Pemindaian

**Lokasi:** `/owner/dashboard.php` → Click "History"

### Tabel Riwayat
```
Kolom Tabel:
├─ Waktu Scan (Tanggal & Jam)
├─ Lokasi Parkir (Nama lahan)
├─ Tipe Scan (📥 Masuk / 📤 Keluar)
├─ Booking ID
└─ Status (✓ Valid / ✗ Invalid)
```

### Filter & Sorting
- Sorting otomatis by **waktu (newest first)**
- Pagination: 20 item per halaman
- Click page number untuk navigasi

### Export Data (Future)
```
Fitur akan datang:
- Export ke CSV
- Export ke PDF
- Filter by tanggal range
- Filter by lokasi
```

---

## ⚙️ Pengaturan Akun

**Lokasi:** `/owner/dashboard.php` → Click "Pengaturan"

### Edit Profil

```
Field yang bisa diedit:
├─ Nama Lengkap
├─ Email
├─ Nomor Telepon
└─ Update button

⚠️ Email baru harus unik (tidak duplikat)
```

### Update Password

```
1. Klik field "Password Baru"
2. Masukkan password minimal 6 karakter
3. Click "Update Password"
4. Password langsung berubah (re-login jika perlu)

Tips password yang aman:
✓ Minimal 8 karakter
✓ Mix huruf BESAR + kecil
✓ Tambah angka + simbol
✗ Jangan gunakan nama/tanggal lahir
```

### Logout

```
1. Pergi ke pengaturan
2. Click "Logout Sekarang"
3. Session berakhir
4. Redirect ke login page
```

---

## 🔍 UI Navigation

### Sidebar Menu (Always Visible)

```
🏠 Dashboard
   └─ Halaman utama dengan stats

🏢 Kelola Lahan
   └─ CRUD parking locations

📷 Scan Tiket
   └─ Validasi via QR code

📊 Monitoring
   └─ Real-time slot status

📋 History
   └─ Riwayat semua scans

⚙️ Pengaturan
   └─ Edit profil & security

🚪 Logout
   └─ Keluar dari sistem
```

### Responsive Design

```
DESKTOP (≥1024px)
├─ Sidebar: 260px fixed
├─ Content: Full width
├─ Grid: Multi-column

TABLET (768px - 1023px)
├─ Sidebar: 260px fixed
├─ Content: Adjusted
├─ Grid: 2-column

MOBILE (<768px)
├─ Sidebar: 70px (icons only)
├─ Content: Full width
├─ Grid: 1-column
├─ Buttons: Full width
└─ Touch-friendly size
```

---

## 📱 Mobile Tips

1. **Use in Landscape Mode** untuk monitoring
2. **Tablet Recommended** untuk QR scanning
3. **Auto-refresh** di monitoring page sangat membantu
4. **Sidebar Collapse** otomatis di mobile

---

## 🎯 Workflow Harian

### Morning (Pagi)
```
1. Login ke dashboard
2. Cek statistik hari ini
3. Go to Monitoring
4. Verifikasi semua lokasi siap operasi
```

### During Operation (Saat Operasional)
```
1. Keep monitoring page open (auto-refresh)
2. Saat customer datang:
   - Go to Scan Tiket
   - Select lokasi parkir
   - Scan QR code tiket
3. Update status checking
```

### Evening (Malam)
```
1. Buka History
2. Review semua scan hari ini
3. Hitung total earning
4. Cek masalah (invalid scans)
5. Logout
```

---

## ❓ FAQ

### Q: Saya lupa password, bagaimana?
**A:** Feature "lupa password" akan ditambahkan. Hubungi admin untuk reset.

### Q: Bisa manage multiple lokasi?
**A:** Bisa! Daftar multiple locations di "Kelola Lahan", pilih lokasi saat scan.

### Q: Bagaimana jika customer tidak punya QR?
**A:** Owner bisa scan ticket di scanner milik customer di pintu masuk.

### Q: Data scan history disimpan berapa lama?
**A:** Selamanya (unlimited), bisa diakses kapan saja.

### Q: Bisa edit informasi lahan setelah dibuat?
**A:** Ya, fitur edit ada di rencana. Saat ini bisa hapus & buat ulang.

### Q: Apakah data aman?
**A:** Ya! Password di-hash, QR di-validate, ownership di-verify.

---

## 📞 Support Contacts

```
Email: support@spark-parking.local
WhatsApp: +62 812-3456-7890
Live Chat: Available 9AM - 5PM
```

---

## 🎉 You're All Set!

Sekarang Anda siap menggunakan Owner Dashboard SPARK!

**Quick Links:**
- 🏠 [Dashboard](http://localhost:8080/owner/dashboard.php)
- 🏢 [Kelola Lahan](http://localhost:8080/owner/manage-parking.php)
- 📷 [Scan Tiket](http://localhost:8080/owner/scan-ticket.php)
- 📊 [Monitoring](http://localhost:8080/owner/monitoring.php)
- 📋 [History](http://localhost:8080/owner/scan-history.php)
- ⚙️ [Settings](http://localhost:8080/owner/settings.php)

**Happy Parking Management! 🚗**
