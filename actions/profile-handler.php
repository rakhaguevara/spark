<?php
// actions/profile-handler.php
// Handles profile update requests including image upload

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

// Check authentication
if (!isLoggedIn()) {
    header('Location: ' . BASEURL . '/pages/login.php');
    exit;
}

$userId = $_SESSION['id_pengguna'];
$pdo = getDBConnection();

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_profile') {
            // Validate and sanitize input
            $nama = trim($_POST['nama_pengguna'] ?? '');
            $noHp = trim($_POST['noHp_pengguna'] ?? '');

            // Validation
            $errors = [];
            if (empty($nama)) {
                $errors[] = 'Full name is required';
            }
            if (empty($noHp)) {
                $errors[] = 'Phone number is required';
            }

            if (!empty($errors)) {
                $_SESSION['error_message'] = implode(', ', $errors);
                header('Location: ' . BASEURL . '/pages/profile.php');
                exit;
            }



            // Update profile data
            $stmt = $pdo->prepare("
                UPDATE data_pengguna 
                SET nama_pengguna = ?, noHp_pengguna = ?
                WHERE id_pengguna = ?
            ");
            $stmt->execute([$nama, $noHp, $userId]);

            // Refresh session data to reflect changes immediately
            $stmt = $pdo->prepare("
                SELECT dp.*, rp.nama_role
                FROM data_pengguna dp
                JOIN role_pengguna rp ON dp.role_pengguna = rp.id_role
                WHERE dp.id_pengguna = ?
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $updatedUser = $stmt->fetch();
            
            // Update session with fresh data
            if ($updatedUser) {
                $_SESSION['user_name'] = $updatedUser['nama_pengguna'];
            }

            // Check if AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
                exit;
            }

            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: ' . BASEURL . '/pages/profile.php');
            exit;

        } elseif ($action === 'upload_image') {
            // Handle image upload
            if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
                $_SESSION['error_message'] = 'No file uploaded';
                header('Location: ' . BASEURL . '/pages/profile.php');
                exit;
            }

            $file = $_FILES['profile_image'];

            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error_message'] = 'File upload failed';
                header('Location: ' . BASEURL . '/pages/profile.php');
                exit;
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $_SESSION['error_message'] = 'Invalid file type. Only PNG, JPG, and GIF are allowed';
                header('Location: ' . BASEURL . '/pages/profile.php');
                exit;
            }

            // Validate file size (10MB max)
            $maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if ($file['size'] > $maxSize) {
                $_SESSION['error_message'] = 'File size exceeds 10MB limit';
                header('Location: ' . BASEURL . '/pages/profile.php');
                exit;
            }

            // Get file extension
            $extension = match($mimeType) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => 'jpg'
            };

            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads/profile/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate secure filename
            $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            $dbPath = 'profile/' . $filename;

            // Get current profile image to delete old one
            $stmt = $pdo->prepare("SELECT profile_image FROM data_pengguna WHERE id_pengguna = ?");
            $stmt->execute([$userId]);
            $currentImage = $stmt->fetchColumn();

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Update database
                $stmt = $pdo->prepare("
                    UPDATE data_pengguna 
                    SET profile_image = ?
                    WHERE id_pengguna = ?
                ");
                $stmt->execute([$dbPath, $userId]);

                // Delete old image if exists
                if ($currentImage && file_exists(__DIR__ . '/../uploads/' . $currentImage)) {
                    unlink(__DIR__ . '/../uploads/' . $currentImage);
                }

                $_SESSION['success_message'] = 'Profile picture updated successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to save uploaded file';
            }

            header('Location: ' . BASEURL . '/pages/profile.php');
            exit;

        } elseif ($action === 'remove_image') {
            // Get current profile image
            $stmt = $pdo->prepare("SELECT profile_image FROM data_pengguna WHERE id_pengguna = ?");
            $stmt->execute([$userId]);
            $currentImage = $stmt->fetchColumn();

            // Remove from database
            $stmt = $pdo->prepare("
                UPDATE data_pengguna 
                SET profile_image = NULL
                WHERE id_pengguna = ?
            ");
            $stmt->execute([$userId]);

            // Delete file if exists
            if ($currentImage && file_exists(__DIR__ . '/../uploads/' . $currentImage)) {
                unlink(__DIR__ . '/../uploads/' . $currentImage);
            }

            $_SESSION['success_message'] = 'Profile picture removed successfully!';
            header('Location: ' . BASEURL . '/pages/profile.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log('Profile update error: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: ' . BASEURL . '/pages/profile.php');
        exit;
    } catch (Exception $e) {
        error_log('Profile update error: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('Location: ' . BASEURL . '/pages/profile.php');
        exit;
    }
}

// If not POST or invalid action, redirect back
header('Location: ' . BASEURL . '/pages/profile.php');
exit;
