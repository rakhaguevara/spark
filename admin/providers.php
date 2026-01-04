<?php
$pageTitle = 'Manajemen Penyedia Lahan';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'toggle_status' && isset($_POST['id_pengguna'])) {
            // Here you can add logic to enable/disable provider
            // For now, we'll just show a message
            $_SESSION['success'] = 'Status penyedia lahan berhasil diubah';
            header('Location: ' . BASEURL . '/admin/providers.php');
            exit;
        }
    }
}

// Get all providers with their parking places count
$stmt = $pdo->query("
    SELECT 
        dp.id_pengguna,
        dp.nama_pengguna,
        dp.email_pengguna,
        dp.noHp_pengguna,
        dp.created_at,
        COUNT(DISTINCT tp.id_tempat) as total_lahan,
        SUM(tp.total_spot) as total_slot,
        COUNT(DISTINCT sp.id_slot) as slot_terpakai,
        COUNT(DISTINCT bp.id_booking) as total_booking,
        COALESCE(SUM(CASE WHEN bp.status_booking = 'completed' THEN bp.total_harga ELSE 0 END), 0) as total_pendapatan
    FROM data_pengguna dp
    JOIN tempat_parkir tp ON dp.id_pengguna = tp.id_pemilik
    LEFT JOIN slot_parkir sp ON tp.id_tempat = sp.id_tempat AND sp.status_slot = 'booked'
    LEFT JOIN booking_parkir bp ON tp.id_tempat = bp.id_tempat
    GROUP BY dp.id_pengguna
    ORDER BY total_lahan DESC, dp.created_at DESC
");
$providers = $stmt->fetchAll();

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
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
            
            <!-- Header -->
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-section-title">Daftar Penyedia Lahan Parkir</h2>
                    <p class="admin-info-text" style="margin-top: 8px;">
                        Kelola dan kontrol penyedia lahan parkir yang terdaftar
                    </p>
                </div>
            </div>
            
            <!-- Providers List -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama Penyedia</th>
                            <th>Kontak</th>
                            <th>Total Lahan</th>
                            <th>Total Slot</th>
                            <th>Slot Terpakai</th>
                            <th>Total Booking</th>
                            <th>Total Pendapatan</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($providers)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="admin-empty-state">
                                        <i class="fas fa-building"></i>
                                        <p>Belum ada penyedia lahan terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($providers as $provider): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($provider['nama_pengguna']) ?></strong>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($provider['email_pengguna']) ?></div>
                                        <small style="color: var(--spark-text-light);">
                                            <?= htmlspecialchars($provider['noHp_pengguna']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-info">
                                            <?= $provider['total_lahan'] ?> lahan
                                        </span>
                                    </td>
                                    <td><?= $provider['total_slot'] ?? 0 ?></td>
                                    <td>
                                        <span class="admin-badge admin-badge-warning">
                                            <?= $provider['slot_terpakai'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-success">
                                            <?= $provider['total_booking'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= formatRupiah($provider['total_pendapatan']) ?></strong>
                                    </td>
                                    <td>
                                        <small style="color: var(--spark-text-light);">
                                            <?= date('d/m/Y', strtotime($provider['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="admin-actions-group">
                                            <a href="<?= BASEURL ?>/admin/users.php?provider_id=<?= $provider['id_pengguna'] ?>" 
                                               class="admin-btn admin-btn-sm admin-btn-secondary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Stats -->
            <div class="admin-stats" style="margin-top: 32px;">
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Penyedia</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value"><?= count($providers) ?></p>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Lahan</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-parking"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value">
                        <?= array_sum(array_column($providers, 'total_lahan')) ?>
                    </p>
                </div>
                
                <div class="admin-stat-card">
                    <div class="admin-stat-header">
                        <h3 class="admin-stat-title">Total Pendapatan</h3>
                        <div class="admin-stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <p class="admin-stat-value">
                        <?= formatRupiah(array_sum(array_column($providers, 'total_pendapatan'))) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

