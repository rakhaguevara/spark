<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

echo '<h2>Database Test</h2>';
echo '<p><strong>BASEURL:</strong> ' . BASEURL . '</p>';
echo '<p><strong>DB_HOST:</strong> ' . DB_HOST . '</p>';
echo '<p><strong>DB_NAME:</strong> ' . DB_NAME . '</p>';
echo '<p><strong>DB_USER:</strong> ' . DB_USER . '</p>';

try {
    $pdo = getDBConnection();
    echo '<p style="color: green;"><strong>✓ Database Connected</strong></p>';

    // Test query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM data_pengguna");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo '<p><strong>Total Users:</strong> ' . $result['count'] . '</p>';

    // Show first user
    $stmt = $pdo->prepare("SELECT id_pengguna, nama_pengguna, email_pengguna, role_pengguna FROM data_pengguna LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo '<p><strong>Sample User:</strong></p>';
        echo '<pre>' . print_r($user, true) . '</pre>';
    }
} catch (Exception $e) {
    echo '<p style="color: red;"><strong>✗ Error:</strong> ' . $e->getMessage() . '</p>';
}
