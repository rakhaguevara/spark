<?php
$pageTitle = 'Riwayat Transaksi';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Filter
$filter_status = $_GET['status'] ?? 'all';
$filter_date = $_GET['date'] ?? '';

// Build query
$sql = "
    SELECT 
        bp.*,
        tp.nama_tempat,
        tp.alamat_tempat,
        dp.nama_pengguna,
        dp.email_pengguna,
        sp.nomor_slot,
        pb.metode as metode_pembayaran,
        pb.status as status_pembayaran
    FROM booking_parkir bp
    JOIN tempat_parkir tp ON bp.id_tempat = tp.id_tempat
    JOIN data_pengguna dp ON bp.id_pengguna = dp.id_pengguna
    LEFT JOIN slot_parkir sp ON bp.id_slot = sp.id_slot
    LEFT JOIN pembayaran_booking pb ON bp.id_booking = pb.id_booking
    WHERE 1=1
";

$params = [];

if ($filter_status !== 'all') {
    $sql .= " AND bp.status_booking = ?";
    $params[] = $filter_status;
}

if ($filter_date) {
    $sql .= " AND DATE(bp.created_at) = ?";
    $params[] = $filter_date;
}

$sql .= " ORDER BY bp.created_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_booking = 'completed' THEN total_harga ELSE 0 END) as total_pendapatan,
        SUM(CASE WHEN status_booking = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status_booking = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status_booking = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status_booking = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM booking_parkir
");
$stats = $stmt->fetch();

function formatRupiah($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getStatusBadge($status)
{
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

            <!-- Statistics -->
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Transaksi</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= $stats['total'] ?></p>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Pendapatan</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= formatRupiah($stats['total_pendapatan'] ?? 0) ?></p>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Pending</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= $stats['pending'] ?? 0 ?></p>
                </div>

                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Completed</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= $stats['completed'] ?? 0 ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="admin-table-container" style="margin-bottom: 28px;">
                <div style="padding: 20px 24px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <span style="color: var(--spark-text-light); font-weight: 500;">Filter:</span>
                    <a href="?status=all"
                        class="admin-btn admin-btn-sm <?= $filter_status === 'all' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Semua
                    </a>
                    <a href="?status=pending"
                        class="admin-btn admin-btn-sm <?= $filter_status === 'pending' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Pending
                    </a>
                    <a href="?status=confirmed"
                        class="admin-btn admin-btn-sm <?= $filter_status === 'confirmed' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Confirmed
                    </a>
                    <a href="?status=completed"
                        class="admin-btn admin-btn-sm <?= $filter_status === 'completed' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Completed
                    </a>
                    <a href="?status=cancelled"
                        class="admin-btn admin-btn-sm <?= $filter_status === 'cancelled' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Cancelled
                    </a>
                    <form method="GET" style="display: flex; gap: 8px; margin-left: auto;">
                        <input type="hidden" name="status" value="<?= $filter_status ?>">
                        <input type="date" name="date" value="<?= $filter_date ?>"
                            class="admin-form-input" style="width: auto; padding: 8px 12px;">
                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($filter_date): ?>
                            <a href="?status=<?= $filter_status ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Booking</th>
                            <th>Pengguna</th>
                            <th>Tempat Parkir</th>
                            <th>Slot</th>
                            <th>Waktu</th>
                            <th>Total Harga</th>
                            <th>Status Booking</th>
                            <th>Pembayaran</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="admin-empty-state">
                                        <i class="fas fa-receipt"></i>
                                        <p>Belum ada transaksi</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= str_pad($transaction['id_booking'], 6, '0', STR_PAD_LEFT) ?></strong>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($transaction['nama_pengguna']) ?></div>
                                        <small style="color: var(--spark-text-light);">
                                            <?= htmlspecialchars($transaction['email_pengguna']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($transaction['nama_tempat']) ?></div>
                                        <small style="color: var(--spark-text-light);">
                                            <?= htmlspecialchars(substr($transaction['alamat_tempat'], 0, 30)) ?>...
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['nomor_slot'] ?? '-') ?></td>
                                    <td>
                                        <div><?= date('d/m/Y', strtotime($transaction['waktu_mulai'])) ?></div>
                                        <small style="color: var(--spark-text-light);">
                                            <?= date('H:i', strtotime($transaction['waktu_mulai'])) ?> -
                                            <?= date('H:i', strtotime($transaction['waktu_selesai'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= formatRupiah($transaction['total_harga']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="admin-badge <?= getStatusBadge($transaction['status_booking']) ?>">
                                            <?= ucfirst($transaction['status_booking']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($transaction['metode_pembayaran']): ?>
                                            <div><?= htmlspecialchars($transaction['metode_pembayaran']) ?></div>
                                            <span class="admin-badge <?= $transaction['status_pembayaran'] === 'success' ? 'admin-badge-success' : 'admin-badge-warning' ?>">
                                                <?= ucfirst($transaction['status_pembayaran'] ?? 'pending') ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--spark-text-light);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small style="color: var(--spark-text-light);">
                                            <?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?>
                                        </small>
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