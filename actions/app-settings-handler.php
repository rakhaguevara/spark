<?php
// actions/app-settings-handler.php
// Handles app settings updates (language, theme, location, etc.)

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
        // Get settings
        $language = $_POST['language'] ?? 'id';
        $theme = $_POST['theme'] ?? 'auto';
        $distanceUnit = $_POST['distance_unit'] ?? 'km';
        $autoLocation = isset($_POST['auto_location']) && $_POST['auto_location'] === 'true' ? 1 : 0;
        $manualLocation = $_POST['manual_location'] ?? null;

        // Validation
        $validLanguages = ['id', 'en'];
        $validThemes = ['auto', 'light', 'dark'];
        $validUnits = ['km', 'miles'];

        if (!in_array($language, $validLanguages)) {
            $language = 'id';
        }

        if (!in_array($theme, $validThemes)) {
            $theme = 'auto';
        }

        if (!in_array($distanceUnit, $validUnits)) {
            $distanceUnit = 'km';
        }

        // If auto location is enabled, clear manual location
        if ($autoLocation) {
            $manualLocation = null;
        }

        // Check if app settings columns exist
        try {
            $checkColumn = $pdo->query("SHOW COLUMNS FROM data_pengguna LIKE 'app_language'");
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
            SET app_language = ?,
                app_theme = ?,
                app_distance_unit = ?,
                app_auto_location = ?,
                app_manual_location = ?
            WHERE id_pengguna = ?
        ");
        $stmt->execute([
            $language,
            $theme,
            $distanceUnit,
            $autoLocation,
            $manualLocation,
            $userId
        ]);

        echo json_encode([
            'success' => true, 
            'message' => 'App settings updated',
            'settings' => [
                'language' => $language,
                'theme' => $theme,
                'distance_unit' => $distanceUnit,
                'auto_location' => $autoLocation,
                'manual_location' => $manualLocation
            ]
        ]);
        exit;

    } catch (PDOException $e) {
        error_log('App settings error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        exit;
    }
}

// Invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
