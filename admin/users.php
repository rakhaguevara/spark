<?php
$pageTitle = 'Data Akun';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && isset($_POST['id_pengguna'])) {
            $id = (int)$_POST['id_pengguna'];
            try {
                // Check if user has parking places
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tempat_parkir WHERE id_pemilik = ?");
                $stmt->execute([$id]);
                $hasParking = $stmt->fetch()['count'] > 0;
                
                if ($hasParking) {
                    $_SESSION['error'] = 'Tidak dapat menghapus pengguna yang memiliki lahan parkir';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM data_pengguna WHERE id_pengguna = ? AND role_pengguna != 2");
                    $stmt->execute([$id]);
                    $_SESSION['success'] = 'Akun berhasil dihapus';
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Gagal menghapus akun';
            }
            header('Location: ' . BASEURL . '/admin/users.php');
            exit;
        }
    }
}

// Filter by role
$filter_role = $_GET['role'] ?? 'all';
$provider_id = isset($_GET['provider_id']) ? (int)$_GET['provider_id'] : 0;

// Build query
$sql = "
    SELECT 
        dp.*,
        rp.nama_role,
        rp.id_role,
        COUNT(DISTINCT tp.id_tempat) as total_lahan,
        COUNT(DISTINCT bp.id_booking) as total_booking,
        COUNT(DISTINCT kp.id_kendaraan) as total_kendaraan
    FROM data_pengguna dp
    JOIN role_pengguna rp ON dp.role_pengguna = rp.id_role
    LEFT JOIN tempat_parkir tp ON dp.id_pengguna = tp.id_pemilik
    LEFT JOIN booking_parkir bp ON dp.id_pengguna = bp.id_pengguna
    LEFT JOIN kendaraan_pengguna kp ON dp.id_pengguna = kp.id_pengguna
    WHERE 1=1
";

$params = [];

// Filter by role - pastikan hanya filter role yang valid
if ($filter_role !== 'all' && in_array($filter_role, ['1', '2'])) {
    $filter_role_int = (int)$filter_role;
    $sql .= " AND dp.role_pengguna = ?";
    $params[] = $filter_role_int;
}

if ($provider_id > 0) {
    $sql .= " AND dp.id_pengguna = ?";
    $params[] = $provider_id;
}

$sql .= " GROUP BY dp.id_pengguna ORDER BY dp.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get role counts
$stmt = $pdo->query("
    SELECT 
        rp.id_role,
        rp.nama_role,
        COUNT(dp.id_pengguna) as total
    FROM role_pengguna rp
    LEFT JOIN data_pengguna dp ON rp.id_role = dp.role_pengguna
    GROUP BY rp.id_role
    ORDER BY rp.id_role
");
$role_counts = $stmt->fetchAll();

// Create associative array for easier access
$role_counts_map = [];
foreach ($role_counts as $rc) {
    $role_counts_map[$rc['id_role']] = $rc['total'];
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
                    <h2 class="admin-section-title">Data Akun Pengguna</h2>
                    <p class="admin-info-text" style="margin-top: 8px;">
                        Kelola data akun pengguna dan penyedia lahan parkir
                    </p>
                </div>
                
                <!-- Filter -->
                <div class="admin-section-actions">
                    <a href="?role=all" 
                       class="admin-btn admin-btn-sm <?= $filter_role === 'all' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Semua
                    </a>
                    <a href="?role=1" 
                       class="admin-btn admin-btn-sm <?= $filter_role === '1' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        User (<?= $role_counts_map[1] ?? 0 ?>)
                    </a>
                    <a href="?role=2" 
                       class="admin-btn admin-btn-sm <?= $filter_role === '2' ? 'admin-btn-primary' : 'admin-btn-secondary' ?>">
                        Admin (<?= $role_counts_map[2] ?? 0 ?>)
                    </a>
                </div>
            </div>
            
            <!-- Users List -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Role</th>
                            <th>Lahan</th>
                            <th>Booking</th>
                            <th>Kendaraan</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="admin-empty-state">
                                        <i class="fas fa-users"></i>
                                        <p>Belum ada pengguna terdaftar</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($user['nama_pengguna']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email_pengguna']) ?></td>
                                    <td><?= htmlspecialchars($user['noHp_pengguna']) ?></td>
                                    <td>
                                        <span class="admin-badge <?= $user['role_pengguna'] == 2 ? 'admin-badge-danger' : ($user['total_lahan'] > 0 ? 'admin-badge-info' : 'admin-badge-success') ?>">
                                            <?= ucfirst($user['nama_role']) ?>
                                            <?= $user['total_lahan'] > 0 ? ' (Penyedia)' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['total_lahan'] > 0): ?>
                                            <a href="<?= BASEURL ?>/admin/parking.php?provider=<?= $user['id_pengguna'] ?>" 
                                               style="color: var(--spark-yellow);">
                                                <?= $user['total_lahan'] ?> lahan
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-info">
                                            <?= $user['total_booking'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-success">
                                            <?= $user['total_kendaraan'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small style="color: var(--spark-text-light);">
                                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($user['role_pengguna'] != 2): ?>
                                            <div class="admin-actions-group">
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id_pengguna" value="<?= $user['id_pengguna'] ?>">
                                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--spark-text-light);">-</span>
                                        <?php endif; ?>
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

