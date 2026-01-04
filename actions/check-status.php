<?php
/**
 * CHECK BOOKING STATUS ACTION
 * Returns current status for polling
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT status_booking FROM booking_parkir WHERE id_booking = ?");
    $stmt->execute([$bookingId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'status' => $result['status_booking']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
