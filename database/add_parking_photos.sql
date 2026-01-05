-- Create table untuk menyimpan multiple photos per parking location
CREATE TABLE IF NOT EXISTS `parking_photos` (
  `id_foto` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_tempat` INT NOT NULL,
  `foto_path` VARCHAR(255) NOT NULL,
  `urutan` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_tempat`) REFERENCES `tempat_parkir`(`id_tempat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add index untuk faster queries
ALTER TABLE `parking_photos` ADD INDEX `idx_id_tempat` (`id_tempat`);
