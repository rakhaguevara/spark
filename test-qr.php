<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/lib/phpqrcode-2010100721_1.1.4/phpqrcode/qrlib.php';

$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT qr_token FROM qr_session WHERE id_booking = 15');
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $qr_content = 'qr:' . $row['qr_token'];
    $file = '/tmp/test-qr.png';
    QRcode::png($qr_content, $file, QR_ECLEVEL_M, 10, 2);

    if (file_exists($file)) {
        echo "QR image generated: " . filesize($file) . " bytes\n";
        unlink($file);
        echo "QR generation SUCCESS!\n";
    } else {
        echo "Failed to generate QR\n";
    }
} else {
    echo "No token found\n";
}
