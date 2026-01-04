<?php
/**
 * REMOVE PAYMENT METHOD API
 * Deletes payment method after verifying ownership
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
    $stmt = $pdo->prepare("SELECT id_wallet, is_default FROM wallet_methods WHERE id_wallet = ? AND id_pengguna = ?");
    $stmt->execute([$id_wallet, $user['id_pengguna']]);
    $method = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$method) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment method not found']);
        exit;
    }
    
    // Delete payment method
    $stmt = $pdo->prepare("DELETE FROM wallet_methods WHERE id_wallet = ?");
    $stmt->execute([$id_wallet]);
    
    // If deleted method was default, set another as default
    if ($method['is_default']) {
        $stmt = $pdo->prepare("
            UPDATE wallet_methods 
            SET is_default = 1 
            WHERE id_pengguna = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$user['id_pengguna']]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment method removed successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Remove payment method error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
