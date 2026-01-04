<?php
$pageTitle = 'Statistik & Analitik';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Statistik penggunaan lahan parkir
$stmt = $pdo->query("
    SELECT 
        tp.id_tempat,
        tp.nama_tempat,
        COUNT(DISTINCT bp.id_booking) as total_booking,
        SUM(CASE WHEN bp.status_booking = 'completed' THEN 1 ELSE 0 END) as booking_completed,
        SUM(CASE WHEN bp.status_booking = 'completed' THEN bp.total_harga ELSE 0 END) as total_pendapatan,
        COUNT(DISTINCT sp.id_slot) as total_slot,
        SUM(CASE WHEN sp.status_slot = 'booked' THEN 1 ELSE 0 END) as slot_terpakai,
        ROUND(COUNT(DISTINCT bp.id_booking) * 100.0 / NULLIF(COUNT(DISTINCT sp.id_slot), 0), 2) as utilization_rate
    FROM tempat_parkir tp
    LEFT JOIN booking_parkir bp ON tp.id_tempat = bp.id_tempat
    LEFT JOIN slot_parkir sp ON tp.id_tempat = sp.id_tempat
    GROUP BY tp.id_tempat
    ORDER BY total_booking DESC
");
$parking_stats = $stmt->fetchAll();

// Statistik per bulan (12 bulan terakhir)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan,
        DATE_FORMAT(created_at, '%M %Y') as bulan_nama,
        COUNT(*) as jumlah_booking,
        SUM(CASE WHEN status_booking = 'completed' THEN total_harga ELSE 0 END) as pendapatan,
        SUM(CASE WHEN status_booking = 'completed' THEN 1 ELSE 0 END) as booking_selesai
    FROM booking_parkir
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan DESC
");
$monthly_stats = $stmt->fetchAll();

// Statistik per hari dalam seminggu
$stmt = $pdo->query("
    SELECT 
        DAYNAME(created_at) as hari,
        DAYOFWEEK(created_at) as hari_num,
        COUNT(*) as jumlah_booking
    FROM booking_parkir
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY DAYOFWEEK(created_at)
    ORDER BY hari_num
");
$daily_stats = $stmt->fetchAll();

// Statistik per jam
$stmt = $pdo->query("
    SELECT 
        HOUR(waktu_mulai) as jam,
        COUNT(*) as jumlah_booking
    FROM booking_parkir
    WHERE waktu_mulai >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY HOUR(waktu_mulai)
    ORDER BY jam
");
$hourly_stats = $stmt->fetchAll();

// Top users
$stmt = $pdo->query("
    SELECT 
        dp.id_pengguna,
        dp.nama_pengguna,
        dp.email_pengguna,
        COUNT(bp.id_booking) as total_booking,
        SUM(CASE WHEN bp.status_booking = 'completed' THEN bp.total_harga ELSE 0 END) as total_spent
    FROM data_pengguna dp
    JOIN booking_parkir bp ON dp.id_pengguna = bp.id_pengguna
    WHERE dp.role_pengguna = 1
    GROUP BY dp.id_pengguna
    ORDER BY total_booking DESC
    LIMIT 10
");
$top_users = $stmt->fetchAll();

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>
        
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-section-title">Statistik & Analitik</h2>
                    <p class="admin-info-text" style="margin-top: 8px;">
                        Analisis penggunaan dan performa lahan parkir
                    </p>
                </div>
            </div>
            
            <!-- Usage Statistics by Parking -->
            <div class="admin-table-container" style="margin-bottom: 32px;">
                <div class="admin-table-header">
                    <h2 class="admin-table-title">Statistik Penggunaan Lahan Parkir</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama Tempat</th>
                            <th>Total Booking</th>
                            <th>Booking Selesai</th>
                            <th>Total Pendapatan</th>
                            <th>Slot</th>
                            <th>Tingkat Penggunaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($parking_stats)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="admin-empty-state">
                                        <i class="fas fa-chart-line"></i>
                                        <p>Belum ada data statistik</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($parking_stats as $stat): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($stat['nama_tempat']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-info">
                                            <?= $stat['total_booking'] ?> booking
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-success">
                                            <?= $stat['booking_completed'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= formatRupiah($stat['total_pendapatan'] ?? 0) ?></strong>
                                    </td>
                                    <td>
                                        <?= $stat['slot_terpakai'] ?? 0 ?>/<?= $stat['total_slot'] ?? 0 ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $rate = $stat['utilization_rate'] ?? 0;
                                        $badge_class = $rate >= 70 ? 'admin-badge-success' : ($rate >= 40 ? 'admin-badge-warning' : 'admin-badge-danger');
                                        ?>
                                        <span class="admin-badge <?= $badge_class ?>">
                                            <?= number_format($rate, 1) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Monthly Statistics -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2 class="admin-table-title">Statistik Per Bulan</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Booking</th>
                                <th>Selesai</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthly_stats)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--spark-text-light);">
                                        Belum ada data
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($monthly_stats as $month): ?>
                                    <tr>
                                        <td><?= $month['bulan_nama'] ?></td>
                                        <td><?= $month['jumlah_booking'] ?></td>
                                        <td><?= $month['booking_selesai'] ?></td>
                                        <td><?= formatRupiah($month['pendapatan'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Daily Statistics -->
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2 class="admin-table-title">Statistik Per Hari (3 Bulan)</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jumlah Booking</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daily_stats)): ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: var(--spark-text-light);">
                                        Belum ada data
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daily_stats as $day): ?>
                                    <tr>
                                        <td><?= $day['hari'] ?></td>
                                        <td>
                                            <span class="admin-badge admin-badge-info">
                                                <?= $day['jumlah_booking'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Hourly Statistics -->
            <div class="admin-table-container" style="margin-bottom: 32px;">
                <div class="admin-table-header">
                    <h2 class="admin-table-title">Jam Puncak Booking (3 Bulan)</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Jumlah Booking</th>
                            <th>Visualisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hourly_stats)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--spark-text-light);">
                                    Belum ada data
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $max_booking = max(array_column($hourly_stats, 'jumlah_booking'));
                            foreach ($hourly_stats as $hour): 
                                $percentage = $max_booking > 0 ? ($hour['jumlah_booking'] / $max_booking) * 100 : 0;
                            ?>
                                <tr>
                                    <td><?= str_pad($hour['jam'], 2, '0', STR_PAD_LEFT) ?>:00</td>
                                    <td>
                                        <span class="admin-badge admin-badge-info">
                                            <?= $hour['jumlah_booking'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="background: var(--spark-bg); height: 20px; border-radius: 4px; overflow: hidden;">
                                            <div style="background: var(--spark-yellow); height: 100%; width: <?= $percentage ?>%; transition: width 0.3s;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Top Users -->
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2 class="admin-table-title">Top 10 Pengguna Aktif</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Total Booking</th>
                            <th>Total Pengeluaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_users)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--spark-text-light);">
                                    Belum ada data
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($top_users as $user): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($user['nama_pengguna']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['email_pengguna']) ?></td>
                                    <td>
                                        <span class="admin-badge admin-badge-success">
                                            <?= $user['total_booking'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= formatRupiah($user['total_spent'] ?? 0) ?></strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

