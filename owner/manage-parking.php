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

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nama = trim($_POST['nama_tempat'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $jam_buka = trim($_POST['jam_buka'] ?? '');
        $jam_tutup = trim($_POST['jam_tutup'] ?? '');
        $harga_jam = floatval($_POST['harga_jam'] ?? 0);
        $total_slot = intval($_POST['total_slot'] ?? 0);
        $latitude = floatval($_POST['latitude'] ?? 0);
        $longitude = floatval($_POST['longitude'] ?? 0);

        if (!$nama || !$alamat || !$jam_buka || !$jam_tutup || $harga_jam <= 0 || $total_slot <= 0) {
            $message = 'Semua field harus diisi dengan benar';
            $message_type = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO tempat_parkir (id_pemilik, nama_tempat, alamat_tempat, latitude, longitude, jam_buka, jam_tutup, harga_per_jam, total_spot)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$owner['id_pengguna'], $nama, $alamat, $latitude ?: null, $longitude ?: null, $jam_buka, $jam_tutup, $harga_jam, $total_slot]);
                $id_tempat_baru = $pdo->lastInsertId();

                // Handle file uploads
                handlePhotoUpload($id_tempat_baru, $owner['id_pengguna']);

                $message = 'Lahan parkir berhasil ditambahkan';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update') {
        $id_tempat = intval($_POST['id_tempat'] ?? 0);
        $nama = trim($_POST['nama_tempat'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $jam_buka = trim($_POST['jam_buka'] ?? '');
        $jam_tutup = trim($_POST['jam_tutup'] ?? '');
        $harga_jam = floatval($_POST['harga_jam'] ?? 0);
        $total_slot = intval($_POST['total_slot'] ?? 0);
        $latitude = floatval($_POST['latitude'] ?? 0);
        $longitude = floatval($_POST['longitude'] ?? 0);

        if (!$nama || !$alamat || !$jam_buka || !$jam_tutup || $harga_jam <= 0 || $total_slot <= 0) {
            $message = 'Semua field harus diisi dengan benar';
            $message_type = 'error';
        } else {
            try {
                // Verify ownership
                $stmt = $pdo->prepare("SELECT id_pemilik FROM tempat_parkir WHERE id_tempat = ?");
                $stmt->execute([$id_tempat]);
                $parking = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($parking && $parking['id_pemilik'] == $owner['id_pengguna']) {
                    $stmt = $pdo->prepare("
                        UPDATE tempat_parkir 
                        SET nama_tempat = ?, alamat_tempat = ?, latitude = ?, longitude = ?, 
                            jam_buka = ?, jam_tutup = ?, harga_per_jam = ?, total_spot = ?
                        WHERE id_tempat = ?
                    ");
                    $stmt->execute([$nama, $alamat, $latitude ?: null, $longitude ?: null, $jam_buka, $jam_tutup, $harga_jam, $total_slot, $id_tempat]);

                    // Handle file uploads
                    handlePhotoUpload($id_tempat, $owner['id_pengguna']);

                    $message = 'Lahan parkir berhasil diperbarui';
                    $message_type = 'success';
                } else {
                    $message = 'Anda tidak memiliki akses untuk mengedit lahan ini';
                    $message_type = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $id_tempat = intval($_POST['id_tempat'] ?? 0);
        try {
            // Verify ownership
            $stmt = $pdo->prepare("SELECT id_pemilik FROM tempat_parkir WHERE id_tempat = ?");
            $stmt->execute([$id_tempat]);
            $parking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($parking && $parking['id_pemilik'] == $owner['id_pengguna']) {
                $stmt = $pdo->prepare("DELETE FROM tempat_parkir WHERE id_tempat = ?");
                $stmt->execute([$id_tempat]);
                $message = 'Lahan parkir berhasil dihapus';
                $message_type = 'success';
            } else {
                $message = 'Anda tidak memiliki akses untuk menghapus lahan ini';
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Handle AJAX request untuk fetch parking data
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id_tempat = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM tempat_parkir WHERE id_tempat = ? AND id_pemilik = ?");
        $stmt->execute([$id_tempat, $owner['id_pengguna']]);
        $parking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($parking) {
            echo json_encode(['success' => true, 'parking' => $parking]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lahan parkir tidak ditemukan']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Get owner's parking locations
$parkings = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM tempat_parkir WHERE id_pemilik = ? ORDER BY nama_tempat ASC");
    $stmt->execute([$owner['id_pengguna']]);
    $parkings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('MANAGE PARKING ERROR: ' . $e->getMessage());
}

// ============ FUNCTION: Handle Photo Upload ============
function handlePhotoUpload($id_tempat, $id_pemilik)
{
    global $pdo;

    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../uploads/parking_photos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Check if photos were uploaded
    if (!isset($_FILES['photos'])) {
        return;
    }

    $photos = $_FILES['photos'];
    $maxFiles = 5;
    $uploadedCount = 0;

    // Count existing photos
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM parking_photos WHERE id_tempat = ?");
        $stmt->execute([$id_tempat]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $availableSlots = $maxFiles - $existing;
    } catch (Exception $e) {
        $availableSlots = $maxFiles;
    }

    // Process each uploaded file
    for ($i = 0; $i < count($photos['name']); $i++) {
        if ($uploadedCount >= $availableSlots) {
            break;
        }

        $filename = $photos['name'][$i];
        $fileTmp = $photos['tmp_name'][$i];
        $fileSize = $photos['size'][$i];
        $fileError = $photos['error'][$i];

        // Validate file
        if ($fileError !== UPLOAD_ERR_OK) {
            continue;
        }

        if ($fileSize > 5 * 1024 * 1024) { // 5MB
            continue;
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            continue;
        }

        // Generate unique filename
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = uniqid('parking_' . $id_tempat . '_', true) . '.' . strtolower($ext);
        $filepath = $uploadDir . $newFilename;

        // Move file
        if (move_uploaded_file($fileTmp, $filepath)) {
            // Save to database
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO parking_photos (id_tempat, foto_path, urutan)
                    VALUES (?, ?, ?)
                ");
                $relPath = 'uploads/parking_photos/' . $newFilename;
                $stmt->execute([$id_tempat, $relPath, $uploadedCount + 1]);
                $uploadedCount++;
            } catch (Exception $e) {
                // Delete file if DB insert fails
                unlink($filepath);
            }
        }
    }
}

// ============ FUNCTION: Get Parking Photos ============
function getParkingPhotos($id_tempat, $pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT foto_path FROM parking_photos WHERE id_tempat = ? ORDER BY urutan ASC");
        $stmt->execute([$id_tempat]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lahan Parkir | SPARK</title>

    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/owner.css">
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
                <li><a href="<?= BASEURL ?>/owner/manage-parking.php" class="active">
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
                <li><a href="<?= BASEURL ?>/owner/settings.php">
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
            <div class="header">
                <div>
                    <h1>Kelola Lahan Parkir</h1>
                    <p style="color: var(--text-light); margin-top: 5px;">Atur dan kelola semua lokasi parkiran Anda</p>
                </div>
                <button class="btn btn-primary" onclick="openModal('addModal')">
                    <i class="fas fa-plus"></i> Tambah Lahan
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert <?= $message_type ?>">
                    <i class="fas fa-<?= ($message_type === 'success' ? 'check-circle' : 'exclamation-circle') ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($parkings)): ?>
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <p>Anda belum memiliki lahan parkir</p>
                    <button class="btn btn-primary" onclick="openModal('addModal')">
                        <i class="fas fa-plus"></i> Tambah Lahan Pertama
                    </button>
                </div>
            <?php else: ?>
                <div class="parking-grid">
                    <?php foreach ($parkings as $parking): ?>
                        <div class="parking-card">
                            <!-- PHOTO SLIDER -->
                            <div class="parking-photo-slider" id="slider-<?= $parking['id_tempat'] ?>">
                                <?php
                                $photos = getParkingPhotos($parking['id_tempat'], $pdo);
                                if (!empty($photos)):
                                ?>
                                    <?php foreach ($photos as $idx => $photo): ?>
                                        <img src="<?= BASEURL ?>/<?= htmlspecialchars($photo['foto_path']) ?>"
                                            alt="Parking photo"
                                            class="slider-image"
                                            style="display: <?= $idx === 0 ? 'block' : 'none' ?>; width: 100%; height: 180px; object-fit: cover; border-radius: 8px 8px 0 0;">
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="photo-placeholder">
                                        <i class="fas fa-image"></i>
                                        <p>Belum ada foto</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="parking-header">
                                <div class="parking-name"><?= htmlspecialchars($parking['nama_tempat']) ?></div>
                                <span class="parking-status">
                                    <i class="fas fa-circle" style="color: var(--success); margin-right: 5px;"></i>
                                    Aktif
                                </span>
                            </div>
                            <div class="parking-body">
                                <div class="parking-info">
                                    <div class="parking-info-item">
                                        <span class="parking-label"><i class="fas fa-map-marker"></i> Alamat</span>
                                        <span class="parking-value"><?= htmlspecialchars(substr($parking['alamat_tempat'], 0, 20)) ?>...</span>
                                    </div>
                                    <div class="parking-info-item">
                                        <span class="parking-label"><i class="fas fa-clock"></i> Jam Buka</span>
                                        <span class="parking-value"><?= $parking['jam_buka'] ?> - <?= $parking['jam_tutup'] ?></span>
                                    </div>
                                    <div class="parking-info-item">
                                        <span class="parking-label"><i class="fas fa-dollar-sign"></i> Harga/Jam</span>
                                        <span class="parking-value">Rp <?= number_format($parking['harga_per_jam'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="parking-info-item">
                                        <span class="parking-label"><i class="fas fa-car"></i> Total Slot</span>
                                        <span class="parking-value"><?= $parking['total_spot'] ?> slot</span>
                                    </div>
                                </div>
                                <div class="parking-footer">
                                    <button class="parking-btn parking-btn-edit" onclick="editParking(<?= $parking['id_tempat'] ?>)">
                                        <i class="fas fa-edit"></i> <span>Edit</span>
                                    </button>
                                    <button class="parking-btn parking-btn-delete" onclick="confirmDelete(<?= $parking['id_tempat'] ?>)">
                                        <i class="fas fa-trash"></i> <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ADD/EDIT MODAL -->
    <div class="modal" id="addModal">
        <div class="modal-content" style="position: relative;">
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            <div class="modal-header" id="modalTitle">Tambah Lahan Parkir Baru</div>
            <form method="POST" id="addParkingForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_tempat" id="formIdTempat" value="">
                <div class="form-group">
                    <label>Nama Lahan Parkir</label>
                    <input type="text" id="namaTempat" name="nama_tempat" required placeholder="Contoh: Parkir Mall Central">
                </div>
                <div class="form-group">
                    <label>Alamat atau Google Maps Link <span style="color: var(--warning); font-size: 12px;">(Koordinat otomatis terisi)</span></label>
                    <input type="text" id="alamatInput" name="alamat" required placeholder="Contoh: Jl. Malioboro atau https://maps.google.com/?q=-7.7926,110.3652" autocomplete="off">
                    <small style="color: var(--text-light); margin-top: 5px; display: block;" id="geocodeStatus"></small>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="text" id="latitudeInput" name="latitude" readonly placeholder="-6.2088" style="background: var(--light); cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="text" id="longitudeInput" name="longitude" readonly placeholder="106.8456" style="background: var(--light); cursor: not-allowed;">
                    </div>
                </div>
                <div class="form-group">
                    <label>Jam Buka</label>
                    <input type="time" id="jam_buka" name="jam_buka" required>
                </div>
                <div class="form-group">
                    <label>Jam Tutup</label>
                    <input type="time" id="jam_tutup" name="jam_tutup" required>
                </div>
                <div class="form-group">
                    <label>Harga per Jam (Rp)</label>
                    <input type="number" id="harga_jam" name="harga_jam" step="100" required placeholder="5000">
                </div>
                <div class="form-group">
                    <label>Total Slot</label>
                    <input type="number" id="total_slot" name="total_slot" min="1" required placeholder="50">
                </div>

                <!-- FOTO UPLOAD -->
                <div class="form-group">
                    <label>Foto Lahan Parkir (Max 5 foto)</label>
                    <div class="photo-upload-area" id="photoUploadArea" onclick="document.getElementById('photoInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Klik atau drag foto di sini</p>
                        <small>JPG, PNG - Max 5MB per foto</small>
                    </div>
                    <input type="file" id="photoInput" name="photos[]" multiple accept="image/*" style="display:none;">
                    <div id="photoPreviewContainer" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap:10px; margin-top:15px;"></div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%; margin-top: 20px;">
                    <i class="fas fa-plus"></i> <span id="submitBtnText">Tambah Lahan</span>
                </button>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal" id="deleteModal">
        <div class="modal-content" style="position: relative; max-width: 400px;">
            <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            <div class="modal-header">Hapus Lahan Parkir?</div>
            <p style="margin-bottom: 20px; color: var(--text-light);">Anda yakin ingin menghapus lahan parkir ini? Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id_tempat" id="deleteId">
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn" onclick="closeModal('deleteModal')" style="flex: 1; background: var(--light); color: var(--text);">Batal</button>
                    <button type="submit" class="btn btn-danger" style="flex: 1;">Hapus Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('show');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
            // Reset form jika menutup add modal
            if (id === 'addModal') {
                document.getElementById('addParkingForm').reset();
                document.getElementById('formAction').value = 'add';
                document.getElementById('modalTitle').textContent = 'Tambah Lahan Parkir Baru';
                document.getElementById('submitBtnText').textContent = 'Tambah Lahan';
                selectedPhotos = [];
                photoPreviewContainer.innerHTML = '';
            }
        }

        function confirmDelete(id) {
            document.getElementById('deleteId').value = id;
            openModal('deleteModal');
        }

        // ============ EDIT PARKING ============
        function editParking(id) {
            // Fetch parking data via AJAX
            fetch(`manage-parking.php?action=get&id=${id}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Response:', text);
                    try {
                        const data = JSON.parse(text);

                        if (data.success && data.parking) {
                            const p = data.parking;

                            // Populate form
                            document.getElementById('formAction').value = 'update';
                            document.getElementById('formIdTempat').value = p.id_tempat;
                            document.getElementById('namaTempat').value = p.nama_tempat;
                            document.getElementById('alamatInput').value = p.alamat_tempat;
                            document.getElementById('latitudeInput').value = p.latitude || '';
                            document.getElementById('longitudeInput').value = p.longitude || '';
                            document.getElementById('jam_buka').value = p.jam_buka;
                            document.getElementById('jam_tutup').value = p.jam_tutup;
                            document.getElementById('harga_jam').value = p.harga_per_jam;
                            document.getElementById('total_slot').value = p.total_spot;

                            // Update modal header and button
                            document.getElementById('modalTitle').textContent = 'Edit Lahan Parkir';
                            document.getElementById('submitBtnText').textContent = 'Simpan Perubahan';

                            // Clear geocode status
                            document.getElementById('geocodeStatus').textContent = '';

                            // Open modal
                            openModal('addModal');
                        } else {
                            alert('Error: ' + (data.message || 'Gagal memuat data lahan parkir'));
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        alert('Error: Response parsing failed - ' + e.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Error: Gagal memuat data lahan parkir - ' + error.message);
                });
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal(modal.id);
                }
            });
        });

        // GEOCODING FUNCTIONALITY using Nominatim (OpenStreetMap)
        const alamatInput = document.getElementById('alamatInput');
        const latitudeInput = document.getElementById('latitudeInput');
        const longitudeInput = document.getElementById('longitudeInput');
        const geocodeStatus = document.getElementById('geocodeStatus');

        let geocodeTimeout;

        alamatInput.addEventListener('blur', function() {
            const input = this.value.trim();
            if (input.length > 3) {
                geocodeInput(input);
            }
        });

        // MAIN FUNCTION: Detect input type and process accordingly
        async function geocodeInput(input) {
            // Check if input is a Google Maps link
            if (isGoogleMapsLink(input)) {
                extractFromGoogleMaps(input);
            } else {
                // Treat as regular address
                geocodeAddress(input);
            }
        }

        // FUNCTION 1: Check if input is Google Maps link
        function isGoogleMapsLink(input) {
            return input.includes('google.com/maps') ||
                input.includes('maps.google.com') ||
                input.includes('goo.gl/maps') ||
                input.includes('share.google') ||
                input.includes('share.google');
        }

        // FUNCTION 2: Extract coordinates from Google Maps link
        function extractFromGoogleMaps(input) {
            geocodeStatus.textContent = '⏳ Mengekstrak koordinat dari Google Maps...';
            geocodeStatus.style.color = '#FFE100';

            try {
                // Try to extract coordinates from URL
                // Format 1: /maps/place/-7.7926,110.3652
                let match = input.match(/\/place\/([-\d.]+),([-\d.]+)/);

                // Format 2: ?q=-7.7926,110.3652
                if (!match) {
                    match = input.match(/[?&]q=([-\d.]+),([-\d.]+)/);
                }

                // Format 3: coords= parameter
                if (!match) {
                    match = input.match(/coords=([-\d.]+),([-\d.]+)/);
                }

                if (match && match[1] && match[2]) {
                    const lat = parseFloat(match[1]).toFixed(7);
                    const lon = parseFloat(match[2]).toFixed(7);

                    // Validate coordinates are within valid range
                    if (lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                        latitudeInput.value = lat;
                        longitudeInput.value = lon;

                        geocodeStatus.textContent = `✓ Koordinat diekstrak dari Google Maps: ${lat}, ${lon}`;
                        geocodeStatus.style.color = 'var(--success)';
                    } else {
                        throw new Error('Koordinat tidak valid');
                    }
                } else {
                    // Try shortened URL warning
                    if (input.includes('goo.gl') || input.includes('share.google')) {
                        geocodeStatus.textContent = '⚠️ Link Google Maps diperpendek. Gunakan "Bagikan" → Copy link penuh (https://maps.google.com/?q=...) atau langsung paste alamat.';
                        geocodeStatus.style.color = '#f39c12';
                        latitudeInput.value = '';
                        longitudeInput.value = '';
                    } else {
                        throw new Error('Tidak bisa ekstrak koordinat dari link');
                    }
                }
            } catch (error) {
                console.error('Google Maps extraction error:', error);
                geocodeStatus.textContent = `✗ Error: ${error.message}. Pastikan link Google Maps berisi koordinat atau gunakan alamat.`;
                geocodeStatus.style.color = 'var(--danger)';
                latitudeInput.value = '';
                longitudeInput.value = '';
            }
        }

        // FUNCTION 3: Geocode regular address using Nominatim
        async function geocodeAddress(address) {
            geocodeStatus.textContent = '⏳ Mencari koordinat via Nominatim...';
            geocodeStatus.style.color = '#FFE100';

            try {
                // Add "Indonesia" to improve search results for Indonesian addresses
                const searchAddress = address.includes('Indonesia') ? address : address + ', Indonesia';

                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchAddress)}&limit=1`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error('Gagal menghubungi server geocoding');
                }

                const data = await response.json();

                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat).toFixed(7);
                    const lon = parseFloat(result.lon).toFixed(7);

                    latitudeInput.value = lat;
                    longitudeInput.value = lon;

                    geocodeStatus.textContent = `✓ Koordinat ditemukan: ${lat}, ${lon}`;
                    geocodeStatus.style.color = 'var(--success)';
                } else {
                    geocodeStatus.textContent = '✗ Alamat tidak ditemukan. Coba dengan alamat yang lebih spesifik (Jl. Malioboro No. 123, Yogyakarta) atau paste link Google Maps.';
                    geocodeStatus.style.color = 'var(--danger)';
                    latitudeInput.value = '';
                    longitudeInput.value = '';
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                geocodeStatus.textContent = '✗ Error: ' + error.message;
                geocodeStatus.style.color = 'var(--danger)';
            }
        }

        // Form submission validation
        document.getElementById('addParkingForm').addEventListener('submit', function(e) {
            const latitude = latitudeInput.value.trim();
            const longitude = longitudeInput.value.trim();

            if (!latitude || !longitude) {
                e.preventDefault();
                alert('Koordinat belum terisi. Harap masukkan alamat yang valid.');
            }
        });

        // Clear coordinates when address changes
        alamatInput.addEventListener('input', function() {
            latitudeInput.value = '';
            longitudeInput.value = '';
            geocodeStatus.textContent = '';
        });

        // ============ PHOTO UPLOAD HANDLING ============
        const photoInput = document.getElementById('photoInput');
        const photoUploadArea = document.getElementById('photoUploadArea');
        const photoPreviewContainer = document.getElementById('photoPreviewContainer');

        let selectedPhotos = [];

        // Drag and drop
        photoUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            photoUploadArea.style.borderColor = 'var(--spark-yellow)';
            photoUploadArea.style.background = 'rgba(255, 225, 0, 0.15)';
        });

        photoUploadArea.addEventListener('dragleave', () => {
            photoUploadArea.style.borderColor = 'rgba(255, 225, 0, 0.3)';
            photoUploadArea.style.background = 'rgba(255, 225, 0, 0.05)';
        });

        photoUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            photoUploadArea.style.borderColor = 'rgba(255, 225, 0, 0.3)';
            photoUploadArea.style.background = 'rgba(255, 225, 0, 0.05)';

            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            handlePhotoSelection(files);
        });

        photoInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            handlePhotoSelection(files);
        });

        function handlePhotoSelection(files) {
            // Limit to 5 photos
            const availableSlots = 5 - selectedPhotos.length;
            const filesToAdd = files.slice(0, availableSlots);

            if (filesToAdd.length < files.length) {
                alert(`Max 5 foto. Hanya ${filesToAdd.length} foto yang ditambahkan.`);
            }

            filesToAdd.forEach(file => {
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert(`File ${file.name} terlalu besar (max 5MB)`);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    selectedPhotos.push({
                        file: file,
                        preview: e.target.result
                    });
                    renderPhotoPreview();
                };
                reader.readAsDataURL(file);
            });
        }

        function renderPhotoPreview() {
            photoPreviewContainer.innerHTML = selectedPhotos.map((photo, index) => `
        <div class="photo-preview-item">
            <img src="${photo.preview}" alt="Preview ${index + 1}">
            <button type="button" class="photo-preview-remove" onclick="removePhoto(${index})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');

            // Update file input
            const dataTransfer = new DataTransfer();
            selectedPhotos.forEach(p => dataTransfer.items.add(p.file));
            photoInput.files = dataTransfer.files;
        }

        function removePhoto(index) {
            selectedPhotos.splice(index, 1);
            renderPhotoPreview();
        }

        // ============ PHOTO SLIDER FOR CARDS ============
        let photoSliders = {};

        function initPhotoSliders() {
            document.querySelectorAll('[id^="slider-"]').forEach(sliderDiv => {
                const parkingId = sliderDiv.id.replace('slider-', '');
                const images = sliderDiv.querySelectorAll('img.slider-image');

                if (images.length > 0) {
                    photoSliders[parkingId] = {
                        element: sliderDiv,
                        currentIndex: 0,
                        images: images,
                        total: images.length
                    };

                    if (images.length > 1) {
                        renderPhotoSliderControls(parkingId);
                    }
                }
            });
        }

        function renderPhotoSliderControls(parkingId) {
            const slider = photoSliders[parkingId];

            // Remove existing controls if any
            slider.element.querySelectorAll('.photo-slider-arrow, .photo-slider-nav').forEach(el => el.remove());

            const controlsHTML = `
        <button class="photo-slider-arrow photo-slider-prev" onclick="prevPhoto('${parkingId}'); return false;">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="photo-slider-arrow photo-slider-next" onclick="nextPhoto('${parkingId}'); return false;">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="photo-slider-nav">
            ${Array(slider.total).fill(0).map((_, i) => `
                <div class="photo-slider-dot ${i === 0 ? 'active' : ''}" onclick="goToPhoto('${parkingId}', ${i}); return false;"></div>
            `).join('')}
        </div>
    `;
            slider.element.insertAdjacentHTML('beforeend', controlsHTML);
            updatePhotoSlider(parkingId);
        }

        function updatePhotoSlider(parkingId) {
            const slider = photoSliders[parkingId];
            if (!slider) return;

            slider.images.forEach((img, i) => {
                img.style.display = i === slider.currentIndex ? 'block' : 'none';
            });

            const dots = document.querySelectorAll(`#slider-${parkingId} .photo-slider-dot`);
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === slider.currentIndex);
            });
        }

        function nextPhoto(parkingId) {
            const slider = photoSliders[parkingId];
            if (!slider) return;
            slider.currentIndex = (slider.currentIndex + 1) % slider.total;
            updatePhotoSlider(parkingId);
        }

        function prevPhoto(parkingId) {
            const slider = photoSliders[parkingId];
            if (!slider) return;
            slider.currentIndex = (slider.currentIndex - 1 + slider.total) % slider.total;
            updatePhotoSlider(parkingId);
        }

        function goToPhoto(parkingId, index) {
            if (photoSliders[parkingId]) {
                photoSliders[parkingId].currentIndex = index;
                updatePhotoSlider(parkingId);
            }
        }

        // Initialize sliders when page loads
        document.addEventListener('DOMContentLoaded', initPhotoSliders);
    </script>

</body>

</html>