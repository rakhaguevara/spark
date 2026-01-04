<?php
// pages/dashboard.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../includes/parking-card-components.php';

startSession();

if (!isLoggedIn()) {
    header('Location: ' . BASEURL . '/pages/login.php');
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

// Helper function to get first 2 words of name
function getShortName($fullName)
{
    $words = explode(' ', trim($fullName));
    $shortName = implode(' ', array_slice($words, 0, 2));
    return $shortName;
}

// Get filter parameters
$vehicleTypeFilter = $_GET['vehicle_type'] ?? null;
$cityFilter = $_GET['city'] ?? null;
$dateFilter = $_GET['date'] ?? null;

// Check if foto_tempat column exists
$has_foto_tempat = false;
try {
    $check_cols = $pdo->query("SHOW COLUMNS FROM tempat_parkir LIKE 'foto_tempat'");
    $has_foto_tempat = $check_cols->rowCount() > 0;
} catch (PDOException $e) {
    // Column doesn't exist, continue without it
}

// Build SQL query with optional vehicle type filter
$foto_select = $has_foto_tempat ? "tp.foto_tempat," : "NULL as foto_tempat,";

$sql = "
    SELECT 
        tp.id_tempat,
        tp.nama_tempat,
        tp.alamat_tempat,
        tp.latitude,
        tp.longitude,
        tp.harga_per_jam,
        tp.jam_buka,
        tp.jam_tutup,
        {$foto_select}
        COUNT(sp.id_slot) as total_slot,
        SUM(CASE WHEN sp.status_slot = 'available' THEN 1 ELSE 0 END) as slot_tersedia,
        COALESCE(AVG(ut.rating), 4.5) as avg_rating,
        COUNT(DISTINCT ut.id_ulasan) as total_review
    FROM tempat_parkir tp
    LEFT JOIN slot_parkir sp ON tp.id_tempat = sp.id_tempat
    LEFT JOIN ulasan_tempat ut ON tp.id_tempat = ut.id_tempat
";

// Add vehicle type filter if specified
$whereConditions = [];
$params = [];

if ($vehicleTypeFilter) {
    $whereConditions[] = "EXISTS (
        SELECT 1 
        FROM slot_parkir sp2
        INNER JOIN jenis_kendaraan jk ON sp2.id_jenis = jk.id_jenis
        WHERE sp2.id_tempat = tp.id_tempat
        AND sp2.status_slot = 'available'
        AND jk.nama_jenis = :vehicle_type
    )";
    $params['vehicle_type'] = $vehicleTypeFilter;
}

// Facility Filter (Self Park / Garage)
// Note: Since 'is_covered' column is missing in provided schema, currently mocking logic
// or assuming 'Garage' implies checks against 'Covered' logic if implemented later.
// For now, we allow the parameter to pass through.
$facilityFilter = $_GET['facility'] ?? null;
if ($facilityFilter) {
    if ($facilityFilter === 'garage') {
        // Example: If we had a column. For now, we can filter by name or assume all are supported.
        // $whereConditions[] = "tp.is_covered = 1"; 
        // Or check if name contains Mall/Garage
        // $whereConditions[] = "(tp.nama_tempat LIKE '%Mall%' OR tp.nama_tempat LIKE '%Garage%')"; 
    } elseif ($facilityFilter === 'self-park') {
        // $whereConditions[] = "tp.is_self_park = 1";
    }
}

// Add city filter if specified
if ($cityFilter) {
    $whereConditions[] = "tp.alamat_tempat LIKE :city";
    $params['city'] = "%{$cityFilter}%";
}

// Build WHERE clause
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= "
    GROUP BY tp.id_tempat
    ORDER BY tp.nama_tempat ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$parkingSpots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to get vehicle availability per parking spot
function getVehicleAvailability($pdo, $id_tempat, $vehicleFilter = null)
{
    $sql = "
        SELECT 
            jk.nama_jenis,
            COUNT(sp.id_slot) as available_count
        FROM slot_parkir sp
        INNER JOIN jenis_kendaraan jk ON sp.id_jenis = jk.id_jenis
        WHERE sp.id_tempat = :id_tempat 
        AND sp.status_slot = 'available'
    ";

    // Apply vehicle filter if specified
    if ($vehicleFilter) {
        $sql .= " AND jk.nama_jenis = :vehicle_type";
    }

    $sql .= "
        GROUP BY jk.id_jenis, jk.nama_jenis
        HAVING available_count > 0
        ORDER BY jk.nama_jenis ASC
    ";

    $stmt = $pdo->prepare($sql);
    $params = ['id_tempat' => $id_tempat];
    if ($vehicleFilter) {
        $params['vehicle_type'] = $vehicleFilter;
    }
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add vehicle availability to each parking spot
foreach ($parkingSpots as &$spot) {
    $spot['vehicle_availability'] = getVehicleAvailability($pdo, $spot['id_tempat'], $vehicleTypeFilter);

    // CRITICAL: Recalculate slot_tersedia from sum of per-vehicle availability
    // This ensures total ALWAYS equals sum of per-vehicle slots (filtered)
    $totalAvailable = 0;
    foreach ($spot['vehicle_availability'] as $vehicle) {
        $totalAvailable += $vehicle['available_count'];
    }
    $spot['slot_tersedia'] = $totalAvailable;
}
unset($spot);

// Fungsi helper untuk format harga
function formatPrice($price)
{
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Fungsi untuk mendapatkan fasilitas
function getParkingFacilities($jam_buka, $jam_tutup)
{
    $facilities = [];

    // Cek 24 jam
    if ($jam_buka == '00:00:00' && $jam_tutup == '23:59:59') {
        $facilities[] = ['icon' => 'clock', 'text' => '24 Hours'];
    }

    // Fasilitas standar
    $facilities[] = ['icon' => 'camera', 'text' => 'CCTV'];
    $facilities[] = ['icon' => 'shield-alt', 'text' => 'Secure'];
    $facilities[] = ['icon' => 'parking', 'text' => 'Covered'];

    return $facilities;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Find Parking | SPARK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dashboard-user.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/parking-card.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/map-markers.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/booking-modal.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="dashboard-navbar">
        <!-- Desktop: Logo Left. Mobile: Hamburger Left -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>

        <a href="<?= BASEURL ?>/pages/dashboard.php" class="brand-wrapper">
            <img src="<?= BASEURL ?>/assets/img/logo.png" alt="Spark Logo">
            <span class="brand-text">SPARK</span>
        </a>

        <!-- Search Bar (Hidden on Mobile) -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Where are you going?">
        </div>

        <!-- Right Side: User Actions -->
        <div class="user-actions">
            <!-- Mobile: Profile Icon Only. Desktop: Chip -->
            <button class="icon-btn mobile-hidden" title="Notifications"><i class="fas fa-bell"></i></button>

            <div class="profile-chip">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?= BASEURL ?>/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($user['nama_pengguna'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <span class="profile-name"><?= htmlspecialchars(getShortName($user['nama_pengguna'] ?? 'User')) ?></span>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTAINER -->
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
                        <a href="<?= BASEURL ?>/pages/dashboard.php" class="active" data-tooltip="Find Parking">
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

            <!-- FILTERS BAR -->
            <div class="filters-bar">
                <!-- Filter Pills -->
                <button class="filter-pill active" data-filter="all">
                    <i class="fas fa-filter"></i>
                    All
                </button>
                <button class="filter-pill" data-filter="vehicle">
                    <i class="fas fa-car"></i>
                    Vehicle Type
                </button>
                <button class="filter-pill" data-filter="self-park">
                    Self Park
                </button>
                <button class="filter-pill" data-filter="garage">
                    Garage - Covered
                </button>

                <!-- Combined Search Input (Simple) -->
                <div class="search-input-wrapper">
                    <button class="search-input" id="searchInputBtn" type="button">
                        <i class="fas fa-map-marker-alt"></i>
                        <span id="searchInputText">
                            <?php
                            if ($cityFilter || $dateFilter) {
                                $cityText = $cityFilter ?: 'City';
                                $dateText = $dateFilter ? date('d M Y', strtotime($dateFilter)) : 'Date';
                                echo htmlspecialchars($cityText) . ' • ' . htmlspecialchars($dateText);
                            } else {
                                echo 'Select city and date';
                            }
                            ?>
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <!-- Hidden inputs for backend -->
                    <input type="hidden" id="selectedCity" value="<?= htmlspecialchars($cityFilter ?? '') ?>">
                    <input type="hidden" id="selectedDate" value="<?= htmlspecialchars($dateFilter ?? '') ?>">

                    <!-- Floating Popup (Hidden by default) -->
                    <div class="search-popup" id="searchPopup">
                        <div class="search-popup-content">
                            <!-- City Search -->
                            <div class="popup-input-group">
                                <input type="text" class="popup-input" id="citySearch" placeholder="Search city...">
                            </div>

                            <!-- City Chips (Hidden until needed) -->
                            <div class="city-chips-container" id="cityChipsContainer">
                                <button class="city-chip" data-city="Jakarta">Jakarta</button>
                                <button class="city-chip" data-city="Bandung">Bandung</button>
                                <button class="city-chip" data-city="Surabaya">Surabaya</button>
                                <button class="city-chip" data-city="Yogyakarta">Yogyakarta</button>
                                <button class="city-chip" data-city="Semarang">Semarang</button>
                                <button class="city-chip" data-city="Malang">Malang</button>
                                <button class="city-chip" data-city="Solo">Solo</button>
                                <button class="city-chip" data-city="Cirebon">Cirebon</button>
                            </div>

                            <!-- Date Picker -->
                            <div class="popup-input-group">
                                <input type="date" class="popup-input" id="popupDateInput" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($dateFilter ?? '') ?>">
                            </div>

                            <!-- Actions -->
                            <div class="popup-actions">
                                <button class="btn-reset" id="btnReset">Reset</button>
                                <button class="btn-apply" id="btnApply">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sort Dropdown -->
                <div class="sort-dropdown">
                    <select id="sortSelect">
                        <option value="relevance">Sort by Relevance</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="distance">Distance</option>
                        <option value="rating">Rating</option>
                    </select>
                </div>
            </div>

            <!-- CONTENT GRID -->
            <div class="content-grid">

                <!-- LEFT SIDE - PARKING LIST -->
                <div class="parking-container" id="parkingList">
                    <?php if (!empty($parkingSpots)): ?>
                        <?php foreach ($parkingSpots as $spot): ?>
                            <?php renderParkingCard($spot, BASEURL); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-car-side"></i>
                            <p>Belum ada data tempat parkir.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RIGHT SIDE - MAP -->
                <div class="map-container">
                    <div id="map"></div>
                </div>

            </div>
        </main>
    </div>

    <!-- BOOKING MODAL -->
    <?php include __DIR__ . '/../includes/booking-modal.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Global Configuration -->
    <script>
        window.APP_CONFIG = {
            parkingData: <?= json_encode($parkingSpots) ?>,
            BASEURL: '<?= BASEURL ?>'
        };
    </script>

    <!-- Application Scripts -->
    <script src="<?= BASEURL ?>/assets/js/sidebar-toggle.js"></script>
    <script src="<?= BASEURL ?>/assets/js/dashboard-map.js"></script>
    <script src="<?= BASEURL ?>/assets/js/booking-modal.js"></script>
    <script src="<?= BASEURL ?>/assets/js/dashboard-card-interaction.js"></script>
    <script src="<?= BASEURL ?>/assets/js/dashboard-search-sort.js"></script>
    <script src="<?= BASEURL ?>/assets/js/dashboard-filters.js"></script>
    <script src="<?= BASEURL ?>/assets/js/search-popup.js"></script>
    <script src="<?= BASEURL ?>/assets/js/mobile-interaction.js"></script>
</body>

</html>