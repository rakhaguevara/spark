<?php
/**
 * ADD PAYMENT METHOD API
 * Validates and stores payment method with masked account number
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

// Validate input
$type = $_POST['type'] ?? '';
$provider_name = trim($_POST['provider_name'] ?? '');
$account_number = trim($_POST['account_number'] ?? '');

if (empty($type) || empty($provider_name) || empty($account_number)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

// Validate type
if (!in_array($type, ['bank', 'ewallet', 'paypal'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment type']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Mask account number (show only last 4 digits)
    $account_length = strlen($account_number);
    if ($account_length > 4) {
        $masked = str_repeat('*', $account_length - 4) . substr($account_number, -4);
    } else {
        $masked = $account_number;
    }
    
    // Check if this is the first payment method (auto-set as default)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wallet_methods WHERE id_pengguna = ?");
    $stmt->execute([$user['id_pengguna']]);
    $count = $stmt->fetchColumn();
    $is_default = ($count == 0) ? 1 : 0;
    
    // Insert payment method
    $stmt = $pdo->prepare("
        INSERT INTO wallet_methods (id_pengguna, type, provider_name, account_identifier, is_default)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user['id_pengguna'],
        $type,
        $provider_name,
        $masked,
        $is_default
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment method added successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Add payment method error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
