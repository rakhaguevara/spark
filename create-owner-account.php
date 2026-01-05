<?php
// Script untuk membuat test owner account
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    
    // Data test owner
    $nama = 'Owner Test SPARK';
    $email = 'owner@spark.test';
    $password = 'Owner123456';
    $no_hp = '081234567890';
    $nama_parkir = 'Parkir Test SPARK';
    
    // Check if owner role exists
    $stmt = $pdo->query("SELECT id_role FROM role_pengguna WHERE nama_role = 'owner' LIMIT 1");
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$role) {
        // Create owner role if doesn't exist
        $pdo->exec("INSERT INTO role_pengguna (nama_role) VALUES ('owner')");
        $role_id = 3; // Assuming owner is role 3
        echo "✅ Owner role created\n";
    } else {
        $role_id = $role['id_role'];
    }
    
    // Check if owner already exists
    $stmt = $pdo->prepare("SELECT id_pengguna FROM data_pengguna WHERE email_pengguna = ? LIMIT 1");
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "⚠️ Owner sudah ada dengan email: $email\n";
        echo "ID: " . $existing['id_pengguna'] . "\n";
    } else {
        // Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert owner
        $stmt = $pdo->prepare("
            INSERT INTO data_pengguna 
            (role_pengguna, nama_pengguna, email_pengguna, password_pengguna, noHp_pengguna)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$role_id, $nama, $email, $hashed, $no_hp])) {
            $owner_id = $pdo->lastInsertId();
            
            // Insert parking location
            $stmt = $pdo->prepare("
                INSERT INTO tempat_parkir 
                (id_pengguna, nama_tempat, alamat_tempat, jam_buka, jam_tutup, harga_jam, total_slot, status_tempat)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'aktif')
            ");
            
            $stmt->execute([
                $owner_id,
                'Parkir Test SPARK Pusat',
                'Jl. Merdeka No. 123, Jakarta',
                '07:00',
                '22:00',
                5000,
                50
            ]);
            
            echo "✅ Owner account created successfully!\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "Owner ID: $owner_id\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
            echo "Name: $nama\n";
            echo "Phone: $no_hp\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
