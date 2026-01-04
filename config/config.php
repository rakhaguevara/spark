<?php
// BASEURL - Sesuaikan dengan path folder Anda
// Folder Anda: spark-final
// Jika akses via: http://localhost/spark-final → gunakan: http://localhost/spark-final
// Jika akses via: http://localhost/spark-app/spark-final → gunakan: http://localhost/spark-app/spark-final

// Auto-detect BASEURL
if (!defined('BASEURL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Docker env takes priority
    $envBaseurl = getenv('BASEURL');
    if ($envBaseurl) {
        define('BASEURL', $envBaseurl);
    } else {
        // Deteksi dari SCRIPT_NAME
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Jika file diakses dari admin/login.php, naik 2 level
        if (strpos($scriptPath, '/admin/') !== false || strpos($scriptPath, '/functions/') !== false) {
            $basePath = dirname(dirname($scriptPath));
        } elseif (strpos($scriptPath, '/config/') !== false) {
            $basePath = dirname($scriptPath);
        } else {
            // Default detection
            $basePath = '/spark-final';
        }
        
        // Normalize path
        $basePath = str_replace('\\', '/', $basePath);
        $basePath = rtrim($basePath, '/');
        
        // Jika masih kosong, gunakan default
        if (empty($basePath) || $basePath === '/' || $basePath === '.') {
            $basePath = '/spark-final';
        }
        
        define('BASEURL', $protocol . '://' . $host . $basePath);
    }
}

