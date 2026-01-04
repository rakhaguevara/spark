<?php
// actions/password-handler.php
// Handles password change requests with validation and security

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['id_pengguna'];
$pdo = getDBConnection();

// Rate limiting: max 5 attempts per 15 minutes
if (!isset($_SESSION['password_attempts'])) {
    $_SESSION['password_attempts'] = [];
}

// Clean old attempts (older than 15 minutes)
$_SESSION['password_attempts'] = array_filter($_SESSION['password_attempts'], function($timestamp) {
    return (time() - $timestamp) < 900; // 15 minutes
});

if (count($_SESSION['password_attempts']) >= 5) {
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please try again in 15 minutes.']);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get input
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];

        if (empty($currentPassword)) {
            $errors[] = 'Current password is required';
        }

        if (empty($newPassword)) {
            $errors[] = 'New password is required';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters';
        } elseif (!preg_match('/[0-9]/', $newPassword)) {
            $errors[] = 'New password must contain at least 1 number';
        } elseif (!preg_match('/[a-zA-Z]/', $newPassword)) {
            $errors[] = 'New password must contain at least 1 letter';
        }

        if (empty($confirmPassword)) {
            $errors[] = 'Please confirm your new password';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
            exit;
        }

        // Verify current password
        $stmt = $pdo->prepare("SELECT password_pengguna FROM data_pengguna WHERE id_pengguna = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_pengguna'])) {
            // Record failed attempt
            $_SESSION['password_attempts'][] = time();
            
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $stmt = $pdo->prepare("
            UPDATE data_pengguna 
            SET password_pengguna = ?
            WHERE id_pengguna = ?
        ");
        $stmt->execute([$hashedPassword, $userId]);

        // Clear password attempts on success
        unset($_SESSION['password_attempts']);

        // Destroy session to force re-login
        session_destroy();

        echo json_encode([
            'success' => true, 
            'message' => 'Password changed successfully. Please login with your new password.',
            'redirect' => BASEURL . '/pages/login.php'
        ]);
        exit;

    } catch (PDOException $e) {
        error_log('Password change error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        exit;
    }
}

// Invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
