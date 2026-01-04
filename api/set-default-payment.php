<?php
/**
 * SET DEFAULT PAYMENT METHOD API
 * Sets a payment method as default
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$id_wallet = $_POST['id_wallet'] ?? '';

if (empty($id_wallet)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Payment method ID required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id_wallet FROM wallet_methods WHERE id_wallet = ? AND id_pengguna = ?");
    $stmt->execute([$id_wallet, $user['id_pengguna']]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment method not found']);
        exit;
    }
    
    // Unset all defaults for this user
    $stmt = $pdo->prepare("UPDATE wallet_methods SET is_default = 0 WHERE id_pengguna = ?");
    $stmt->execute([$user['id_pengguna']]);
    
    // Set new default
    $stmt = $pdo->prepare("UPDATE wallet_methods SET is_default = 1 WHERE id_wallet = ?");
    $stmt->execute([$id_wallet]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Default payment method updated'
    ]);
    
} catch (Exception $e) {
    error_log("Set default payment error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
