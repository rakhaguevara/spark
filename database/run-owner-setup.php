<?php
// Script untuk menjalankan database migration
// Akses via: http://localhost/spark/database/setup.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Hanya bisa di-akses dari localhost untuk security
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== 'localhost') {
    die('Access denied');
}

$pdo = getDBConnection();

echo '<h2>SPARK Database Setup</h2>';
echo '<hr>';

try {
    // 1. Create owner_parkir table
    echo '<h3>1. Creating owner_parkir table...</h3>';
    
    $createTableSQL = "
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
    ";
    
    $pdo->exec($createTableSQL);
    echo '<p style="color: green;"><strong>✓ owner_parkir table created successfully</strong></p>';

    // 2. Insert owner role
    echo '<h3>2. Inserting owner role...</h3>';
    
    $checkRole = $pdo->prepare("SELECT id_role FROM role_pengguna WHERE nama_role = 'owner' LIMIT 1");
    $checkRole->execute();
    $roleExists = $checkRole->fetch();
    
    if (!$roleExists) {
        $insertRole = $pdo->prepare("INSERT INTO role_pengguna (nama_role) VALUES ('owner')");
        $insertRole->execute();
        echo '<p style="color: green;"><strong>✓ Owner role inserted successfully</strong></p>';
    } else {
        echo '<p style="color: blue;"><strong>✓ Owner role already exists</strong></p>';
    }

    // 3. Verify setup
    echo '<h3>3. Verifying setup...</h3>';
    
    $checkTable = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'owner_parkir'");
    $checkTable->execute([DB_NAME]);
    $tableExists = $checkTable->fetch();
    
    if ($tableExists['count'] > 0) {
        echo '<p style="color: green;"><strong>✓ owner_parkir table verified</strong></p>';
    }
    
    $checkRole = $pdo->prepare("SELECT id_role, nama_role FROM role_pengguna WHERE nama_role = 'owner'");
    $checkRole->execute();
    $role = $checkRole->fetch();
    
    if ($role) {
        echo '<p style="color: green;"><strong>✓ Owner role verified (ID: ' . $role['id_role'] . ')</strong></p>';
    }

    echo '<hr>';
    echo '<h3 style="color: green;">✓ Database setup completed successfully!</h3>';
    echo '<p><strong>Owner Role ID:</strong> ' . $role['id_role'] . '</p>';
    echo '<p><a href="' . BASEURL . '/owner/register.php">Go to Owner Registration →</a></p>';

} catch (PDOException $e) {
    echo '<h3 style="color: red;">✗ Setup failed</h3>';
    echo '<p style="color: red;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
}
