<?php
$pageTitle = 'Manajemen Lahan Parkir';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && isset($_POST['id_tempat'])) {
            $id = (int)$_POST['id_tempat'];
            try {
                // Delete slots first
                $stmt = $pdo->prepare("DELETE FROM slot_parkir WHERE id_tempat = ?");
                $stmt->execute([$id]);

                // Delete parking place
                $stmt = $pdo->prepare("DELETE FROM tempat_parkir WHERE id_tempat = ?");
                $stmt->execute([$id]);

                $_SESSION['success'] = 'Lahan parkir berhasil dihapus';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Gagal menghapus lahan parkir';
            }
            header('Location: ' . BASEURL . '/admin/parking.php');
            exit;
        }
    }
}

// Get all parking places with provider info
$stmt = $pdo->query("
    SELECT 
        tp.*,
        dp.nama_pengguna as nama_pemilik,
        dp.email_pengguna as email_pemilik,
        COUNT(DISTINCT sp.id_slot) as total_slot,
        SUM(CASE WHEN sp.status_slot = 'available' THEN 1 ELSE 0 END) as slot_tersedia,
        COUNT(DISTINCT bp.id_booking) as total_booking
    FROM tempat_parkir tp
    LEFT JOIN data_pengguna dp ON tp.id_pemilik = dp.id_pengguna
    LEFT JOIN slot_parkir sp ON tp.id_tempat = sp.id_tempat
    LEFT JOIN booking_parkir bp ON tp.id_tempat = bp.id_tempat
    GROUP BY tp.id_tempat
    ORDER BY tp.created_at DESC
");
$parkings = $stmt->fetchAll();

function formatRupiah($amount)
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatTime($time)
{
    return date('H:i', strtotime($time));
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

            <!-- Header Actions -->
            <div class="admin-section-header">
                <h2 class="admin-section-title">Daftar Lahan Parkir</h2>
                <div class="admin-section-actions">
                    <a href="<?= BASEURL ?>/admin/parking-detail.php?action=add" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus"></i> Tambah Lahan Parkir
                    </a>
                </div>
            </div>

            <!-- Parking List -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama Tempat</th>
                            <th>Penyedia</th>
                            <th>Alamat</th>
                            <th>Slot</th>
                            <th>Harga/Jam</th>
                            <th>Jam Operasional</th>
                            <th>Total Booking</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($parkings)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="admin-empty-state">
                                        <i class="fas fa-parking"></i>
                                        <p>Belum ada lahan parkir terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($parkings as $parking): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($parking['nama_tempat']) ?></strong>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($parking['nama_pemilik'] ?? '-') ?></div>
                                        <small style="color: var(--spark-text-light);">
                                            <?= htmlspecialchars($parking['email_pemilik'] ?? '-') ?>
                                        </small>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <?= htmlspecialchars(substr($parking['alamat_tempat'], 0, 50)) ?>
                                        <?= strlen($parking['alamat_tempat']) > 50 ? '...' : '' ?>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-info">
                                            <?= $parking['slot_tersedia'] ?? 0 ?>/<?= $parking['total_slot'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td><?= formatRupiah($parking['harga_per_jam']) ?></td>
                                    <td>
                                        <?= formatTime($parking['jam_buka']) ?> - <?= formatTime($parking['jam_tutup']) ?>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-success">
                                            <?= $parking['total_booking'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-actions-group">
                                            <a href="<?= BASEURL ?>/admin/parking-detail.php?id=<?= $parking['id_tempat'] ?>"
                                                class="admin-btn admin-btn-sm admin-btn-secondary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST" style="display: inline;"
                                                onsubmit="return confirm('Yakin ingin menghapus lahan parkir ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_tempat" value="<?= $parking['id_tempat'] ?>">
                                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger" title="Hapus">
                                        </div>
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