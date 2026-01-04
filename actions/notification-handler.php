<?php
// actions/notification-handler.php
// Handles notification preference updates

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

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get preferences (sent as individual POST params)
        $emailNotifications = isset($_POST['email_notifications']) && $_POST['email_notifications'] === 'true';
        $bookingReminders = isset($_POST['booking_reminders']) && $_POST['booking_reminders'] === 'true';
        $profileUpdates = isset($_POST['profile_updates']) && $_POST['profile_updates'] === 'true';
        
        // Password changes is always forced to true (security requirement)
        $passwordChanges = true;

        // Build JSON object
        $preferences = [
            'email_notifications' => $emailNotifications,
            'booking_reminders' => $bookingReminders,
            'profile_updates' => $profileUpdates,
            'password_changes' => $passwordChanges
        ];

        // Check if notification_preferences column exists
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM data_pengguna LIKE 'notification_preferences'");
            if ($checkColumn->rowCount() == 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Database not migrated. Please run the migration SQL first. Check MIGRATION_INSTRUCTIONS.md'
                ]);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }

        // Update database
        $stmt = $pdo->prepare("
            UPDATE data_pengguna 
            SET notification_preferences = ?
            WHERE id_pengguna = ?
        ");
        $stmt->execute([json_encode($preferences), $userId]);

        echo json_encode([
            'success' => true, 
            'message' => 'Notification preferences updated',
            'preferences' => $preferences
        ]);
        exit;

    } catch (PDOException $e) {
        error_log('Notification preferences error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        exit;
    }
}

// Invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
