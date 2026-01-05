<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/owner-auth.php';

requireOwnerLogin();

$owner = getCurrentOwner();
$pdo = getDBConnection();

// Get parking locations with real-time stats
$parkings_stats = [];
try {
    $stmt = $pdo->prepare("
        SELECT tp.*, 
               (SELECT COUNT(*) FROM booking_parkir WHERE id_tempat = tp.id_tempat AND status_booking IN ('confirmed', 'checked_in')) as active_bookings
        FROM tempat_parkir tp
        WHERE tp.id_pengguna = ?
        ORDER BY tp.nama_tempat ASC
    ");
    $stmt->execute([$owner['id_pengguna']]);
    $parkings_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('MONITORING ERROR: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring | SPARK</title>
    
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
            <li><a href="<?= BASEURL ?>/owner/monitoring.php" class="active">
                <i class="fas fa-chart-line"></i>
                <span>Monitoring</span>
            </a></li>
            <li><a href="<?= BASEURL ?>/owner/scan-history.php">
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
            <h1>Monitoring Real-time</h1>
            <p>Pantau status slot parkir dan aktivitas terkini</p>
        </div>

        <?php if (empty($parkings_stats)): ?>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>Anda belum memiliki lahan parkir untuk dimonitor</p>
            </div>
        <?php else: ?>
            <div class="monitoring-grid">
                <?php foreach ($parkings_stats as $parking): ?>
                    <?php
                        $occupied = $parking['active_bookings'];
                        $available = $parking['total_slot'] - $occupied;
                        $occupancy_percent = ($occupied / $parking['total_slot']) * 100;
                    ?>
                    <div class="monitor-card">
                        <div class="monitor-header">
                            <div class="monitor-name"><?= htmlspecialchars($parking['nama_tempat']) ?></div>
                            <div class="monitor-status">
                                <span class="status-dot"></span>
                                <span>Sedang beroperasi</span>
                            </div>
                        </div>
                        <div class="monitor-body">
                            <div class="slot-progress">
                                <div class="slot-label">
                                    <span>Slot Terisi</span>
                                    <span><?= $occupied ?> / <?= $parking['total_slot'] ?></span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $occupancy_percent ?>%">
                                        <?php if ($occupancy_percent > 15): ?>
                                            <?= round($occupancy_percent, 0) ?>%
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-row">
                                <span class="stat-label"><i class="fas fa-car"></i> Tersedia</span>
                                <span class="stat-value"><?= $available ?> slot</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label"><i class="fas fa-clock"></i> Jam Operasional</span>
                                <span class="stat-value"><?= $parking['jam_buka'] ?> - <?= $parking['jam_tutup'] ?></span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label"><i class="fas fa-dollar-sign"></i> Tarif</span>
                                <span class="stat-value">Rp <?= number_format($parking['harga_jam'], 0, ',', '.') ?>/jam</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-refresh monitoring every 5 seconds
setInterval(() => {
    location.reload();
}, 5000);
</script>

</body>
</html>
