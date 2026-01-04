<?php
/**
 * HISTORY PAGE
 * Shows past parking bookings (completed, cancelled, or expired)
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

// Fetch past bookings with slot information
$stmt = $pdo->prepare("
    SELECT 
        b.id_booking,
        b.waktu_mulai,
        b.waktu_selesai,
        b.total_harga,
        b.status_booking,
        b.created_at,
        t.nama_tempat,
        t.alamat_tempat,
        j.nama_jenis,
        k.plat_hint,
        s.nomor_slot
    FROM booking_parkir b
    INNER JOIN tempat_parkir t ON b.id_tempat = t.id_tempat
    INNER JOIN kendaraan_pengguna k ON b.id_kendaraan = k.id_kendaraan
    INNER JOIN jenis_kendaraan j ON k.id_jenis = j.id_jenis
    LEFT JOIN slot_parkir s ON b.id_slot = s.id_slot
    WHERE b.id_pengguna = ?
    AND (
        b.status_booking = 'completed'
        OR b.status_booking = 'cancelled'
        OR (b.status_booking = 'confirmed' AND b.waktu_selesai < NOW())
    )
    ORDER BY b.created_at DESC
");
$stmt->execute([$user['id_pengguna']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History | SPARK</title>
    
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dashboard-user.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/history.css">
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
            <input type="text" placeholder="Search parking history...">
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
                        <a href="<?= BASEURL ?>/pages/history.php" class="active" data-tooltip="History">
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
        <div class="history-container">
            <div class="history-header">
                <h1>Booking History</h1>
                <p class="subtitle">Your past parking activities</p>
            </div>

            <?php if (empty($history)): ?>
                <!-- EMPTY STATE -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h2>You haven't parked with SPARK yet</h2>
                    <p>Start by finding parking near you!</p>
                    <a href="<?= BASEURL ?>/pages/dashboard.php" class="btn-primary">
                        <i class="fas fa-map-marked-alt"></i>
                        Find Parking
                    </a>
                </div>
            <?php else: ?>
                <!-- HISTORY CARDS -->
                <div class="history-grid">
                    <?php foreach ($history as $booking): 
                    // Validate dates before processing
                    if (empty($booking['waktu_mulai']) || empty($booking['waktu_selesai'])) {
                        continue; // Skip invalid bookings
                    }
                    
                    try {
                        $start = new DateTime($booking['waktu_mulai']);
                        $end = new DateTime($booking['waktu_selesai']);
                        $duration = $start->diff($end);
                        
                        // Calculate total hours correctly
                        $totalHours = ($duration->days * 24) + $duration->h;
                        
                        // If duration is 0, show minutes instead
                        if ($totalHours == 0 && $duration->i > 0) {
                            $durationText = $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
                        } else {
                            $durationText = $totalHours . ' hour' . ($totalHours != 1 ? 's' : '');
                        }
                        
                        $statusClass = $booking['status_booking'] === 'completed' ? 'completed' : 'cancelled';
                        $statusText = $booking['status_booking'] === 'completed' ? 'Completed' : 'Cancelled';
                    } catch (Exception $e) {
                        continue; // Skip if date parsing fails
                    }
                ?>
                        <div class="history-card">
                            <div class="card-header">
                                <div class="location-info">
                                    <h3><?= htmlspecialchars($booking['nama_tempat']) ?></h3>
                                    <p class="address"><?= htmlspecialchars($booking['alamat_tempat']) ?></p>
                                </div>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </div>

                            <div class="card-body">
                                <div class="info-row">
                                    <i class="fas fa-car"></i>
                                    <span><?= htmlspecialchars($booking['nama_jenis']) ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-id-card"></i>
                                    <span>***<?= htmlspecialchars($booking['plat_hint']) ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= $start->format('M d, Y') ?> • <?= $start->format('H:i') ?>-<?= $end->format('H:i') ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-clock"></i>
                                    <span>Duration: <?= $durationText ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="price">
                                    <span class="price-label">Total</span>
                                    <span class="price-value">Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></span>
                                </div>
                                <button class="btn-details" onclick="showBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)">
                                    <i class="fas fa-eye"></i>
                                    See Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    </div><!-- End dashboard-container -->

    <!-- BOOKING DETAIL MODAL -->
    <div class="modal-overlay" id="detailModal" onclick="closeDetailModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <button class="modal-close" onclick="closeDetailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body" id="modalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script src="<?= BASEURL ?>/assets/js/sidebar-toggle.js"></script>
    <script>
        const BASEURL = '<?= BASEURL ?>';

        function showBookingDetails(booking) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('modalContent');
            
            const start = new Date(booking.waktu_mulai);
            const end = new Date(booking.waktu_selesai);
            const duration = Math.round((end - start) / (1000 * 60 * 60));
            const created = new Date(booking.created_at);
            
            const statusClass = booking.status_booking === 'completed' ? 'completed' : 'cancelled';
            const statusText = booking.status_booking === 'completed' ? 'Completed' : 'Cancelled';
            
            content.innerHTML = `
                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Booking ID</span>
                        <span class="detail-value">#${booking.id_booking}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Location</h3>
                    <div class="detail-row">
                        <span class="detail-label">Parking Name</span>
                        <span class="detail-value">${booking.nama_tempat}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address</span>
                        <span class="detail-value">${booking.alamat_tempat}</span>
                    </div>
                    ${booking.nomor_slot ? `
                    <div class="detail-row">
                        <span class="detail-label">Slot Number</span>
                        <span class="detail-value">${booking.nomor_slot}</span>
                    </div>
                    ` : ''}
                </div>

                <div class="detail-section">
                    <h3>Vehicle</h3>
                    <div class="detail-row">
                        <span class="detail-label">Type</span>
                        <span class="detail-value">${booking.nama_jenis}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Plate Number</span>
                        <span class="detail-value">***${booking.plat_hint}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Time</h3>
                    <div class="detail-row">
                        <span class="detail-label">Start Time</span>
                        <span class="detail-value">${start.toLocaleString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        })}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">End Time</span>
                        <span class="detail-value">${end.toLocaleString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        })}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duration</span>
                        <span class="detail-value">${duration} hour${duration > 1 ? 's' : ''}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Payment</h3>
                    <div class="detail-row">
                        <span class="detail-label">Total Price</span>
                        <span class="detail-value price-highlight">Rp ${booking.total_harga.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Booked On</span>
                        <span class="detail-value">${created.toLocaleString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        })}</span>
                    </div>
                </div>
            `;
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeDetailModal(event) {
            if (event && event.target !== event.currentTarget) return;
            
            const modal = document.getElementById('detailModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailModal();
            }
        });
    </script>
</body>
</html>
