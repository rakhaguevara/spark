# INSTRUKSI MIGRASI DATABASE - SETTINGS MODULE

## Cara Menjalankan Migrasi

### Opsi 1: Via phpMyAdmin (RECOMMENDED)

1. Buka browser, akses: `http://localhost/phpmyadmin`
2. Klik database **`spark`** di sidebar kiri
3. Klik tab **"SQL"** di bagian atas
4. Copy SEMUA query di bawah ini dan paste ke SQL editor
5. Klik tombol **"Go"** atau **"Kirim"**

### SQL Query untuk di-copy:

```sql
-- STEP 1: Add notification_preferences column
ALTER TABLE `data_pengguna` 
ADD COLUMN `notification_preferences` JSON NULL DEFAULT NULL 
AFTER `profile_image`;

-- STEP 2: Add app_language column  
ALTER TABLE `data_pengguna`
ADD COLUMN `app_language` VARCHAR(10) NOT NULL DEFAULT 'id' 
AFTER `notification_preferences`;

-- STEP 3: Add app_theme column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_theme` VARCHAR(10) NOT NULL DEFAULT 'auto' 
AFTER `app_language`;

-- STEP 4: Add app_distance_unit column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_distance_unit` VARCHAR(10) NOT NULL DEFAULT 'km' 
AFTER `app_theme`;

-- STEP 5: Add app_auto_location column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_auto_location` TINYINT(1) NOT NULL DEFAULT 1 
AFTER `app_distance_unit`;

-- STEP 6: Add app_manual_location column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_manual_location` VARCHAR(255) NULL DEFAULT NULL 
AFTER `app_auto_location`;

-- STEP 7: Set default notification preferences for existing users
UPDATE `data_pengguna` 
SET `notification_preferences` = '{"email_notifications":true,"booking_reminders":true,"profile_updates":true,"password_changes":true}'
WHERE `notification_preferences` IS NULL;
```

### Opsi 2: Via MySQL Command Line

```bash
# Buka Command Prompt / PowerShell
cd C:\xampp\mysql\bin

# Login ke MySQL
.\mysql.exe -u root -p

# Pilih database spark
USE spark;

# Copy paste query di atas satu per satu
```

## Verifikasi Migrasi Berhasil

Jalankan query ini untuk memastikan kolom sudah ada:

```sql
DESCRIBE data_pengguna;
```

Anda harus melihat kolom-kolom baru:
- notification_preferences (JSON)
- app_language (VARCHAR)
- app_theme (VARCHAR)
- app_distance_unit (VARCHAR)
- app_auto_location (TINYINT)
- app_manual_location (VARCHAR)

## Troubleshooting

### Error: "Duplicate column name"
Artinya kolom sudah ada. Skip step tersebut dan lanjut ke step berikutnya.

### Error: "Unknown column 'profile_image'"
Jalankan dulu migrasi profile_image:
```sql
ALTER TABLE `data_pengguna` 
ADD COLUMN `profile_image` VARCHAR(255) NULL DEFAULT NULL 
AFTER `noHp_pengguna`;
```

## Setelah Migrasi Selesai

1. Refresh halaman Settings
2. Semua tab (Profile, Password, Notification, App Settings) seharusnya sudah berfungsi
3. Test setiap fitur untuk memastikan semuanya bekerja
