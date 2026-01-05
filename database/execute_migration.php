<?php
/**
 * Migration Execution Script
 * Run this file once to create parking_photos table
 * Visit: http://localhost/spark/database/execute_migration.php
 */

// Include database config
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>SPARK Photo Migration Execution</h2>";
    echo "<pre>";
    
    // Read migration SQL
    $sql = file_get_contents(__DIR__ . '/add_parking_photos.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s) && !str_starts_with($s, '--')
    );
    
    foreach ($statements as $statement) {
        echo "\nExecuting:\n$statement\n";
        $pdo->exec($statement);
        echo "✓ Success\n";
    }
    
    echo "\n--- MIGRATION COMPLETE ---\n";
    
    // Verify table exists
    $result = $pdo->query("SHOW TABLES LIKE 'parking_photos'")->fetch();
    if ($result) {
        echo "✓ parking_photos table created successfully\n";
        
        // Show table structure
        echo "\nTable Structure:\n";
        $columns = $pdo->query("DESCRIBE parking_photos")->fetchAll();
        foreach ($columns as $col) {
            echo "  - {$col['Field']}: {$col['Type']}\n";
        }
    } else {
        echo "✗ Error: parking_photos table not found\n";
    }
    
    echo "</pre>";
    
    // Create uploads directory
    $uploadDir = __DIR__ . '/../uploads/parking_photos';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "<p style='color: green;'>✓ Created upload directory: {$uploadDir}</p>";
    } else {
        echo "<p style='color: blue;'>✓ Upload directory already exists: {$uploadDir}</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>Migration completed! You can now upload photos.</p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Migration Error</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
