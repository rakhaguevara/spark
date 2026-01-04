<?php
/**
 * WALLET PAGE
 * Manage saved payment methods (storage only, no payment processing)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

if (!isLoggedIn()) {
    header('Location: ' . BASEURL . '/pages/login.php');
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

// Helper function to get first 2 words of name
function getShortName($fullName) {
    $words = explode(' ', trim($fullName));
    $shortName = implode(' ', array_slice($words, 0, 2));
    return $shortName;
}

// Fetch saved payment methods
$stmt = $pdo->prepare("
    SELECT 
        id_wallet,
        type,
        provider_name,
        account_identifier,
        is_default,
        created_at
    FROM wallet_methods
    WHERE id_pengguna = ?
    ORDER BY is_default DESC, created_at DESC
");
$stmt->execute([$user['id_pengguna']]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet | SPARK</title>
    
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dashboard-user.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/wallet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="dashboard-navbar">
        <a href="<?= BASEURL ?>/pages/dashboard.php" class="brand-wrapper">
            <img src="<?= BASEURL ?>/assets/img/logo.png" alt="Spark Logo">
            SPARK
        </a>

        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search payment methods...">
        </div>

        <div class="user-actions">
            <button class="icon-btn" title="Notifications"><i class="fas fa-bell"></i></button>
            <div class="profile-chip">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?= BASEURL ?>/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($user['nama_pengguna'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <span><?= htmlspecialchars(getShortName($user['nama_pengguna'] ?? 'User')) ?></span>
            </div>
        </div>
    </nav>

    <!-- DASHBOARD CONTAINER -->
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <aside class="dashboard-sidebar" id="sidebar">
            <!-- Toggle Button (Centered on Edge) -->
            <button class="sidebar-toggle-edge" id="sidebarToggle" aria-label="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>

            <!-- Main Navigation -->
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li>
                        <a href="<?= BASEURL ?>/pages/dashboard.php" data-tooltip="Find Parking">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Find Parking</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/my-ticket.php" data-tooltip="My Ticket">
                            <i class="fas fa-ticket-alt"></i>
                            <span>My Ticket</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/history.php" data-tooltip="History">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/wallet.php" class="active" data-tooltip="Wallet">
                            <i class="fas fa-wallet"></i>
                            <span>Wallet</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Bottom Section (Settings + Logout) -->
            <div class="sidebar-bottom">
                <a href="<?= BASEURL ?>/pages/profile.php" data-tooltip="Settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="<?= BASEURL ?>/logout.php" class="logout-link" data-tooltip="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">
        <div class="wallet-container">
            <div class="wallet-header">
                <div>
                    <h1>Wallet</h1>
                    <p class="subtitle">Manage your payment methods</p>
                </div>
            </div>

            <?php if (empty($payment_methods)): ?>
                <!-- PAYMENT LOGOS SLIDER -->
                <div class="payment-logos-slider">
                    <div class="payment-logos-track">
                        <i class="fab fa-cc-mastercard payment-logo"></i>
                        <i class="fas fa-wallet payment-logo"></i>
                        <i class="fab fa-apple-pay payment-logo"></i>
                        <i class="fab fa-ethereum payment-logo"></i>
                        <!-- Duplicate for seamless loop -->
                        <i class="fab fa-cc-mastercard payment-logo"></i>
                        <i class="fas fa-wallet payment-logo"></i>
                        <i class="fab fa-apple-pay payment-logo"></i>
                        <i class="fab fa-ethereum payment-logo"></i>
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h2>No payment methods added yet</h2>
                    <p>Add a method to speed up future bookings!</p>
                    <button class="btn-primary" onclick="openAddPaymentModal()">
                        <i class="fas fa-plus"></i>
                        Add Payment Method
                    </button>
                </div>
            <?php else: ?>
                <!-- PAYMENT METHODS GRID -->
                <div class="payment-methods-grid">
                    <?php foreach ($payment_methods as $method): 
                        $iconClass = match($method['type']) {
                            'bank' => 'fa-university',
                            'ewallet' => 'fa-mobile-alt',
                            'paypal' => 'fa-paypal',
                            default => 'fa-credit-card'
                        };
                    ?>
                        <div class="payment-card" data-id="<?= $method['id_wallet'] ?>">
                            <div class="card-header">
                                <div class="provider-info">
                                    <div class="provider-icon">
                                        <i class="fas <?= $iconClass ?>"></i>
                                    </div>
                                    <div>
                                        <h3><?= htmlspecialchars($method['provider_name']) ?></h3>
                                        <p class="account-number"><?= htmlspecialchars($method['account_identifier']) ?></p>
                                    </div>
                                </div>
                                <?php if ($method['is_default']): ?>
                                    <span class="default-badge">Default</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-actions">
                                <?php if (!$method['is_default']): ?>
                                    <button class="btn-action" onclick="setDefaultPayment(<?= $method['id_wallet'] ?>)">
                                        <i class="fas fa-star"></i>
                                        Set as Default
                                    </button>
                                <?php endif; ?>
                                <button class="btn-action btn-danger" onclick="removePayment(<?= $method['id_wallet'] ?>)">
                                    <i class="fas fa-trash"></i>
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    </div><!-- End dashboard-container -->

    <!-- ADD PAYMENT MODAL -->
    <div class="modal" id="addPaymentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Payment Method</h2>
                <button class="modal-close" onclick="closeAddPaymentModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="addPaymentForm">
                    <!-- Step 1: Select Type -->
                    <div class="form-step active" id="step1">
                        <label>Select Payment Type</label>
                        <div class="payment-type-grid">
                            <button type="button" class="payment-type-btn" onclick="selectType('bank')">
                                <i class="fas fa-university"></i>
                                <span>Bank Transfer</span>
                            </button>
                            <button type="button" class="payment-type-btn" onclick="selectType('ewallet')">
                                <i class="fas fa-mobile-alt"></i>
                                <span>E-Wallet</span>
                            </button>
                            <button type="button" class="payment-type-btn" onclick="selectType('paypal')">
                                <i class="fab fa-paypal"></i>
                                <span>PayPal</span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Enter Details -->
                    <div class="form-step" id="step2">
                        <input type="hidden" name="type" id="paymentType">
                        
                        <div class="form-group">
                            <label>Provider Name</label>
                            <input type="text" name="provider_name" id="providerName" required placeholder="e.g., BCA, DANA, PayPal">
                        </div>

                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="account_number" id="accountNumber" required placeholder="Will be masked for security">
                            <small>Only the last 4 digits will be visible</small>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="backToStep1()">Back</button>
                            <button type="submit" class="btn-primary">Save Payment Method</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= BASEURL ?>/assets/js/sidebar-toggle.js"></script>
    <script src="<?= BASEURL ?>/assets/js/wallet.js"></script>
</body>
</html>
