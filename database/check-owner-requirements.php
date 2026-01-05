<?php
/**
 * CHECK OWNER REGISTRATION REQUIREMENTS
 * Script untuk mengecek apakah semua requirement sudah terpenuhi
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Check Owner Registration</title>";
echo "<meta charset='utf-8'>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
h1{color:#333;}
.success{color:green;padding:10px;background:#d4edda;border-left:4px solid #28a745;margin:10px 0;}
.error{color:red;padding:10px;background:#f8d7da;border-left:4px solid #dc3545;margin:10px 0;}
.warning{color:#856404;padding:10px;background:#fff3cd;border-left:4px solid #ffc107;margin:10px 0;}
.info{color:#004085;padding:10px;background:#d1ecf1;border-left:4px solid #17a2b8;margin:10px 0;}
table{width:100%;border-collapse:collapse;margin:10px 0;background:white;}
th,td{padding:10px;text-align:left;border:1px solid #ddd;}
th{background:#007bff;color:white;}
pre{background:#f8f9fa;padding:10px;border:1px solid #ddd;overflow-x:auto;}
.btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin:10px 5px 10px 0;}
.btn:hover{background:#0056b3;}
</style>";
echo "</head><body>";
echo "<h1>🔍 Check Owner Registration Requirements</h1>";

try {
    $pdo = getDBConnection();
    $allGood = true;
    
    echo "<div class='info'>📋 Memeriksa requirement untuk registrasi owner...</div>";
    
    // 1. Cek role_pengguna table
    echo "<h2>1. Tabel role_pengguna</h2>";
    $stmt = $pdo->query("SELECT * FROM role_pengguna ORDER BY id_role");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($roles) > 0) {
        echo "<div class='success'>✅ Tabel role_pengguna ditemukan</div>";
        echo "<table><tr><th>ID Role</th><th>Nama Role</th></tr>";
        $ownerExists = false;
        foreach ($roles as $role) {
            echo "<tr><td>" . $role['id_role'] . "</td><td>" . $role['nama_role'] . "</td></tr>";
            if ($role['nama_role'] === 'owner') {
                $ownerExists = true;
            }
        }
        echo "</table>";
        
        if ($ownerExists) {
            echo "<div class='success'>✅ Role 'owner' sudah ada</div>";
        } else {
            echo "<div class='error'>❌ Role 'owner' BELUM ADA! Ini masalahnya!</div>";
            $allGood = false;
        }
    } else {
        echo "<div class='error'>❌ Tabel role_pengguna kosong!</div>";
        $allGood = false;
    }
    
    // 2. Cek owner_parkir table
    echo "<h2>2. Tabel owner_parkir</h2>";
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'owner_parkir'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Tabel owner_parkir ditemukan</div>";
            
            // Cek struktur
            $stmt = $pdo->query("DESCRIBE owner_parkir");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . $col['Field'] . "</td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . $col['Key'] . "</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ Tabel owner_parkir BELUM ADA! Ini masalahnya!</div>";
            $allGood = false;
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error saat cek tabel owner_parkir: " . $e->getMessage() . "</div>";
        $allGood = false;
    }
    
    // 3. Test insert simulation
    echo "<h2>3. Simulasi Insert</h2>";
    echo "<div class='info'>📝 Kode yang akan dijalankan saat registrasi:</div>";
    echo "<pre>";
    echo "// 1. Insert ke data_pengguna\n";
    echo "INSERT INTO data_pengguna (role_pengguna, nama_pengguna, email_pengguna, password_pengguna, noHp_pengguna)\n";
    echo "VALUES (3, 'Test Owner', 'test@owner.com', 'hashed_password', '081234567890')\n\n";
    echo "// 2. Insert ke owner_parkir\n";
    echo "INSERT INTO owner_parkir (id_owner, nama_parkir)\n";
    echo "VALUES (last_insert_id, 'Test Parkir')\n";
    echo "</pre>";
    
    // 4. Summary
    echo "<hr>";
    if ($allGood) {
        echo "<div class='success'><h2>🎉 Semua Requirement Terpenuhi!</h2>";
        echo "<p>Registrasi owner seharusnya bisa berjalan. Jika masih error, coba registrasi lagi dan lihat pesan error yang muncul.</p>";
        echo "</div>";
    } else {
        echo "<div class='error'><h2>❌ Ada Masalah yang Harus Diperbaiki!</h2>";
        echo "<p>Silakan jalankan script perbaikan di bawah ini:</p>";
        echo "<a class='btn' href='fix-owner-registration.php'>🔧 Jalankan Fix Script</a>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h2>🛠️ Actions</h2>";
    echo "<a class='btn' href='fix-owner-registration.php'>🔧 Run Fix Script</a>";
    echo "<a class='btn' href='" . BASEURL . "/owner/register.php'>📝 Coba Registrasi Owner</a>";
    echo "<a class='btn' href='" . BASEURL . "/owner/login.php'>🔐 Login Owner</a>";
    
} catch (PDOException $e) {
    echo "<div class='error'><h3>❌ Database Connection Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
}

echo "</body></html>";
