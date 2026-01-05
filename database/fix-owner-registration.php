<?php
/**
 * FIX OWNER REGISTRATION
 * Script untuk memperbaiki masalah registrasi owner
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Fix Owner Registration</title>";
echo "<meta charset='utf-8'>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:800px;margin:0 auto;}";
echo ".success{color:green;padding:10px;background:#d4edda;border-left:4px solid #28a745;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border-left:4px solid #dc3545;margin:10px 0;}";
echo ".info{color:#004085;padding:10px;background:#d1ecf1;border-left:4px solid #17a2b8;margin:10px 0;}";
echo ".warning{color:#856404;padding:10px;background:#fff3cd;border-left:4px solid #ffc107;margin:10px 0;}";
echo "pre{background:#f8f9fa;padding:10px;border:1px solid #ddd;overflow-x:auto;}";
echo ".btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin:10px 5px 10px 0;}";
echo "</style>";
echo "</head><body>";
echo "<h1>🔧 Fix Owner Registration</h1>";

try {
    $pdo = getDBConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='info'>📋 Memulai perbaikan...</div>";
    
    // 1. Tambahkan role 'owner'
    echo "<h3>Step 1: Menambahkan role 'owner'...</h3>";
    try {
        // Cek dulu apakah sudah ada
        $stmt = $pdo->query("SELECT id_role FROM role_pengguna WHERE nama_role = 'owner'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='warning'>⚠️ Role 'owner' sudah ada, skip...</div>";
        } else {
            // Insert dengan ID spesifik
            $sql = "INSERT INTO `role_pengguna` (`id_role`, `nama_role`) VALUES (3, 'owner')";
            $pdo->exec($sql);
            echo "<div class='success'>✅ Role 'owner' berhasil ditambahkan dengan ID = 3</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error menambahkan role: " . $e->getMessage() . "</div>";
        echo "<div class='info'>Mencoba cara alternatif...</div>";
        try {
            $sql = "INSERT IGNORE INTO `role_pengguna` (`id_role`, `nama_role`) VALUES (3, 'owner')";
            $pdo->exec($sql);
            echo "<div class='success'>✅ Role ditambahkan dengan INSERT IGNORE</div>";
        } catch (PDOException $e2) {
            echo "<div class='error'>❌ Gagal: " . $e2->getMessage() . "</div>";
        }
    }
    
    // 2. Drop tabel owner_parkir jika ada (untuk fresh start)
    echo "<h3>Step 2: Membersihkan tabel lama...</h3>";
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("DROP TABLE IF EXISTS `owner_parkir`");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "<div class='success'>✅ Tabel lama dihapus (jika ada)</div>";
    } catch (PDOException $e) {
        echo "<div class='warning'>⚠️ " . $e->getMessage() . "</div>";
    }
    
    // 3. Buat tabel owner_parkir baru
    echo "<h3>Step 3: Membuat tabel owner_parkir...</h3>";
    try {
        $sql = "CREATE TABLE `owner_parkir` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $pdo->exec($sql);
        echo "<div class='success'>✅ Tabel owner_parkir berhasil dibuat</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error membuat tabel: " . $e->getMessage() . "</div>";
    }
    
    // 4. Verifikasi
    echo "<h3>Step 4: Verifikasi...</h3>";
    
    // Cek role
    $stmt = $pdo->query("SELECT * FROM role_pengguna WHERE nama_role = 'owner'");
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($role) {
        echo "<div class='success'>✅ Role 'owner' ditemukan dengan ID: " . $role['id_role'] . "</div>";
    } else {
        echo "<div class='error'>❌ Role 'owner' tidak ditemukan!</div>";
    }
    
    // Cek tabel
    $stmt = $pdo->query("SHOW TABLES LIKE 'owner_parkir'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Tabel owner_parkir ditemukan</div>";
        
        // Cek struktur tabel
        $stmt = $pdo->query("DESCRIBE owner_parkir");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>📊 Struktur tabel owner_parkir:<br><pre>";
        foreach ($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        echo "</pre></div>";
    } else {
        echo "<div class='error'>❌ Tabel owner_parkir tidak ditemukan!</div>";
    }
    
    // Test query
    echo "<h3>Step 5: Test Query...</h3>";
    try {
        $testQuery = "SELECT rp.id_role, rp.nama_role 
                      FROM role_pengguna rp 
                      WHERE rp.nama_role = 'owner'";
        $stmt = $pdo->query($testQuery);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo "<div class='success'>✅ Query test berhasil! Role owner ID = " . $result['id_role'] . "</div>";
        } else {
            echo "<div class='error'>❌ Role owner tidak ditemukan dalam query test</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error test query: " . $e->getMessage() . "</div>";
    }
    
    echo "<hr>";
    echo "<div class='success'><h2>🎉 Perbaikan Selesai!</h2>";
    echo "<p>Sekarang Anda bisa mencoba registrasi owner lagi.</p>";
    echo "<a class='btn' href='" . BASEURL . "/owner/register.php'>➡️ Coba Registrasi Owner</a>";
    echo "<a class='btn' href='check-owner-requirements.php'>🔍 Check Requirements</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'><h3>❌ Fatal Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</body></html>";
