<nav class="admin-navbar">
    <div style="display: flex; align-items: center; gap: 16px;">
        <img src="<?= BASEURL ?>/assets/img/logo.png" alt="SPARK" width="32" height="32" style="object-fit: contain;">
        <h1 class="admin-navbar-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
    </div>
    <div class="admin-navbar-user">
        <div class="admin-user-info">
            <div class="admin-user-name"><?= htmlspecialchars($admin['nama_pengguna'] ?? 'Admin') ?></div>
            <div class="admin-user-role">Administrator</div>
        </div>
        <a href="<?= BASEURL ?>/admin/logout.php" class="admin-logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>