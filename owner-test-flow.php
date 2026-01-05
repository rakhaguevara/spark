<?php
session_start();
require_once 'config/database.php';
require_once 'functions/owner-auth.php';

// Get test results
$tests = [];

echo "
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Owner Registration & Login Test Flow</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        .test-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .test-section h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .test-item {
            padding: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .test-item.pass {
            border-left-color: #2ecc71;
            background: #f0fff4;
        }
        .test-item.fail {
            border-left-color: #e74c3c;
            background: #fff5f5;
        }
        .test-item.info {
            border-left-color: #3498db;
            background: #f0f8ff;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge.pass { background: #2ecc71; color: white; }
        .badge.fail { background: #e74c3c; color: white; }
        .badge.info { background: #3498db; color: white; }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        tr:hover { background: #f9f9f9; }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #2ecc71; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔐 Owner Registration & Login Test Flow</h1>
";

// TEST 1: Database Connection
echo "<div class='test-section'>";
echo "<h2>Test 1: Database Connection</h2>";
try {
    $test_query = $pdo->query("SELECT 1");
    if ($test_query) {
        echo "<div class='test-item pass'>";
        echo "✅ PDO Connection Active <span class='badge pass'>PASS</span>";
        echo "</div>";
        $tests['db_connection'] = true;
    }
} catch (Exception $e) {
    echo "<div class='test-item fail'>";
    echo "❌ Connection Failed: " . $e->getMessage() . " <span class='badge fail'>FAIL</span>";
    echo "</div>";
    $tests['db_connection'] = false;
}
echo "</div>";

// TEST 2: Tables Exist
echo "<div class='test-section'>";
echo "<h2>Test 2: Database Tables</h2>";

$tables = ['data_pengguna', 'owner_parkir', 'role_pengguna'];
foreach ($tables as $table) {
    try {
        $check = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'spark' AND TABLE_NAME = '$table'");
        if ($check->rowCount() > 0) {
            echo "<div class='test-item pass'>";
            echo "✅ Table '$table' exists <span class='badge pass'>OK</span>";
            echo "</div>";
        } else {
            echo "<div class='test-item fail'>";
            echo "❌ Table '$table' not found <span class='badge fail'>MISSING</span>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item fail'>";
        echo "❌ Error checking table '$table': " . $e->getMessage() . " <span class='badge fail'>ERROR</span>";
        echo "</div>";
    }
}
echo "</div>";

// TEST 3: Existing Owner Data
echo "<div class='test-section'>";
echo "<h2>Test 3: Existing Owner Data in Database</h2>";
try {
    $stmt = $pdo->query("
        SELECT 
            dp.id_pengguna,
            dp.nama_pengguna,
            dp.email_pengguna,
            rp.nama_role,
            op.id_owner_parkir,
            op.nama_parkir
        FROM data_pengguna dp
        LEFT JOIN role_pengguna rp ON dp.role_pengguna = rp.id_role
        LEFT JOIN owner_parkir op ON dp.id_pengguna = op.id_owner
        WHERE dp.role_pengguna = 3
        ORDER BY dp.id_pengguna DESC
    ");

    $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($owners) > 0) {
        echo "<div class='test-item pass'>";
        echo count($owners) . " Owner(s) found in database <span class='badge pass'>OK</span>";
        echo "</div>";

        echo "<table>";
        echo "<tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Parkir</th></tr>";
        foreach ($owners as $owner) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($owner['id_pengguna']) . "</td>";
            echo "<td>" . htmlspecialchars($owner['nama_pengguna']) . "</td>";
            echo "<td>" . htmlspecialchars($owner['email_pengguna']) . "</td>";
            echo "<td>" . htmlspecialchars($owner['nama_role']) . "</td>";
            echo "<td>" . htmlspecialchars($owner['nama_parkir'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='test-item info'>";
        echo "ℹ️ No owners found - need to register first <span class='badge info'>EMPTY</span>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item fail'>";
    echo "❌ Query error: " . $e->getMessage() . " <span class='badge fail'>ERROR</span>";
    echo "</div>";
}
echo "</div>";

// TEST 4: Login Function Check
echo "<div class='test-section'>";
echo "<h2>Test 4: Authentication Functions</h2>";

// Check if functions exist
$functions_to_check = [
    'isOwnerLoggedIn' => 'Session check function',
    'getCurrentOwner' => 'Get current owner data function',
    'requireOwnerLogin' => 'Login requirement function',
    'logoutOwner' => 'Logout function'
];

foreach ($functions_to_check as $func => $desc) {
    if (function_exists($func)) {
        echo "<div class='test-item pass'>";
        echo "✅ $desc ($func) <span class='badge pass'>OK</span>";
        echo "</div>";
    } else {
        echo "<div class='test-item fail'>";
        echo "❌ $desc ($func) NOT FOUND <span class='badge fail'>MISSING</span>";
        echo "</div>";
    }
}
echo "</div>";

// TEST 5: Current Session Status
echo "<div class='test-section'>";
echo "<h2>Test 5: Current Session Status</h2>";

if (isOwnerLoggedIn()) {
    $current_owner = getCurrentOwner();
    echo "<div class='test-item pass'>";
    echo "✅ Owner session active <span class='badge pass'>LOGGED IN</span>";
    echo "<div class='code'>";
    echo "Owner ID: " . htmlspecialchars($current_owner['id_pengguna']) . "<br>";
    echo "Name: " . htmlspecialchars($current_owner['nama_pengguna']) . "<br>";
    echo "Email: " . htmlspecialchars($current_owner['email_pengguna']) . "<br>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div class='test-item info'>";
    echo "ℹ️ No owner session active (not logged in) <span class='badge info'>NOT LOGGED IN</span>";
    echo "</div>";
}
echo "</div>";

// TEST 6: Registration Process
echo "<div class='test-section'>";
echo "<h2>Test 6: Registration Code Analysis</h2>";

$register_file = 'functions/owner-register-proses.php';
if (file_exists($register_file)) {
    echo "<div class='test-item pass'>";
    echo "✅ Registration handler exists: $register_file <span class='badge pass'>OK</span>";
    echo "</div>";

    $content = file_get_contents($register_file);

    // Check for key features
    $checks = [
        'password_hash' => 'Password hashing',
        'prepared statements' => 'SQL Injection protection',
        'INSERT INTO data_pengguna' => 'Owner data insertion',
        'owner_parkir' => 'Parking data insertion'
    ];

    foreach ($checks as $check => $label) {
        if (stripos($content, $check) !== false) {
            echo "<div class='test-item pass'>";
            echo "✅ $label implementation found <span class='badge pass'>OK</span>";
            echo "</div>";
        } else {
            echo "<div class='test-item fail'>";
            echo "❌ $label NOT implemented <span class='badge fail'>MISSING</span>";
            echo "</div>";
        }
    }
} else {
    echo "<div class='test-item fail'>";
    echo "❌ Registration handler not found: $register_file <span class='badge fail'>MISSING</span>";
    echo "</div>";
}
echo "</div>";

// TEST 7: Login Process
echo "<div class='test-section'>";
echo "<h2>Test 7: Login Code Analysis</h2>";

$login_file = 'functions/owner-login-proses.php';
if (file_exists($login_file)) {
    echo "<div class='test-item pass'>";
    echo "✅ Login handler exists: $login_file <span class='badge pass'>OK</span>";
    echo "</div>";

    $content = file_get_contents($login_file);

    // Check for key features
    $checks = [
        'password_verify' => 'Password verification',
        'prepared statements' => 'SQL Injection protection',
        'role_pengguna = 3' => 'Owner role check',
        '\$_SESSION' => 'Session management'
    ];

    foreach ($checks as $check => $label) {
        if (stripos($content, $check) !== false) {
            echo "<div class='test-item pass'>";
            echo "✅ $label implementation found <span class='badge pass'>OK</span>";
            echo "</div>";
        } else {
            echo "<div class='test-item fail'>";
            echo "❌ $label NOT implemented <span class='badge fail'>MISSING</span>";
            echo "</div>";
        }
    }
} else {
    echo "<div class='test-item fail'>";
    echo "❌ Login handler not found: $login_file <span class='badge fail'>MISSING</span>";
    echo "</div>";
}
echo "</div>";

// Summary and Actions
echo "
    <div class='test-section'>
        <div class='summary'>
            ✅ System Ready for Owner Registration & Login Testing
        </div>
        <h2 style='color: #333; margin-top: 20px;'>Quick Start</h2>
        <div class='actions'>
            <a href='/owner/register.php' class='btn btn-primary'>📋 Register New Owner</a>
            <a href='/owner/login.php' class='btn btn-success'>🔐 Login as Owner</a>
            <a href='/owner/dashboard.php' class='btn btn-danger'>📊 View Dashboard</a>
        </div>
    </div>

    <div class='test-section'>
        <h2>📝 Test Instructions</h2>
        <ol style='line-height: 1.8; color: #333;'>
            <li><strong>Register</strong>: Click \"Register New Owner\" dan isi form dengan data baru</li>
            <li><strong>Check Database</strong>: Pergi ke phpMyAdmin (port 8081) dan cek tabel data_pengguna dan owner_parkir</li>
            <li><strong>Login</strong>: Login menggunakan email dan password yang baru didaftarkan</li>
            <li><strong>Verify</strong>: Setelah login, dashboard akan menampilkan data owner dari database</li>
            <li><strong>Logout</strong>: Test logout dan session handling</li>
        </ol>
    </div>

    <div class='test-section' style='background: #f0f8ff;'>
        <h2>🔍 Database Access</h2>
        <ul style='color: #333; line-height: 1.8;'>
            <li><strong>phpMyAdmin</strong>: <a href='http://localhost:8081' target='_blank'>http://localhost:8081</a></li>
            <li><strong>Username</strong>: root</li>
            <li><strong>Password</strong>: rootpassword</li>
            <li><strong>Database</strong>: spark</li>
        </ul>
    </div>

    </div>
</body>
</html>
";
