<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/owner-auth.php';

requireOwnerLogin();

$owner = getCurrentOwner();
$pdo = getDBConnection();
$current_page = 'dashboard';

// Ambil statistik parkir owner
$stats = [
    'total_tempat' => 0,
    'tempat_aktif' => 0,
    'total_revenue' => 0,
    'total_booking' => 0,
    'slot_tersedia' => 0,
    'slot_terisi' => 0
];

try {
    // Total tempat parkir
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tempat_parkir WHERE id_pengguna = ?");
    $stmt->execute([$owner['id_pengguna']]);
    $stats['total_tempat'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total tempat aktif
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tempat_parkir WHERE id_pengguna = ? AND status_tempat = 'aktif'");
    $stmt->execute([$owner['id_pengguna']]);
    $stats['tempat_aktif'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(bp.total_harga), 0) as total 
        FROM booking_parkir bp
        JOIN tempat_parkir tp ON bp.id_tempat = tp.id_tempat
        WHERE tp.id_pengguna = ? AND bp.status_booking = 'completed'
    ");
    $stmt->execute([$owner['id_pengguna']]);
    $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Total booking
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM booking_parkir bp
        JOIN tempat_parkir tp ON bp.id_tempat = tp.id_tempat
        WHERE tp.id_pengguna = ?
    ");
    $stmt->execute([$owner['id_pengguna']]);
    $stats['total_booking'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {
    error_log('OWNER DASHBOARD ERROR: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner | SPARK</title>
    
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/owner.css">
</head>
<body>

<div class="owner-wrapper">

    <!-- SIDEBAR -->
    <div class="owner-sidebar">
        <div class="sidebar-brand">
            <img src="<?= BASEURL ?>/assets/img/logoSpark.png" alt="SPARK Logo" class="sidebar-logo">
        </div>

        <ul class="sidebar-menu">
            <li><a href="<?= BASEURL ?>/owner/dashboard.php" class="active">
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

        <!-- HEADER -->
        <div class="content-header">
            <h1>Dashboard Pemilik Lahan</h1>
            <p>Kelola parkiran Anda dan pantau performa real-time</p>
        </div>

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h2>Selamat Datang, <?= htmlspecialchars($owner['nama_pengguna']) ?>! 👋</h2>
            <p>Atur parkiran Anda, pantau slot, validasi tiket, dan tingkatkan penghasilan dengan sistem SPARK.</p>
        </div>

        <!-- STATS GRID -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-label">Total Lahan Parkir</div>
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                </div>
                <div class="stat-value"><?= $stats['total_tempat'] ?></div>
                <div class="stat-footer">Lokasi parkir terdaftar</div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-label">Lahan Aktif</div>
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-value"><?= $stats['tempat_aktif'] ?></div>
                <div class="stat-footer">Sedang beroperasi</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-label">Total Penghasilan</div>
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                </div>
                <div class="stat-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                <div class="stat-footer">Dari semua booking</div>
            </div>

            <div class="stat-card info">
                <div class="stat-header">
                    <div class="stat-label">Total Booking</div>
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
                <div class="stat-value"><?= $stats['total_booking'] ?></div>
                <div class="stat-footer">Transaksi parkir</div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="action-grid">
            <a href="<?= BASEURL ?>/owner/manage-parking.php" class="action-btn">
                <i class="fas fa-plus"></i>
                Kelola Lahan
            </a>
            <a href="<?= BASEURL ?>/owner/scan-ticket.php" class="action-btn">
                <i class="fas fa-qrcode"></i>
                Scan Tiket
            </a>
            <a href="<?= BASEURL ?>/owner/monitoring.php" class="action-btn">
                <i class="fas fa-bar-chart"></i>
                Monitoring
            </a>
            <a href="<?= BASEURL ?>/owner/scan-history.php" class="action-btn">
                <i class="fas fa-clock"></i>
                History
            </a>
        </div>

        <!-- RECENT ACTIVITY -->
        <div class="recent-section">
            <div class="section-title">
                <i class="fas fa-clock"></i>
                Aktivitas Terbaru
            </div>
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-text">
                        <span class="activity-badge success">✓ NEW</span>
                        Dashboard Owner siap digunakan
                    </div>
                    <div class="activity-time">Hari ini</div>
                </li>
                <li class="activity-item">
                    <div class="activity-text">
                        <span class="activity-badge info">🚀</span>
                        Mulai kelola lahan parkir Anda
                    </div>
                    <div class="activity-time">Sekarang</div>
                </li>
                <li class="activity-item">
                    <div class="activity-text">
                        <span class="activity-badge info">🔧</span>
                        Fitur scan tiket siap digunakan
                    </div>
                    <div class="activity-time">Siap</div>
                </li>
                <li class="activity-item">
                    <div class="activity-text">
                        <span class="activity-badge info">📊</span>
                        Real-time monitoring untuk slot parkir
                    </div>
                    <div class="activity-time">Aktif</div>
                </li>
            </ul>
        </div>

    </div>

</div>

</body>
</html>