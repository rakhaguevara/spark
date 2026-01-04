<?php
/**
 * CANCEL TICKET ACTION
 * Handles parking ticket cancellation
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user = getCurrentUser();
$bookingId = $_POST['booking_id'] ?? null;

if (!$bookingId) {
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Verify ownership and status
    $stmt = $pdo->prepare("
        SELECT id_booking, status_booking 
        FROM booking_parkir 
        WHERE id_booking = ? AND id_pengguna = ?
    ");
    $stmt->execute([$bookingId, $user['id_pengguna']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
        exit;
    }

    // Check if cancellable
    if (!in_array($booking['status_booking'], ['pending', 'confirmed'])) {
        echo json_encode(['success' => false, 'message' => 'Ticket cannot be cancelled in current status']);
        exit;
    }

    // Update status to cancelled
    $updateStmt = $pdo->prepare("UPDATE booking_parkir SET status_booking = 'cancelled' WHERE id_booking = ?");
    $result = $updateStmt->execute([$bookingId]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Ticket cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel ticket']);
    }

} catch (Exception $e) {
    error_log("Cancel Ticket Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
