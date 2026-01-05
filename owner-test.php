<?php
// Quick test file untuk verifikasi implementasi owner parkir
// Akses: http://localhost/spark/owner-test.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/owner-auth.php';

session_start();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Owner Parkir - System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #667eea;
        }
        .test-section h2 {
            margin-top: 0;
            color: #333;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #667eea;
            color: white;
        }
        .link-button {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .link-button:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🧪 SPARK Owner Parkir - System Verification</h1>

    <!-- 1. File Check -->
    <div class="test-section">
        <h2>✓ File Existence Check</h2>
        <?php
        $files = [
            '/owner/login.php' => 'Owner Login Page',
            '/owner/register.php' => 'Owner Register Page',
            '/owner/dashboard.php' => 'Owner Dashboard',
            '/owner/logout.php' => 'Owner Logout',
            '/functions/owner-auth.php' => 'Owner Auth Functions',
            '/functions/owner-login-proses.php' => 'Owner Login Process',
            '/functions/owner-register-proses.php' => 'Owner Register Process',
        ];

        $baseDir = __DIR__;
        $allFilesExist = true;

        echo '<table>';
        echo '<tr><th>File</th><th>Status</th></tr>';
        
        foreach ($files as $path => $name) {
            $fullPath = $baseDir . $path;
            $exists = file_exists($fullPath);
            $status = $exists ? '<span class="success">✓ OK</span>' : '<span class="error">✗ Missing</span>';
            echo '<tr><td>' . $name . '</td><td>' . $status . '</td></tr>';
            if (!$exists) $allFilesExist = false;
        }
        echo '</table>';

        echo $allFilesExist ? '<p class="success">✓ All files exist!</p>' : '<p class="error">✗ Some files are missing!</p>';
        ?>
    </div>

    <!-- 2. Database Check -->
    <div class="test-section">
        <h2>✓ Database Connection & Tables Check</h2>
        <?php
        try {
            $pdo = getDBConnection();
            echo '<p class="success">✓ Database connection successful</p>';

            // Check owner_parkir table
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'owner_parkir'
            ");
            $stmt->execute([DB_NAME]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                echo '<p class="success">✓ owner_parkir table exists</p>';
                
                // Check columns
                $stmt = $pdo->prepare("SHOW COLUMNS FROM owner_parkir");
                $stmt->execute();
                $columns = $stmt->fetchAll();
                echo '<p><strong>Columns:</strong> ' . count($columns) . '</p>';
            } else {
                echo '<p class="warning">⚠ owner_parkir table not found</p>';
                echo '<p>Please run: <a href="' . BASEURL . '/database/run-owner-setup.php" class="link-button">Setup Database</a></p>';
            }

            // Check owner role
            $stmt = $pdo->prepare("SELECT id_role, nama_role FROM role_pengguna WHERE nama_role = 'owner'");
            $stmt->execute();
            $ownerRole = $stmt->fetch();
            
            if ($ownerRole) {
                echo '<p class="success">✓ Owner role exists (ID: ' . $ownerRole['id_role'] . ')</p>';
            } else {
                echo '<p class="warning">⚠ Owner role not found</p>';
            }

            // List all roles
            $stmt = $pdo->query("SELECT id_role, nama_role FROM role_pengguna ORDER BY id_role");
            $roles = $stmt->fetchAll();
            echo '<p><strong>Available Roles:</strong></p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Role Name</th></tr>';
            foreach ($roles as $role) {
                echo '<tr><td>' . $role['id_role'] . '</td><td>' . htmlspecialchars($role['nama_role']) . '</td></tr>';
            }
            echo '</table>';

        } catch (PDOException $e) {
            echo '<p class="error">✗ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- 3. Session Check -->
    <div class="test-section">
        <h2>✓ Session & Auth Functions Check</h2>
        <?php
        $sessionStatus = session_status();
        echo '<p>' . (($sessionStatus === PHP_SESSION_ACTIVE) ? 
            '<span class="success">✓ Session is active</span>' : 
            '<span class="warning">⚠ Session not active</span>') . '</p>';

        echo '<p><strong>Auth Functions Available:</strong></p>';
        echo '<ul>';
        echo '<li>' . (function_exists('isOwnerLoggedIn') ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . ' isOwnerLoggedIn()</li>';
        echo '<li>' . (function_exists('getCurrentOwner') ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . ' getCurrentOwner()</li>';
        echo '<li>' . (function_exists('requireOwnerLogin') ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . ' requireOwnerLogin()</li>';
        echo '<li>' . (function_exists('logoutOwner') ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . ' logoutOwner()</li>';
        echo '</ul>';

        if (isOwnerLoggedIn()) {
            $owner = getCurrentOwner();
            echo '<p class="success">✓ Owner is logged in: ' . htmlspecialchars($owner['nama_pengguna']) . '</p>';
        } else {
            echo '<p class="info">ℹ No owner currently logged in</p>';
        }
        ?>
    </div>

    <!-- 4. Quick Links -->
    <div class="test-section">
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="<?= BASEURL ?>/owner/login.php" class="link-button">Owner Login</a>
            <a href="<?= BASEURL ?>/owner/register.php" class="link-button">Owner Register</a>
            <a href="<?= BASEURL ?>/owner/dashboard.php" class="link-button">Owner Dashboard</a>
            <a href="<?= BASEURL ?>/database/run-owner-setup.php" class="link-button">Database Setup</a>
        </p>
    </div>

    <!-- 5. Test Instructions -->
    <div class="test-section">
        <h2>📝 Test Flow</h2>
        <ol>
            <li><strong>Setup Database:</strong> Click "Database Setup" button above</li>
            <li><strong>Register:</strong> Go to Owner Register and create new account</li>
            <li><strong>Login:</strong> Go to Owner Login with registered credentials</li>
            <li><strong>Dashboard:</strong> Check Dashboard after successful login</li>
            <li><strong>Logout:</strong> Test logout functionality</li>
        </ol>
    </div>

    <!-- 6. System Info -->
    <div class="test-section">
        <h2>ℹ System Information</h2>
        <table>
            <tr><th>Property</th><th>Value</th></tr>
            <tr><td>PHP Version</td><td><?= phpversion() ?></td></tr>
            <tr><td>Database Host</td><td><?= DB_HOST ?></td></tr>
            <tr><td>Database Name</td><td><?= DB_NAME ?></td></tr>
            <tr><td>Base URL</td><td><?= BASEURL ?></td></tr>
            <tr><td>Server Time</td><td><?= date('Y-m-d H:i:s') ?></td></tr>
        </table>
    </div>

</div>

</body>
</html>
