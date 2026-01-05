-- ========================================
-- SPARK COMPLETE DATABASE INITIALIZATION
-- Auto-run on Docker first start
-- ========================================

-- Set charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- 1. CREATE ALL TABLES
-- ========================================

-- Role Pengguna Table
CREATE TABLE IF NOT EXISTS `role_pengguna` (
  `id_role` int(11) NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(255) NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nama_role` (`nama_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Roles
INSERT INTO `role_pengguna` (`id_role`, `nama_role`) VALUES 
(1, 'user'),
(2, 'admin'),
(3, 'owner')
ON DUPLICATE KEY UPDATE `nama_role` = VALUES(`nama_role`);

-- Data Pengguna Table
CREATE TABLE IF NOT EXISTS `data_pengguna` (
  `id_pengguna` int(11) NOT NULL AUTO_INCREMENT,
  `role_pengguna` int(11) NOT NULL,
  `nama_pengguna` varchar(255) NOT NULL,
  `email_pengguna` varchar(255) NOT NULL,
  `password_pengguna` varchar(255) NOT NULL,
  `noHp_pengguna` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
  `app_language` varchar(10) NOT NULL DEFAULT 'id',
  `app_theme` varchar(10) NOT NULL DEFAULT 'auto',
  `app_distance_unit` varchar(10) NOT NULL DEFAULT 'km',
  `app_auto_location` tinyint(1) NOT NULL DEFAULT 1,
  `app_manual_location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pengguna`),
  UNIQUE KEY `email_pengguna` (`email_pengguna`),
  KEY `role_pengguna` (`role_pengguna`),
  CONSTRAINT `data_pengguna_ibfk_1` FOREIGN KEY (`role_pengguna`) REFERENCES `role_pengguna` (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Owner Parkir Table (IMPORTANT!)
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

-- Jenis Kendaraan Table
CREATE TABLE IF NOT EXISTS `jenis_kendaraan` (
  `id_jenis` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(50) NOT NULL,
  PRIMARY KEY (`id_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `jenis_kendaraan` (`id_jenis`, `nama_jenis`) VALUES 
(1, 'Motor'),
(2, 'Mobil')
ON DUPLICATE KEY UPDATE `nama_jenis` = VALUES(`nama_jenis`);

-- Tempat Parkir Table
CREATE TABLE IF NOT EXISTS `tempat_parkir` (
  `id_tempat` int(11) NOT NULL AUTO_INCREMENT,
  `id_pemilik` int(11) NOT NULL,
  `nama_tempat` varchar(255) NOT NULL,
  `alamat_tempat` text NOT NULL,
  `latitude` decimal(10, 8) NOT NULL,
  `longitude` decimal(11, 8) NOT NULL,
  `total_spot` int(11) NOT NULL,
  `harga_per_jam` decimal(10,2) NOT NULL,
  `jam_buka` time NOT NULL,
  `jam_tutup` time NOT NULL,
  `is_plat_required` tinyint(1) NOT NULL DEFAULT 0,
  `foto_tempat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tempat`),
  KEY `id_pemilik` (`id_pemilik`),
  CONSTRAINT `tempat_parkir_ibfk_1` FOREIGN KEY (`id_pemilik`) REFERENCES `data_pengguna` (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Slot Parkir Table
CREATE TABLE IF NOT EXISTS `slot_parkir` (
  `id_slot` int(11) NOT NULL AUTO_INCREMENT,
  `id_tempat` int(11) NOT NULL,
  `nomor_slot` varchar(20) NOT NULL,
  `status_slot` enum('available','booked','maintenance') NOT NULL DEFAULT 'available',
  `id_jenis` int(11) NOT NULL,
  PRIMARY KEY (`id_slot`),
  KEY `id_tempat` (`id_tempat`),
  KEY `id_jenis` (`id_jenis`),
  CONSTRAINT `slot_parkir_ibfk_1` FOREIGN KEY (`id_tempat`) REFERENCES `tempat_parkir` (`id_tempat`),
  CONSTRAINT `slot_parkir_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_kendaraan` (`id_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Harga Parkir Table
CREATE TABLE IF NOT EXISTS `harga_parkir` (
  `id_harga` int(11) NOT NULL AUTO_INCREMENT,
  `id_tempat` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `harga_per_jam` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_harga`),
  KEY `id_tempat` (`id_tempat`),
  KEY `id_jenis` (`id_jenis`),
  CONSTRAINT `harga_parkir_ibfk_1` FOREIGN KEY (`id_tempat`) REFERENCES `tempat_parkir` (`id_tempat`),
  CONSTRAINT `harga_parkir_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_kendaraan` (`id_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Kendaraan Pengguna Table
CREATE TABLE IF NOT EXISTS `kendaraan_pengguna` (
  `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `plat_hash` char(64) NOT NULL,
  `plat_hint` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_kendaraan`),
  KEY `id_pengguna` (`id_pengguna`),
  KEY `id_jenis` (`id_jenis`),
  CONSTRAINT `kendaraan_pengguna_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `data_pengguna` (`id_pengguna`),
  CONSTRAINT `kendaraan_pengguna_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_kendaraan` (`id_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Booking Parkir Table
CREATE TABLE IF NOT EXISTS `booking_parkir` (
  `id_booking` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` int(11) NOT NULL,
  `id_tempat` int(11) NOT NULL,
  `id_slot` int(11) NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status_booking` enum('pending','confirmed','cancelled','completed','ongoing') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_kendaraan` int(11) NOT NULL,
  `qr_secret` char(64) NOT NULL,
  PRIMARY KEY (`id_booking`),
  KEY `id_pengguna` (`id_pengguna`),
  KEY `id_tempat` (`id_tempat`),
  KEY `id_slot` (`id_slot`),
  KEY `id_kendaraan` (`id_kendaraan`),
  CONSTRAINT `booking_parkir_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `data_pengguna` (`id_pengguna`),
  CONSTRAINT `booking_parkir_ibfk_2` FOREIGN KEY (`id_tempat`) REFERENCES `tempat_parkir` (`id_tempat`),
  CONSTRAINT `booking_parkir_ibfk_3` FOREIGN KEY (`id_slot`) REFERENCES `slot_parkir` (`id_slot`),
  CONSTRAINT `booking_parkir_ibfk_4` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan_pengguna` (`id_kendaraan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- QR Session Table
CREATE TABLE IF NOT EXISTS `qr_session` (
  `id_qr` int(11) NOT NULL AUTO_INCREMENT,
  `id_booking` int(11) NOT NULL,
  `qr_token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_qr`),
  KEY `id_booking` (`id_booking`),
  CONSTRAINT `qr_session_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_parkir` (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Scan History Table
CREATE TABLE IF NOT EXISTS `scan_history` (
  `id_scan` int(11) NOT NULL AUTO_INCREMENT,
  `id_booking` int(11) NOT NULL,
  `scan_type` enum('entry','exit','stay_confirm') NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scanned_by` int(11) DEFAULT NULL COMMENT 'ID of owner/staff who performed scan',
  `location_lat` decimal(10, 8) DEFAULT NULL,
  `location_lng` decimal(11, 8) DEFAULT NULL,
  PRIMARY KEY (`id_scan`),
  KEY `id_booking` (`id_booking`),
  CONSTRAINT `scan_history_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_parkir` (`id_booking`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Contacts Table
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Notifikasi Pengguna Table
CREATE TABLE IF NOT EXISTS `notifikasi_pengguna` (
  `id_notif` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notif`),
  KEY `id_pengguna` (`id_pengguna`),
  CONSTRAINT `notifikasi_pengguna_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `data_pengguna` (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pembayaran Booking Table
CREATE TABLE IF NOT EXISTS `pembayaran_booking` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_booking` int(11) NOT NULL,
  `metode` varchar(50) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `transaksi_id` varchar(255) NOT NULL,
  `status` enum('pending','success','failed') NOT NULL,
  `waktu_bayar` datetime NOT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `id_booking` (`id_booking`),
  CONSTRAINT `pembayaran_booking_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_parkir` (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- 2. INSERT DEFAULT ADMIN USER
-- ========================================

-- Default admin account (password: admin123)
INSERT INTO `data_pengguna` (`id_pengguna`, `role_pengguna`, `nama_pengguna`, `email_pengguna`, `password_pengguna`, `noHp_pengguna`) 
VALUES (1, 2, 'Admin SPARK', 'admin@spark.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890')
ON DUPLICATE KEY UPDATE `nama_pengguna` = VALUES(`nama_pengguna`);

-- ========================================
-- 3. ENABLE FOREIGN KEY CHECKS
-- ========================================

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- INITIALIZATION COMPLETE
-- ========================================

SELECT 'Database initialization completed successfully!' AS status;
