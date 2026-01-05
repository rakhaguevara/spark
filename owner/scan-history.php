<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/owner-auth.php';

requireOwnerLogin();

$owner = getCurrentOwner();
$pdo = getDBConnection();

// Get scan history with pagination
$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$scans = [];
$total = 0;

try {
    // Get total count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM qr_session 
        WHERE id_owner = ?
    ");
    $stmt->execute([$owner['id_pengguna']]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get scan history
    $stmt = $pdo->prepare("
        SELECT qs.*, tp.nama_tempat, bp.id_booking
        FROM qr_session qs
        LEFT JOIN tempat_parkir tp ON qs.id_tempat = tp.id_tempat
        LEFT JOIN booking_parkir bp ON qs.id_booking = bp.id_booking
        WHERE qs.id_owner = ?
        ORDER BY qs.waktu_scan DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$owner['id_pengguna'], $limit, $offset]);
    $scans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('SCAN HISTORY ERROR: ' . $e->getMessage());
}

$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Scan | SPARK</title>
    
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/owner.css">
    
    <style>
    </style>
</head>
</head>
<body>

<div class="owner-wrapper">

    <!-- SIDEBAR -->
    <div class="owner-sidebar">
        <div class="sidebar-brand">
            <img src="<?= BASEURL ?>/assets/img/logoSpark.png" alt="SPARK Logo" class="sidebar-logo">
        </div>

        <ul class="sidebar-menu">
            <li><a href="<?= BASEURL ?>/owner/dashboard.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a></li>
            <li><a href="<?= BASEURL ?>/owner/manage-parking.php">
                <i class="fas fa-building"></i>
                <span>Kelola Lahan</span>
            </a></li>
            <li><a href="<?= BASEURL ?>/owner/scan-ticket.php">
                <i class="fas fa-qrcode"></i>
                <span>Scan Tiket</span>
            </a></li>
            <li><a href="<?= BASEURL ?>/owner/monitoring.php">
                <i class="fas fa-chart-line"></i>
                <span>Monitoring</span>
            </a></li>
            <li><a href="<?= BASEURL ?>/owner/scan-history.php" class="active">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a></li>

            <li class="divider"></li>
            <li><a href="<?= BASEURL ?>/owner/settings.php">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a></li>
        </ul>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?= strtoupper(substr($owner['nama_pengguna'], 0, 1)) ?></div>
                <div class="sidebar-user-info">
                    <div class="name"><?= htmlspecialchars(substr($owner['nama_pengguna'], 0, 15)) ?></div>
                    <div class="email"><?= htmlspecialchars(substr($owner['email_pengguna'], 0, 15)) ?></div>
                </div>
            </div>
            <a href="<?= BASEURL ?>/owner/logout.php" class="sidebar-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="owner-content">
        <div class="content-header">
            <h1>Riwayat Pemindaian</h1>
            <p>Lihat semua aktivitas pemindaian QR code</p>
        </div>

        <div class="table-container">
            <?php if (empty($scans)): ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>Belum ada riwayat pemindaian</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Waktu Scan</th>
                            <th>Lokasi Parkir</th>
                            <th>Tipe Scan</th>
                            <th>Booking ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scans as $scan): ?>
                            <tr>
                                <td>
                                    <div class="parking-name"><?= date('d M Y', strtotime($scan['waktu_scan'])) ?></div>
                                    <div class="time"><?= date('H:i:s', strtotime($scan['waktu_scan'])) ?></div>
                                </td>
                                <td><?= htmlspecialchars($scan['nama_tempat'] ?? '-') ?></td>
                                <td>
                                    <span class="badge <?= $scan['tipe_scan'] === 'masuk' ? 'success' : 'warning' ?>">
                                        <?= $scan['tipe_scan'] === 'masuk' ? '📥 Masuk' : '📤 Keluar' ?>
                                    </span>
                                </td>
                                <td>#<?= $scan['id_booking'] ?? '-' ?></td>
                                <td>
                                    <span class="badge <?= $scan['status_scan'] === 'valid' ? 'success' : 'error' ?>">
                                        <?= $scan['status_scan'] === 'valid' ? '✓ Valid' : '✗ Invalid' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1">« First</a>
                            <a href="?page=<?= $page - 1 ?>">‹ Prev</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>">Next ›</a>
                            <a href="?page=<?= $total_pages ?>">Last »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
