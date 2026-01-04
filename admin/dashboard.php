<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Statistik Total
$stats = [];

// Total Lahan Parkir
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tempat_parkir");
$stats['total_parking'] = $stmt->fetch()['total'];

// Total Penyedia Lahan
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT id_pemilik) as total 
    FROM tempat_parkir
");
$stats['total_providers'] = $stmt->fetch()['total'];

// Total Pengguna (role = 1)
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM data_pengguna 
    WHERE role_pengguna = 1
");
$stats['total_users'] = $stmt->fetch()['total'];

// Total Transaksi
$stmt = $pdo->query("SELECT COUNT(*) as total FROM booking_parkir");
$stats['total_transactions'] = $stmt->fetch()['total'];

// Total Pendapatan
$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_harga), 0) as total 
    FROM booking_parkir 
    WHERE status_booking = 'completed'
");
$stats['total_revenue'] = $stmt->fetch()['total'];

// Transaksi Hari Ini
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM booking_parkir 
    WHERE DATE(created_at) = CURDATE()
");
$stats['today_transactions'] = $stmt->fetch()['total'];

// Lahan Parkir Paling Populer (berdasarkan jumlah booking)
$stmt = $pdo->query("
    SELECT 
        tp.id_tempat,
        tp.nama_tempat,
        COUNT(bp.id_booking) as total_booking
    FROM tempat_parkir tp
    LEFT JOIN booking_parkir bp ON tp.id_tempat = bp.id_tempat
    GROUP BY tp.id_tempat
    ORDER BY total_booking DESC
    LIMIT 5
");
$popular_parkings = $stmt->fetchAll();

// Transaksi Terbaru
$stmt = $pdo->query("
    SELECT 
        bp.id_booking,
        bp.waktu_mulai,
        bp.waktu_selesai,
        bp.total_harga,
        bp.status_booking,
        bp.created_at,
        tp.nama_tempat,
        dp.nama_pengguna
    FROM booking_parkir bp
    JOIN tempat_parkir tp ON bp.id_tempat = tp.id_tempat
    JOIN data_pengguna dp ON bp.id_pengguna = dp.id_pengguna
    ORDER BY bp.created_at DESC
    LIMIT 10
");
$recent_transactions = $stmt->fetchAll();

// Statistik per bulan (6 bulan terakhir)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan,
        COUNT(*) as jumlah_booking,
        COALESCE(SUM(total_harga), 0) as pendapatan
    FROM booking_parkir
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        AND status_booking = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");
$monthly_stats = $stmt->fetchAll();

// Data untuk grafik - User Growth (7 bulan terakhir)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan,
        COUNT(*) as total_users
    FROM data_pengguna
    WHERE role_pengguna = 1
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");
$user_growth = $stmt->fetchAll();

// Data untuk grafik - Parking Growth (7 bulan terakhir)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as bulan,
        COUNT(*) as total_parking
    FROM tempat_parkir
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");
$parking_growth = $stmt->fetchAll();

// Hitung kumulatif untuk user dan parking
$cumulative_users = [];
$cumulative_parking = [];
$user_sum = 0;
$parking_sum = 0;

foreach ($user_growth as $data) {
    $user_sum += $data['total_users'];
    $cumulative_users[] = [
        'bulan' => $data['bulan'],
        'total' => $user_sum
    ];
}

foreach ($parking_growth as $data) {
    $parking_sum += $data['total_parking'];
    $cumulative_parking[] = [
        'bulan' => $data['bulan'],
        'total' => $parking_sum
    ];
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'admin-badge-warning',
        'confirmed' => 'admin-badge-info',
        'completed' => 'admin-badge-success',
        'cancelled' => 'admin-badge-danger'
    ];
    return $badges[$status] ?? 'admin-badge-pending';
}
?>

<div class="admin-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>
        
        <div class="admin-content">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="admin-flash-message admin-flash-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="admin-flash-message admin-flash-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Welcome Header -->
            <div class="admin-welcome-header">
                <div>
                    <h1 class="admin-welcome-title">Welcome Back, <?= htmlspecialchars($admin['nama_pengguna'] ?? 'Admin') ?> 👋</h1>
                    <p class="admin-welcome-subtitle">Your Team's Success Starts Here. Let's Make Progress Together!</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Lahan Parkir</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-parking"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= $stats['total_parking'] ?></p>
                    <div class="admin-stat-change">
                        <i class="fas fa-building"></i> <?= $stats['total_providers'] ?> penyedia
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Pengguna</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= $stats['total_users'] ?></p>
                    <div class="admin-stat-change">
                        <i class="fas fa-user-check"></i> Pengguna terdaftar
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Transaksi</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= $stats['total_transactions'] ?></p>
                    <div class="admin-stat-change">
                        <i class="fas fa-calendar-day"></i> <?= $stats['today_transactions'] ?> hari ini
                    </div>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Pendapatan</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= formatRupiah($stats['total_revenue']) ?></p>
                    <div class="admin-stat-change">
                        <i class="fas fa-check-circle"></i> Dari transaksi selesai
                    </div>
                </div>
            </div>

            <!-- Interactive Charts Section -->
            <div class="admin-charts-grid">
                <!-- User Growth Chart -->
                <div class="admin-chart-card">
                    <div class="admin-chart-header">
                        <div>
                            <h3 class="admin-chart-title">Pertumbuhan Pengguna</h3>
                            <p class="admin-chart-subtitle">Total pengguna terdaftar: <strong><?= $stats['total_users'] ?></strong></p>
                        </div>
                        <div class="admin-chart-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="admin-chart-canvas-wrapper">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>

                <!-- Parking Growth Chart -->
                <div class="admin-chart-card">
                    <div class="admin-chart-header">
                        <div>
                            <h3 class="admin-chart-title">Pertumbuhan Lahan Parkir</h3>
                            <p class="admin-chart-subtitle">Total lahan parkir: <strong><?= $stats['total_parking'] ?></strong></p>
                        </div>
                        <div class="admin-chart-icon">
                            <i class="fas fa-parking"></i>
                        </div>
                    </div>
                    <div class="admin-chart-canvas-wrapper">
                        <canvas id="parkingGrowthChart"></canvas>
                    </div>
                </div>

                <!-- Revenue Trend Chart -->
                <div class="admin-chart-card admin-chart-card-full">
                    <div class="admin-chart-header">
                        <div>
                            <h3 class="admin-chart-title">Tren Pendapatan</h3>
                            <p class="admin-chart-subtitle">Total pendapatan: <strong><?= formatRupiah($stats['total_revenue']) ?></strong></p>
                        </div>
                        <div class="admin-chart-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="admin-chart-canvas-wrapper">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Content Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                <!-- Popular Parkings -->
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2 class="admin-table-title">Lahan Parkir Paling Populer</h2>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nama Tempat</th>
                                <th>Total Booking</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($popular_parkings)): ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: var(--spark-text-light);">
                                        Belum ada data
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($popular_parkings as $parking): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASEURL ?>/admin/parking-detail.php?id=<?= $parking['id_tempat'] ?>" 
                                               style="color: var(--spark-yellow);">
                                                <?= htmlspecialchars($parking['nama_tempat']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="admin-badge admin-badge-info">
                                                <?= $parking['total_booking'] ?> booking
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Recent Transactions -->
                <div class="admin-table-container">
                    <div class="admin-table-header">
                        <h2 class="admin-table-title">Transaksi Terbaru</h2>
                        <a href="<?= BASEURL ?>/admin/transactions.php" class="admin-btn admin-btn-sm admin-btn-secondary">
                            Lihat Semua
                        </a>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Tempat</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_transactions)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--spark-text-light);">
                                        Belum ada transaksi
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['nama_pengguna']) ?></td>
                                        <td><?= htmlspecialchars($transaction['nama_tempat']) ?></td>
                                        <td><?= formatRupiah($transaction['total_harga']) ?></td>
                                        <td>
                                            <span class="admin-badge <?= getStatusBadge($transaction['status_booking']) ?>">
                                                <?= ucfirst($transaction['status_booking']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Monthly Statistics Chart -->
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h2 class="admin-table-title">Statistik 6 Bulan Terakhir</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Jumlah Booking</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthly_stats)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--spark-text-light);">
                                    Belum ada data
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthly_stats as $month): ?>
                                <tr>
                                    <td><?= date('F Y', strtotime($month['bulan'] . '-01')) ?></td>
                                    <td><?= $month['jumlah_booking'] ?> booking</td>
                                    <td><?= formatRupiah($month['pendapatan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
// Chart Configuration
const chartColors = {
    yellow: '#FFE100',
    yellowLight: 'rgba(255, 225, 0, 0.2)',
    yellowGradient: 'rgba(255, 225, 0, 0.6)',
    green: '#10b981',
    greenLight: 'rgba(16, 185, 129, 0.2)',
    blue: '#3b82f6',
    blueLight: 'rgba(59, 130, 246, 0.2)',
    text: '#e5e5e5',
    grid: 'rgba(255, 255, 255, 0.05)'
};

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            backgroundColor: 'rgba(26, 26, 26, 0.95)',
            titleColor: chartColors.yellow,
            bodyColor: chartColors.text,
            borderColor: chartColors.yellow,
            borderWidth: 1,
            padding: 12,
            displayColors: false,
            titleFont: {
                size: 13,
                weight: '600'
            },
            bodyFont: {
                size: 12
            }
        }
    },
    scales: {
        x: {
            grid: {
                color: chartColors.grid,
                drawBorder: false
            },
            ticks: {
                color: chartColors.text,
                font: {
                    size: 11
                }
            }
        },
        y: {
            beginAtZero: true,
            grid: {
                color: chartColors.grid,
                drawBorder: false
            },
            ticks: {
                color: chartColors.text,
                font: {
                    size: 11
                }
            }
        }
    }
};

// Prepare data from PHP
const userGrowthData = <?= json_encode(array_map(function($item) {
    return [
        'label' => date('M Y', strtotime($item['bulan'] . '-01')),
        'value' => $item['total']
    ];
}, $cumulative_users)) ?>;

const parkingGrowthData = <?= json_encode(array_map(function($item) {
    return [
        'label' => date('M Y', strtotime($item['bulan'] . '-01')),
        'value' => $item['total']
    ];
}, $cumulative_parking)) ?>;

const revenueData = <?= json_encode(array_map(function($item) {
    return [
        'label' => date('M Y', strtotime($item['bulan'] . '-01')),
        'value' => $item['pendapatan']
    ];
}, $monthly_stats)) ?>;

// User Growth Chart
const userCtx = document.getElementById('userGrowthChart').getContext('2d');
const userGradient = userCtx.createLinearGradient(0, 0, 0, 300);
userGradient.addColorStop(0, chartColors.greenLight);
userGradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

new Chart(userCtx, {
    type: 'line',
    data: {
        labels: userGrowthData.map(d => d.label),
        datasets: [{
            label: 'Total Pengguna',
            data: userGrowthData.map(d => d.value),
            borderColor: chartColors.green,
            backgroundColor: userGradient,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: chartColors.green,
            pointBorderColor: '#1a1a1a',
            pointBorderWidth: 2
        }]
    },
    options: {
        ...defaultOptions,
        plugins: {
            ...defaultOptions.plugins,
            tooltip: {
                ...defaultOptions.plugins.tooltip,
                titleColor: chartColors.green,
                borderColor: chartColors.green,
                callbacks: {
                    label: function(context) {
                        return 'Total: ' + context.parsed.y + ' pengguna';
                    }
                }
            }
        }
    }
});

// Parking Growth Chart
const parkingCtx = document.getElementById('parkingGrowthChart').getContext('2d');
const parkingGradient = parkingCtx.createLinearGradient(0, 0, 0, 300);
parkingGradient.addColorStop(0, chartColors.blueLight);
parkingGradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

new Chart(parkingCtx, {
    type: 'line',
    data: {
        labels: parkingGrowthData.map(d => d.label),
        datasets: [{
            label: 'Total Lahan Parkir',
            data: parkingGrowthData.map(d => d.value),
            borderColor: chartColors.blue,
            backgroundColor: parkingGradient,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: chartColors.blue,
            pointBorderColor: '#1a1a1a',
            pointBorderWidth: 2
        }]
    },
    options: {
        ...defaultOptions,
        plugins: {
            ...defaultOptions.plugins,
            tooltip: {
                ...defaultOptions.plugins.tooltip,
                titleColor: chartColors.blue,
                borderColor: chartColors.blue,
                callbacks: {
                    label: function(context) {
                        return 'Total: ' + context.parsed.y + ' lahan';
                    }
                }
            }
        }
    }
});

// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
revenueGradient.addColorStop(0, chartColors.yellowGradient);
revenueGradient.addColorStop(1, chartColors.yellowLight);

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.label),
        datasets: [{
            label: 'Pendapatan',
            data: revenueData.map(d => d.value),
            borderColor: chartColors.yellow,
            backgroundColor: revenueGradient,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: chartColors.yellow,
            pointBorderColor: '#1a1a1a',
            pointBorderWidth: 3
        }]
    },
    options: {
        ...defaultOptions,
        plugins: {
            ...defaultOptions.plugins,
            tooltip: {
                ...defaultOptions.plugins.tooltip,
                callbacks: {
                    label: function(context) {
                        return 'Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            ...defaultOptions.scales,
            y: {
                ...defaultOptions.scales.y,
                ticks: {
                    ...defaultOptions.scales.y.ticks,
                    callback: function(value) {
                        return 'Rp ' + (value / 1000) + 'k';
                    }
                }
            }
        }
    }
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

