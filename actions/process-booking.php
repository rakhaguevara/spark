<?php

/**
 * PROCESS BOOKING (TEST MODE - NO PAYMENT)
 * Saves booking to existing database schema and generates QR ticket
 */

// TEST MODE FLAG
define('TEST_MODE', true);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASEURL . '/pages/login.php');
    exit;
}

$user = getCurrentUser();

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

// Get input with null coalescing (only fields that MUST come from POST)
$id_tempat = $_POST['id_parkir'] ?? $_POST['id_tempat'] ?? '';
$id_slot = $_POST['id_slot'] ?? 1; // Default slot for now
$id_kendaraan = $_POST['id_kendaraan'] ?? '';
$nomor_plat = trim($_POST['nomor_plat'] ?? '');
$tanggal_booking = $_POST['tanggal_booking'] ?? '';
$waktu_mulai_time = $_POST['waktu_mulai'] ?? '';
$durasi = $_POST['durasi'] ?? $_POST['durasi_jam'] ?? '';

// Combine date and time into full datetime
if (!empty($tanggal_booking) && !empty($waktu_mulai_time)) {
    // If waktu_mulai_time is just time (HH:MM), combine with date
    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $waktu_mulai_time)) {
        $waktu_mulai = $tanggal_booking . ' ' . $waktu_mulai_time;
        if (strlen($waktu_mulai_time) == 5) { // HH:MM format
            $waktu_mulai .= ':00';
        }
    } else {
        $waktu_mulai = $waktu_mulai_time; // Already full datetime
    }
} else {
    $waktu_mulai = $waktu_mulai_time; // Fallback
}

// TEST MODE: Only validate fields that CANNOT be derived
if (TEST_MODE) {
    $missing_fields = [];
    if (empty($id_tempat)) $missing_fields[] = 'id_tempat/id_parkir';
    if (empty($nomor_plat)) $missing_fields[] = 'nomor_plat';
    if (empty($waktu_mulai)) $missing_fields[] = 'waktu_mulai';
    if (empty($durasi)) $missing_fields[] = 'durasi/durasi_jam';

    if (!empty($missing_fields)) {
        error_log("Missing fields: " . implode(', ', $missing_fields));
        error_log("POST data: " . print_r($_POST, true));
        die('Missing required booking fields: ' . implode(', ', $missing_fields));
    }
} else {
    // Production mode would validate payment fields here
    // (Not implemented yet)
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Step 1: Get parking details (for price calculation)
    $stmt = $pdo->prepare("SELECT harga_per_jam FROM tempat_parkir WHERE id_tempat = ?");
    $stmt->execute([$id_tempat]);
    $parking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$parking) {
        throw new Exception("Parking location not found");
    }

    // Step 2: Calculate total_harga (SERVER-SIDE)
    $total_harga = $parking['harga_per_jam'] * $durasi;

    // Step 3: Calculate waktu_selesai (SERVER-SIDE)
    $waktu_selesai = date('Y-m-d H:i:s', strtotime($waktu_mulai) + ($durasi * 3600));

    // Step 4: Check or create vehicle in kendaraan_pengguna
    $secret_salt = defined('SECRET_SALT') ? SECRET_SALT : 'default-salt-change-me';
    $plat_hash = hash('sha256', $nomor_plat . $secret_salt);
    $plat_hint = substr($nomor_plat, -4);

    // If id_kendaraan provided, get id_jenis from it
    if (!empty($id_kendaraan)) {
        $stmt = $pdo->prepare("SELECT id_jenis FROM kendaraan_pengguna WHERE id_kendaraan = ?");
        $stmt->execute([$id_kendaraan]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vehicle) {
            $id_jenis = $vehicle['id_jenis'];
        } else {
            throw new Exception("Vehicle not found");
        }
    } else {
        // Check if vehicle exists by plate hash
        $stmt = $pdo->prepare("
            SELECT id_kendaraan, id_jenis FROM kendaraan_pengguna 
            WHERE id_pengguna = ? AND plat_hash = ?
        ");
        $stmt->execute([$user['id_pengguna'], $plat_hash]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            // Need to get id_jenis from POST or default to 1 (assuming first vehicle type)
            $id_jenis = $_POST['id_jenis_kendaraan'] ?? $_POST['id_jenis'] ?? 1;

            // Insert new vehicle
            $stmt = $pdo->prepare("
                INSERT INTO kendaraan_pengguna (id_pengguna, id_jenis, plat_hash, plat_hint)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user['id_pengguna'], $id_jenis, $plat_hash, $plat_hint]);
            $id_kendaraan = $pdo->lastInsertId();
        } else {
            $id_kendaraan = $vehicle['id_kendaraan'];
            $id_jenis = $vehicle['id_jenis'];
        }
    }

    // Step 2: Generate QR secret
    $qr_secret = bin2hex(random_bytes(32)); // 64 char hex

    // Step 3: Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO booking_parkir (
            id_pengguna, id_tempat, id_slot, id_kendaraan,
            waktu_mulai, waktu_selesai, total_harga,
            status_booking, qr_secret
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)
    ");

    $stmt->execute([
        $user['id_pengguna'],
        $id_tempat,
        $id_slot,
        $id_kendaraan,
        $waktu_mulai,
        $waktu_selesai,
        $total_harga,
        $qr_secret
    ]);

    $id_booking = $pdo->lastInsertId();

    // Step 4: Generate QR token
    $qr_token = hash('sha256', $qr_secret . $id_booking . time());

    // Calculate expiry (booking end time + 1 hour tolerance)
    $expires_at = date('Y-m-d H:i:s', strtotime($waktu_selesai) + 3600);

    // Step 5: Insert QR session
    $stmt = $pdo->prepare("
        INSERT INTO qr_session (id_booking, qr_token, expires_at)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$id_booking, $qr_token, $expires_at]);

    // Step 6: Update slot status
    $stmt = $pdo->prepare("
        UPDATE slot_parkir 
        SET status_slot = 'booked'
        WHERE id_slot = ?
    ");
    $stmt->execute([$id_slot]);

    // Commit transaction
    $pdo->commit();

    // Redirect to My Ticket page
    header('Location: ' . BASEURL . '/pages/my-ticket.php?success=1&booking=' . $id_booking);
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Booking error: " . $e->getMessage());
    die('Database error: ' . $e->getMessage());
}
