<?php
/**
 * MY TICKET PAGE (FIXED FOR EXISTING SCHEMA)
 * Displays active parking tickets using booking_parkir + qr_session
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASEURL . '/pages/login.php');
    exit;
}

// Get user
$user = getCurrentUser();

// Helper function to get first 2 words of name
function getShortName($fullName) {
    $words = explode(' ', trim($fullName));
    $shortName = implode(' ', array_slice($words, 0, 2));
    return $shortName;
}

// Fetch active bookings with QR tokens
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT 
        b.id_booking,
        b.waktu_mulai,
        b.waktu_selesai,
        b.total_harga,
        b.status_booking,
        b.created_at,
        q.qr_token,
        q.expires_at,
        t.nama_tempat,
        t.alamat_tempat,
        jk.nama_jenis,
        k.plat_hint
    FROM booking_parkir b
    LEFT JOIN qr_session q ON b.id_booking = q.id_booking
    JOIN tempat_parkir t ON b.id_tempat = t.id_tempat
    JOIN kendaraan_pengguna k ON b.id_kendaraan = k.id_kendaraan
    JOIN jenis_kendaraan jk ON k.id_jenis = jk.id_jenis
    WHERE b.id_pengguna = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user['id_pengguna']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active booking (first confirmed)
$activeBooking = null;
foreach ($bookings as $booking) {
    if ($booking['status_booking'] === 'confirmed') {
        $activeBooking = $booking;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>My Ticket | SPARK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dashboard-user.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/my-ticket.css">
</head>

<body>
    <!-- NAVBAR -->
    <nav class="dashboard-navbar">
        <a href="<?= BASEURL ?>/pages/dashboard.php" class="brand-wrapper">
            <img src="<?= BASEURL ?>/assets/img/logo.png" alt="Spark Logo">
            SPARK
        </a>

        <div class="search-bar">
            <input type="text" placeholder="Search tickets...">
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

    <!-- MAIN CONTAINER -->
    <div class="dashboard-container">
        <!-- SIDEBAR -->
        <aside class="dashboard-sidebar" id="sidebar">
            <button class="sidebar-toggle-edge" id="sidebarToggle" aria-label="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>

            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li>
                        <a href="<?= BASEURL ?>/pages/dashboard.php" data-tooltip="Find Parking">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Find Parking</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/my-ticket.php" class="active" data-tooltip="My Ticket">
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
                        <a href="<?= BASEURL ?>/pages/wallet.php" data-tooltip="Wallet">
                            <i class="fas fa-wallet"></i>
                            <span>Wallet</span>
                        </a>
                    </li>
                </ul>
            </nav>

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
        <main class="ticket-main">
            <div class="ticket-header">
                <h1>My Tickets</h1>
                <p>View and manage your parking tickets</p>
            </div>

            <?php if ($activeBooking): ?>
                <!-- ACTIVE TICKET -->
                <div class="active-ticket-container">
                    <div class="ticket-badge">Active Ticket</div>
                    
                    <div class="ticket-card active">
                        <div class="ticket-qr-section">
                            <div class="qr-container" id="qrContainer">
                                <?php if (!empty($activeBooking['qr_token'])): ?>
                                    <img id="qrImage" 
                                         src="<?= BASEURL ?>/api/generate-qr-image.php?booking_id=<?= $activeBooking['id_booking'] ?>&t=<?= time() ?>" 
                                         alt="QR Code" 
                                         style="width: 280px; height: 280px; border: 2px solid #e5e7eb; border-radius: 8px;"
                                         onerror="this.style.display='none'; document.getElementById('qrError').style.display='block';">
                                    <div id="qrError" style="display: none; padding: 20px; background: #f8d7da; border-radius: 8px; text-align: center;">
                                        <p style="color: #721c24; margin: 0;">QR Code generation failed</p>
                                        <p style="font-size: 12px; margin-top: 8px; color: #666;">Booking ID: <?= $activeBooking['id_booking'] ?></p>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 20px; background: #fff3cd; border-radius: 8px; text-align: center;">
                                        <p style="color: #856404; margin: 0;">No QR token available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="qr-hint">Scan this QR code at parking entrance</p>
                            <?php if (!empty($activeBooking['qr_token'])): ?>
                                <div style="text-align: center; margin-top: 10px;">
                                    <div style="display: inline-block; padding: 8px 16px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bfdbfe;">
                                        <i class="fas fa-sync-alt" style="color: #3b82f6; margin-right: 6px;"></i>
                                        <span style="color: #1e40af; font-weight: 500;">Refreshing in <span id="qrTimer" style="font-weight: 700; color: #2563eb;">10</span>s</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="ticket-details">
                            <div class="detail-row">
                                <span class="label">Booking ID</span>
                                <span class="value">#<?= htmlspecialchars($activeBooking['id_booking']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Status</span>
                                <span class="status-badge <?= $activeBooking['status_booking'] ?>">
                                    <?= ucfirst($activeBooking['status_booking']) ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Parking Location</span>
                                <span class="value"><?= htmlspecialchars($activeBooking['nama_tempat']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Vehicle Type</span>
                                <span class="value"><?= htmlspecialchars($activeBooking['nama_jenis']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Plate Number</span>
                                <span class="value">***<?= htmlspecialchars($activeBooking['plat_hint']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Start Time</span>
                                <span class="value">
                                    <?php 
                                    if (!empty($activeBooking['waktu_mulai']) && strtotime($activeBooking['waktu_mulai']) > 0) {
                                        echo date('d M Y, H:i', strtotime($activeBooking['waktu_mulai']));
                                    } else {
                                        echo 'Invalid date';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="label">End Time</span>
                                <span class="value">
                                    <?php 
                                    if (!empty($activeBooking['waktu_selesai']) && strtotime($activeBooking['waktu_selesai']) > 0) {
                                        echo date('d M Y, H:i', strtotime($activeBooking['waktu_selesai']));
                                    } else {
                                        echo 'Invalid date';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>No Active Tickets</h3>
                    <p>You don't have any active parking tickets</p>
                    <a href="<?= BASEURL ?>/pages/dashboard.php" class="btn-primary">Find Parking</a>
                </div>
            <?php endif; ?>

            <!-- TICKET HISTORY -->
            <?php if (count($bookings) > 0): ?>
            <div class="ticket-history">
                <h2>Booking History</h2>
                <div class="history-list">
                    <?php foreach ($bookings as $booking): ?>
                        <?php if ($booking['id_booking'] === ($activeBooking['id_booking'] ?? null)) continue; ?>
                        <div class="ticket-card history">
                            <div class="ticket-summary">
                                <div>
                                    <h4><?= htmlspecialchars($booking['nama_tempat']) ?></h4>
                                    <p><?= htmlspecialchars($booking['nama_jenis']) ?> • ***<?= htmlspecialchars($booking['plat_hint']) ?></p>
                                </div>
                                <span class="status-badge <?= $booking['status_booking'] ?>">
                                    <?= ucfirst($booking['status_booking']) ?>
                                </span>
                            </div>
                            <div class="ticket-meta">
                                <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($booking['created_at'])) ?></span>
                                <span>#<?= $booking['id_booking'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // QR Code Auto-Refresh System
        const BASEURL = '<?= BASEURL ?>';
        const BOOKING_ID = '<?= $activeBooking['id_booking'] ?? '' ?>';
        const REFRESH_INTERVAL = 10000; // 10 seconds
        
        let countdown = 10;
        let refreshTimer = null;
        let countdownTimer = null;
        
        function updateCountdown() {
            const timerElement = document.getElementById('qrTimer');
            if (timerElement) {
                timerElement.textContent = countdown;
                countdown--;
                
                if (countdown < 0) {
                    countdown = 10;
                }
            }
        }
        
        function refreshQRCode() {
            if (!BOOKING_ID) return;
            
            // Refresh token on server
            fetch(BASEURL + '/api/refresh-qr-token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'booking_id=' + BOOKING_ID
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update QR image with cache buster
                    const qrImage = document.getElementById('qrImage');
                    if (qrImage) {
                        qrImage.src = BASEURL + '/api/generate-qr-image.php?booking_id=' + BOOKING_ID + '&t=' + Date.now();
                    }
                    
                    // Reset countdown
                    countdown = 10;
                } else {
                    console.error('QR refresh failed:', data.error);
                }
            })
            .catch(error => {
                console.error('QR refresh error:', error);
            });
        }
        
        // Start auto-refresh if booking exists
        if (BOOKING_ID) {
            // Update countdown every second
            countdownTimer = setInterval(updateCountdown, 1000);
            
            // Refresh QR every 10 seconds
            refreshTimer = setInterval(refreshQRCode, REFRESH_INTERVAL);
            
            console.log('QR auto-refresh started for booking:', BOOKING_ID);
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshTimer) clearInterval(refreshTimer);
            if (countdownTimer) clearInterval(countdownTimer);
        });
    </script>
    <script src="<?= BASEURL ?>/assets/js/sidebar-toggle.js"></script>
</body>
</html>
