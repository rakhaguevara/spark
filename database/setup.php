<?php

/**
 * Database Setup Script
 * Run this script after cloning the project to ensure database schema is complete
 * 
 * Usage: php database/setup.php
 * Or visit: http://localhost/spark/database/setup.php
 */

require_once __DIR__ . '/../config/database.php';

// Output as HTML
$isCLI = php_sapi_name() === 'cli';
if (!$isCLI) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Database Setup - SPARK</title>
        <style>
            body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
            .success { color: #10b981; }
            .error { color: #ef4444; }
            .info { color: #3b82f6; }
            .warning { color: #f59e0b; }
            pre { background: #2a2a2a; padding: 10px; border-radius: 5px; }
        </style>
    </head>
    <body>
    <h1>🔧 SPARK Database Setup</h1>
    <pre>';
}

function logMessage($message, $type = 'info')
{
    global $isCLI;
    $colors = [
        'success' => $isCLI ? "\033[32m" : '<span class="success">',
        'error'   => $isCLI ? "\033[31m" : '<span class="error">',
        'info'    => $isCLI ? "\033[34m" : '<span class="info">',
        'warning' => $isCLI ? "\033[33m" : '<span class="warning">',
    ];
    $reset = $isCLI ? "\033[0m" : '</span>';

    echo $colors[$type] . $message . $reset . "\n";
    if (!$isCLI) flush();
}

try {
    logMessage("=== Starting Database Setup ===", 'info');

    $pdo = getDBConnection();
    logMessage("✓ Database connection successful", 'success');

    // Check and add foto_tempat column to tempat_parkir if missing
    logMessage("\n[1] Checking tempat_parkir table...", 'info');
    $stmt = $pdo->query("SHOW COLUMNS FROM tempat_parkir LIKE 'foto_tempat'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Adding foto_tempat column...", 'warning');
        $pdo->exec("ALTER TABLE tempat_parkir ADD COLUMN foto_tempat VARCHAR(255) DEFAULT NULL AFTER is_plat_required");
        logMessage("  ✓ foto_tempat column added", 'success');
    } else {
        logMessage("  ✓ foto_tempat column exists", 'success');
    }

    // Check and add is_plat_required column if missing
    $stmt = $pdo->query("SHOW COLUMNS FROM tempat_parkir LIKE 'is_plat_required'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Adding is_plat_required column...", 'warning');
        $pdo->exec("ALTER TABLE tempat_parkir ADD COLUMN is_plat_required TINYINT(1) DEFAULT 0 AFTER created_at");
        logMessage("  ✓ is_plat_required column added", 'success');
    } else {
        logMessage("  ✓ is_plat_required column exists", 'success');
    }

    // Check kendaraan_pengguna table for plat_hash and plat_hint
    logMessage("\n[2] Checking kendaraan_pengguna table...", 'info');
    $stmt = $pdo->query("SHOW COLUMNS FROM kendaraan_pengguna LIKE 'plat_hash'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Adding plat_hash column...", 'warning');
        $pdo->exec("ALTER TABLE kendaraan_pengguna ADD COLUMN plat_hash CHAR(64) NOT NULL AFTER nomor_plat");
        logMessage("  ✓ plat_hash column added", 'success');
    } else {
        logMessage("  ✓ plat_hash column exists", 'success');
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM kendaraan_pengguna LIKE 'plat_hint'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Adding plat_hint column...", 'warning');
        $pdo->exec("ALTER TABLE kendaraan_pengguna ADD COLUMN plat_hint VARCHAR(10) DEFAULT NULL AFTER plat_hash");
        logMessage("  ✓ plat_hint column added", 'success');
    } else {
        logMessage("  ✓ plat_hint column exists", 'success');
    }

    // Check booking_parkir for qr_secret
    logMessage("\n[3] Checking booking_parkir table...", 'info');
    $stmt = $pdo->query("SHOW COLUMNS FROM booking_parkir LIKE 'qr_secret'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Adding qr_secret column...", 'warning');
        $pdo->exec("ALTER TABLE booking_parkir ADD COLUMN qr_secret CHAR(64) NOT NULL AFTER id_kendaraan");
        logMessage("  ✓ qr_secret column added", 'success');
    } else {
        logMessage("  ✓ qr_secret column exists", 'success');
    }

    // Check if qr_session table exists
    logMessage("\n[4] Checking qr_session table...", 'info');
    $stmt = $pdo->query("SHOW TABLES LIKE 'qr_session'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Creating qr_session table...", 'warning');
        $sql = file_get_contents(__DIR__ . '/qr_sessions.sql');
        $pdo->exec($sql);
        logMessage("  ✓ qr_session table created", 'success');
    } else {
        logMessage("  ✓ qr_session table exists", 'success');
    }

    // Check if tickets table exists
    logMessage("\n[5] Checking tickets table...", 'info');
    $stmt = $pdo->query("SHOW TABLES LIKE 'tickets'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Creating tickets table...", 'warning');
        $sql = file_get_contents(__DIR__ . '/tickets.sql');
        $pdo->exec($sql);
        logMessage("  ✓ tickets table created", 'success');
    } else {
        logMessage("  ✓ tickets table exists", 'success');
    }

    // Check data_pengguna for profile_image
    logMessage("\n[6] Checking data_pengguna table...", 'info');
    $stmt = $pdo->query("SHOW COLUMNS FROM data_pengguna LIKE 'profile_image'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Adding profile_image column...", 'warning');
        $pdo->exec("ALTER TABLE data_pengguna ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER password_pengguna");
        logMessage("  ✓ profile_image column added", 'success');
    } else {
        logMessage("  ✓ profile_image column exists", 'success');
    }

    // Check user_preferences table
    logMessage("\n[7] Checking user_preferences table...", 'info');
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_preferences'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Creating user_preferences table...", 'warning');
        $sql = file_get_contents(__DIR__ . '/add_user_preferences.sql');
        $pdo->exec($sql);
        logMessage("  ✓ user_preferences table created", 'success');
    } else {
        logMessage("  ✓ user_preferences table exists", 'success');
    }

    // Check wallet_payment_methods table
    logMessage("\n[8] Checking wallet_payment_methods table...", 'info');
    $stmt = $pdo->query("SHOW TABLES LIKE 'wallet_payment_methods'");
    if ($stmt->rowCount() === 0) {
        logMessage("  → Creating wallet_payment_methods table...", 'warning');
        $sql = file_get_contents(__DIR__ . '/wallet_methods.sql');
        $pdo->exec($sql);
        logMessage("  ✓ wallet_payment_methods table created", 'success');
    } else {
        logMessage("  ✓ wallet_payment_methods table exists", 'success');
    }

    logMessage("\n=== Database Setup Complete ===", 'success');
    logMessage("All tables and columns are ready!", 'success');
} catch (PDOException $e) {
    logMessage("\n=== Setup Failed ===", 'error');
    logMessage("Error: " . $e->getMessage(), 'error');
    logMessage("\nPlease check your database connection settings in config/database.php", 'warning');
}

if (!$isCLI) {
    echo '</pre>
    <p><a href="../" style="color: #FFE100;">← Back to Home</a></p>
    </body>
    </html>';
}
