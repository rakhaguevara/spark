<?php
/**
 * QR DIAGNOSTIC TOOL
 * Check QR token status for all bookings
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // Get all bookings with their QR status
    $stmt = $pdo->prepare("
        SELECT 
            b.id_booking,
            b.id_pengguna,
            b.status_booking,
            b.qr_secret,
            b.waktu_mulai,
            b.waktu_selesai,
            q.id_qr,
            q.qr_token,
            q.expires_at,
            t.nama_tempat
        FROM booking_parkir b
        LEFT JOIN qr_session q ON b.id_booking = q.id_booking
        LEFT JOIN tempat_parkir t ON b.id_tempat = t.id_tempat
        WHERE b.status_booking IN ('confirmed', 'ongoing')
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $diagnostics = [];
    
    foreach ($bookings as $booking) {
        $issues = [];
        
        // Check for issues
        if (empty($booking['qr_secret'])) {
            $issues[] = 'Missing qr_secret';
        }
        
        if (empty($booking['qr_token'])) {
            $issues[] = 'Missing qr_token (no qr_session entry)';
        }
        
        if (!empty($booking['expires_at']) && strtotime($booking['expires_at']) < time()) {
            $issues[] = 'QR token expired';
        }
        
        $diagnostics[] = [
            'booking_id' => $booking['id_booking'],
            'user_id' => $booking['id_pengguna'],
            'status' => $booking['status_booking'],
            'parking' => $booking['nama_tempat'],
            'has_qr_secret' => !empty($booking['qr_secret']),
            'has_qr_session' => !empty($booking['id_qr']),
            'has_qr_token' => !empty($booking['qr_token']),
            'qr_expires_at' => $booking['expires_at'],
            'issues' => $issues,
            'needs_fix' => count($issues) > 0
        ];
    }
    
    echo json_encode([
        'success' => true,
        'total_bookings' => count($bookings),
        'bookings' => $diagnostics,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
