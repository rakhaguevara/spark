-- ========================================
-- OWNER PARKIR TABLE
-- For storing owner parking location data
-- ========================================

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

-- ========================================
-- INSERT OWNER ROLE (if not exists)
-- ========================================

INSERT INTO `role_pengguna` (`nama_role`) VALUES ('owner')
ON DUPLICATE KEY UPDATE `nama_role` = `nama_role`;
