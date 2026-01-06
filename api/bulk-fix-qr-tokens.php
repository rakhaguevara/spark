<?php
/**
 * BULK FIX QR TOKENS
 * Fix all bookings that are missing QR tokens
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // Get all confirmed/ongoing bookings without QR session
    $stmt = $pdo->prepare("
        SELECT 
            b.id_booking,
            b.qr_secret,
            b.waktu_selesai
        FROM booking_parkir b
        LEFT JOIN qr_session q ON b.id_booking = q.id_booking
        WHERE b.status_booking IN ('confirmed', 'ongoing')
        AND q.id_qr IS NULL
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fixed = 0;
    $errors = [];
    
    foreach ($bookings as $booking) {
        try {
            $booking_id = $booking['id_booking'];
            
            // Generate qr_secret if missing
            $qr_secret = $booking['qr_secret'];
            if (empty($qr_secret)) {
                $qr_secret = bin2hex(random_bytes(32));
                
                $stmt = $pdo->prepare("UPDATE booking_parkir SET qr_secret = ? WHERE id_booking = ?");
                $stmt->execute([$qr_secret, $booking_id]);
            }
            
            // Generate QR token
            $qr_token = hash('sha256', $qr_secret . $booking_id . time() . bin2hex(random_bytes(16)));
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Insert QR session
            $stmt = $pdo->prepare("
                INSERT INTO qr_session (id_booking, qr_token, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$booking_id, $qr_token, $expires_at]);
            
            $fixed++;
        } catch (Exception $e) {
            $errors[] = [
                'booking_id' => $booking_id,
                'error' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'total_bookings' => count($bookings),
        'fixed' => $fixed,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
