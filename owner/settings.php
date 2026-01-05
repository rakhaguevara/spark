<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/owner-auth.php';

requireOwnerLogin();

$owner = getCurrentOwner();
$pdo = getDBConnection();
$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $password_baru = $_POST['password_baru'] ?? '';

    if (!$nama || !$email || !$no_hp) {
        $message = 'Semua field wajib diisi';
        $message_type = 'error';
    } else {
        try {
            // Check email uniqueness (if changed)
            if ($email !== $owner['email_pengguna']) {
                $stmt = $pdo->prepare("SELECT id_pengguna FROM data_pengguna WHERE email_pengguna = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $message = 'Email sudah terdaftar';
                    $message_type = 'error';
                    goto finish;
                }
            }

            // Update basic info
            $stmt = $pdo->prepare("UPDATE data_pengguna SET nama_pengguna = ?, email_pengguna = ?, noHp_pengguna = ? WHERE id_pengguna = ?");
            $stmt->execute([$nama, $email, $no_hp, $owner['id_pengguna']]);

            // Update password if provided
            if (!empty($password_baru)) {
                if (strlen($password_baru) < 6) {
                    $message = 'Password minimal 6 karakter';
                    $message_type = 'error';
                    goto finish;
                }
                $hashed = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE data_pengguna SET password_pengguna = ? WHERE id_pengguna = ?");
                $stmt->execute([$hashed, $owner['id_pengguna']]);
            }

            $message = 'Profil berhasil diperbarui';
            $message_type = 'success';

            // Refresh owner data
            $owner = getCurrentOwner();
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }

    finish:
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan | SPARK</title>

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
                <li><a href="<?= BASEURL ?>/owner/scan-history.php">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a></li>

                <li class="divider"></li>
                <li><a href="<?= BASEURL ?>/owner/settings.php" class="active">
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
                <h1>Pengaturan Akun</h1>
                <p>Kelola informasi profil dan keamanan akun Anda</p>
            </div>

            <div class="settings-container">
                <?php if ($message): ?>
                    <div class="alert <?= $message_type ?>">
                        <i class="fas fa-<?= ($message_type === 'success' ? 'check-circle' : 'exclamation-circle') ?>"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="settings-card">
                    <div class="settings-title">
                        <i class="fas fa-user"></i>
                        Profil Pengguna
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= htmlspecialchars($owner['nama_pengguna']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($owner['email_pengguna']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="tel" name="no_hp" value="<?= htmlspecialchars($owner['noHp_pengguna'] ?? '') ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <div class="settings-card">
                    <div class="settings-title">
                        <i class="fas fa-lock"></i>
                        Keamanan Akun
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" name="password_baru" placeholder="Masukkan password baru">
                            <p class="form-help">Minimal 6 karakter</p>
                        </div>

                        <input type="hidden" name="nama" value="<?= htmlspecialchars($owner['nama_pengguna']) ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($owner['email_pengguna']) ?>">
                        <input type="hidden" name="no_hp" value="<?= htmlspecialchars($owner['noHp_pengguna'] ?? '') ?>">

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </form>
                </div>

                <div class="settings-card" style="background: rgba(231,76,60,0.05); border-left: 4px solid var(--danger);">
                    <div class="settings-title" style="color: var(--danger);">
                        <i class="fas fa-sign-out-alt"></i>
                        Keluar
                    </div>
                    <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                        Logout dari akun Anda dan kembali ke halaman login.
                    </p>
                    <a href="<?= BASEURL ?>/owner/logout.php" class="btn" style="background: var(--danger); color: white;">
                        <i class="fas fa-sign-out-alt"></i> Logout Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>