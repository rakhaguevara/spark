<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/owner-auth.php';

requireOwnerLogin();

$owner = getCurrentOwner();
$pdo = getDBConnection();

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

$response = [
    'success' => false,
    'message' => 'Invalid request'
];

try {
    if (!isset($data['parking_id']) || !isset($data['booking_id'])) {
        $response['message'] = 'Data tidak lengkap';
        echo json_encode($response);
        exit;
    }

    $parking_id = intval($data['parking_id']);
    $booking_id = intval($data['booking_id']);
    $qr_token = $data['qr_token'] ?? '';

    // Verify owner has access to this parking
    $stmt = $pdo->prepare("
        SELECT id_tempat FROM tempat_parkir 
        WHERE id_tempat = ? AND id_pengguna = ?
    ");
    $stmt->execute([$parking_id, $owner['id_pengguna']]);
    if (!$stmt->fetch()) {
        $response['message'] = 'Anda tidak memiliki akses ke parkir ini';
        echo json_encode($response);
        exit;
    }

    // Get booking
    $stmt = $pdo->prepare("
        SELECT * FROM booking_parkir 
        WHERE id_booking = ? AND id_tempat = ?
    ");
    $stmt->execute([$booking_id, $parking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $response['message'] = 'Tiket tidak ditemukan';
        echo json_encode($response);
        exit;
    }

    // Validate QR token if provided
    if ($qr_token && $booking['qr_secret'] !== $qr_token) {
        // Log failed attempt
        $stmt = $pdo->prepare("
            INSERT INTO qr_session (id_owner, id_tempat, id_booking, tipe_scan, status_scan, waktu_scan)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$owner['id_pengguna'], $parking_id, $booking_id, 'unknown', 'invalid']);

        $response['message'] = 'Token QR tidak valid - Tiket palsu atau expired';
        $response['status'] = 'invalid';
        echo json_encode($response);
        exit;
    }

    // Check booking status
    if ($booking['status_booking'] === 'cancelled' || $booking['status_booking'] === 'completed') {
        $response['message'] = 'Tiket sudah ' . ($booking['status_booking'] === 'cancelled' ? 'dibatalkan' : 'selesai');
        $response['status'] = $booking['status_booking'];
        echo json_encode($response);
        exit;
    }

    // Determine scan type (check-in or check-out)
    $scan_type = 'masuk';
    $new_status = 'checked_in';

    if ($booking['status_booking'] === 'checked_in') {
        $scan_type = 'keluar';
        $new_status = 'completed';
    }

    // Update booking status
    $stmt = $pdo->prepare("UPDATE booking_parkir SET status_booking = ? WHERE id_booking = ?");
    $stmt->execute([$new_status, $booking_id]);

    // Record scan in history
    $stmt = $pdo->prepare("
        INSERT INTO qr_session (id_owner, id_tempat, id_booking, tipe_scan, status_scan, waktu_scan, qr_token)
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $stmt->execute([$owner['id_pengguna'], $parking_id, $booking_id, $scan_type, 'valid', $qr_token]);

    // Success response
    $response = [
        'success' => true,
        'message' => 'Tiket valid - Scan ' . ($scan_type === 'masuk' ? 'MASUK' : 'KELUAR') . ' tercatat',
        'status' => $new_status,
        'type' => $scan_type === 'masuk' ? 'CHECK-IN' : 'CHECK-OUT',
        'booking_id' => $booking_id,
        'parking_id' => $parking_id
    ];
} catch (PDOException $e) {
    error_log('QR VALIDATION ERROR: ' . $e->getMessage());
    $response['message'] = 'Database error';
}

echo json_encode($response);
