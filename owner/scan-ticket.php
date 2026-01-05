<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/owner-auth.php';

requireOwnerLogin();

$owner = getCurrentOwner();
$pdo = getDBConnection();

// Get owner's parking locations
$parkings = [];
try {
    $stmt = $pdo->prepare("SELECT id_tempat, nama_tempat FROM tempat_parkir WHERE id_pengguna = ? ORDER BY nama_tempat ASC");
    $stmt->execute([$owner['id_pengguna']]);
    $parkings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('SCAN TICKET ERROR: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Tiket | SPARK</title>
    
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/owner.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.js"></script>
    
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
            <li><a href="<?= BASEURL ?>/owner/scan-ticket.php" class="active">
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
        <div class="content-header">
            <h1>Scan Tiket Parkir</h1>
            <p>Validasi tiket parkir pelanggan melalui QR code</p>
        </div>

        <div class="scanner-container">
            <div class="parking-select">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Pilih Lokasi Parkir</label>
                <select id="parkingSelect">
                    <option value="">-- Pilih Lahan Parkir --</option>
                    <?php foreach ($parkings as $p): ?>
                        <option value="<?= $p['id_tempat'] ?>"><?= htmlspecialchars($p['nama_tempat']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="camera-wrapper">
                <video id="camera-video" playsinline></video>
                <canvas id="camera-canvas"></canvas>
            </div>

            <div class="scanner-controls">
                <button class="btn btn-primary" id="startBtn" onclick="startCamera()">
                    <i class="fas fa-camera"></i> Mulai Kamera
                </button>
                <button class="btn btn-danger" id="stopBtn" onclick="stopCamera()" style="display: none;">
                    <i class="fas fa-stop-circle"></i> Hentikan Kamera
                </button>
            </div>

            <div class="status-box" id="statusBox">
                <span class="status-icon" id="statusIcon"></span>
                <span id="statusText">Ready to scan</span>
            </div>

            <div class="result-info" id="resultInfo">
                <h3 style="margin-bottom: 15px; color: var(--dark);">Hasil Pemindaian</h3>
                <div class="result-item">
                    <span class="result-label">Status</span>
                    <span class="result-value" id="resultStatus">-</span>
                </div>
                <div class="result-item">
                    <span class="result-label">ID Booking</span>
                    <span class="result-value" id="resultBookingId">-</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Waktu</span>
                    <span class="result-value" id="resultTime">-</span>
                </div>
                <div class="result-item">
                    <span class="result-label">Tipe</span>
                    <span class="result-value" id="resultType">-</span>
                </div>
            </div>
        </div>

        <div style="background: rgba(52,152,219,0.1); border-left: 4px solid #3498db; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 0; color: #2c3e50; font-size: 13px;">
                <i class="fas fa-info-circle"></i>
                <strong>Info:</strong> Fitur scan QR akan memvalidasi tiket parkir secara real-time. 
                Pastikan kamera perangkat Anda memiliki akses dan pencahayaan yang cukup.
            </p>
        </div>
    </div>
</div>

<script>
let camera = null;
let scanningActive = false;

async function startCamera() {
    try {
        const video = document.getElementById('camera-video');
        camera = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        video.srcObject = camera;
        video.play();
        
        document.getElementById('startBtn').style.display = 'none';
        document.getElementById('stopBtn').style.display = 'inline-flex';
        scanningActive = true;
        
        setTimeout(() => scanQR(), 100);
    } catch (error) {
        alert('Tidak dapat mengakses kamera: ' + error.message);
    }
}

function stopCamera() {
    if (camera) {
        camera.getTracks().forEach(track => track.stop());
    }
    scanningActive = false;
    document.getElementById('startBtn').style.display = 'inline-flex';
    document.getElementById('stopBtn').style.display = 'none';
}

function scanQR() {
    if (!scanningActive) return;
    
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 2 });
    
    if (code) {
        handleQRCode(code.data);
        return;
    }
    
    requestAnimationFrame(scanQR);
}

function handleQRCode(data) {
    try {
        const qrData = JSON.parse(data);
        validateQR(qrData);
    } catch (e) {
        showStatus('error', '❌ QR Code tidak valid');
    }
}

function validateQR(qrData) {
    const parkingId = document.getElementById('parkingSelect').value;
    
    if (!parkingId) {
        showStatus('warning', '⚠️ Pilih lokasi parkir terlebih dahulu');
        return;
    }
    
    // Send to validation API
    fetch('<?= BASEURL ?>/owner/api/validate-ticket.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            parking_id: parkingId,
            ...qrData
        })
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            showStatus('success', '✓ Tiket Valid - ' + result.type);
            showResult(result);
        } else {
            showStatus('error', '❌ ' + result.message);
        }
    })
    .catch(err => {
        showStatus('error', '❌ Error: ' + err.message);
    });
}

function showStatus(type, message) {
    const box = document.getElementById('statusBox');
    const icon = document.getElementById('statusIcon');
    const text = document.getElementById('statusText');
    
    box.className = 'status-box ' + type;
    icon.textContent = type === 'success' ? '✓' : type === 'error' ? '❌' : '⚠️';
    text.textContent = message;
}

function showResult(result) {
    document.getElementById('resultStatus').textContent = result.status || '-';
    document.getElementById('resultBookingId').textContent = result.booking_id || '-';
    document.getElementById('resultTime').textContent = new Date().toLocaleTimeString('id-ID');
    document.getElementById('resultType').textContent = result.type || '-';
    document.getElementById('resultInfo').classList.add('show');
}
</script>

</body>
</html>
