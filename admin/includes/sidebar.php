<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <div class="admin-sidebar-logo">
            <img src="<?= BASEURL ?>/assets/img/logo.png" alt="SPARK Logo" width="48" height="48">
            <h2>SPARK Admin</h2>
        </div>
    </div>

    <nav>
        <ul class="admin-menu">
            <li class="admin-menu-item">
                <a href="<?= BASEURL ?>/admin/dashboard.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-th-large admin-menu-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?= BASEURL ?>/admin/parking.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'parking.php' ? 'active' : '' ?>">
                    <i class="fas fa-parking admin-menu-icon"></i>
                    <span>Lahan Parkir</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?= BASEURL ?>/admin/providers.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'providers.php' ? 'active' : '' ?>">
                    <i class="fas fa-building admin-menu-icon"></i>
                    <span>Penyedia Lahan</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?= BASEURL ?>/admin/users.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users admin-menu-icon"></i>
                    <span>Data Akun</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?= BASEURL ?>/admin/transactions.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : '' ?>">
                    <i class="fas fa-receipt admin-menu-icon"></i>
                    <span>Riwayat Transaksi</span>
                </a>
            </li>
            <li class="admin-menu-item">
                <a href="<?= BASEURL ?>/admin/statistics.php" class="admin-menu-link <?= basename($_SERVER['PHP_SELF']) == 'statistics.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line admin-menu-icon"></i>
                    <span>Statistik</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>