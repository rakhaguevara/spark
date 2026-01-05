<?php
require_once __DIR__ . '/config/app.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Badge - Both Register & Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: rgba(255,255,255,0.9);
            text-align: center;
            margin-bottom: 40px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #667eea;
        }
        .feature-list {
            background: #f0f8ff;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #2c3e50;
            line-height: 1.8;
        }
        .feature-list ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        .test-section {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .test-section h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .test-case {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #3498db;
            color: white;
            flex: 1;
        }
        .btn-secondary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .badge-demo {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.1) 0%, rgba(39, 174, 96, 0.1) 100%);
            border: 1px solid #2ecc71;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
            font-size: 12px;
            color: #27ae60;
        }
        .note {
            background: #fff9e6;
            border-left: 4px solid #f39c12;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            color: #8b6914;
            margin-top: 15px;
        }
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 10px;
        }
        .status.active {
            background: #d4edda;
            color: #155724;
        }
        .highlight {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe6cc 100%);
            border-left: 4px solid #f39c12;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Notification Badge Test - Both Pages</h1>
        <p class="subtitle">Test notification badge di halaman register dan login</p>

        <div class="highlight">
            <strong>✨ Status:</strong> Badge notifikasi sudah ada di KEDUA halaman - Register dan Login!
            Setiap halaman memiliki error badge (merah) dan success badge (hijau) dengan animasi yang sama.
        </div>

        <div class="grid">
            <!-- REGISTER PAGE -->
            <div class="card">
                <h2>📋 Register Page Badges</h2>
                <div class="feature-list">
                    <strong>✅ Badge Features:</strong>
                    <ul>
                        <li>❌ Error Badge (Merah) - saat validasi gagal</li>
                        <li>✓ Success Badge (Hijau) - saat registrasi berhasil</li>
                        <li>🔄 Auto-redirect - ke login setelah 3 detik</li>
                        <li>✕ Dismiss button - bisa close error badge</li>
                        <li>📱 Responsive - mobile & desktop</li>
                    </ul>
                </div>

                <div class="test-section">
                    <h3>🧪 Test Error Badge</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                        Coba register dengan email yang sudah terdaftar (email duplikat)
                    </p>
                    <form method="POST" action="<?= BASEURL ?>/functions/owner-register-proses.php" class="test-case">
                        <input type="hidden" name="nama" value="Test Owner">
                        <input type="hidden" name="email" value="owner@parkir.com">
                        <input type="hidden" name="password" value="TestPass123">
                        <input type="hidden" name="confirm_password" value="TestPass123">
                        <input type="hidden" name="no_hp" value="081234567890">
                        <input type="hidden" name="nama_parkir" value="Test Parkir">
                        <button type="submit" class="btn btn-danger" style="flex: 1;">
                            Test Error: Email Duplicate
                        </button>
                    </form>
                </div>

                <div class="test-section">
                    <h3>🧪 Test Success Badge</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                        Coba register dengan data valid dan email baru
                    </p>
                    <a href="<?= BASEURL ?>/owner/register.php" class="btn btn-primary">
                        Go to Register Page
                    </a>
                </div>

                <div class="badge-demo">
                    <strong>📌 Register Badge Messages:</strong><br>
                    ❌ "Email sudah terdaftar"<br>
                    ❌ "Password dan konfirmasi tidak cocok"<br>
                    ❌ "Password minimal 6 karakter"<br>
                    ❌ "Semua field wajib diisi"<br>
                    ✅ "Registrasi berhasil!" → countdown 3 detik → redirect login
                </div>
            </div>

            <!-- LOGIN PAGE -->
            <div class="card">
                <h2>🔐 Login Page Badges</h2>
                <div class="feature-list">
                    <strong>✅ Badge Features:</strong>
                    <ul>
                        <li>❌ Error Badge (Merah) - saat login gagal</li>
                        <li>✓ Success Badge (Hijau) - saat login berhasil</li>
                        <li>🔄 Auto-redirect - ke dashboard langsung</li>
                        <li>✕ Dismiss button - bisa close error badge</li>
                        <li>📱 Responsive - mobile & desktop</li>
                    </ul>
                </div>

                <div class="test-section">
                    <h3>🧪 Test Error Badge</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                        Coba login dengan email/password yang salah
                    </p>
                    <form method="POST" action="<?= BASEURL ?>/functions/owner-login-proses.php" class="test-case">
                        <input type="hidden" name="email" value="wrong@email.com">
                        <input type="hidden" name="password" value="wrongpassword">
                        <button type="submit" class="btn btn-danger" style="flex: 1;">
                            Test Error: Wrong Credentials
                        </button>
                    </form>
                </div>

                <div class="test-section">
                    <h3>🧪 Test Success Badge</h3>
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                        Login dengan akun yang sudah terdaftar
                    </p>
                    <a href="<?= BASEURL ?>/owner/login.php" class="btn btn-primary">
                        Go to Login Page
                    </a>
                </div>

                <div class="badge-demo">
                    <strong>📌 Login Badge Messages:</strong><br>
                    ❌ "Email atau password salah"<br>
                    ❌ "Owner tidak ditemukan"<br>
                    ❌ "Database error"<br>
                    ✅ "Login berhasil!" → redirect ke dashboard
                </div>
            </div>
        </div>

        <!-- COMPARISON -->
        <div class="card" style="margin-bottom: 20px;">
            <h2>📊 Badge System Comparison</h2>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #667eea;">Feature</th>
                        <th style="padding: 10px; text-align: center; border-bottom: 2px solid #667eea;">Register</th>
                        <th style="padding: 10px; text-align: center; border-bottom: 2px solid #667eea;">Login</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">Error Badge (Merah)</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">Success Badge (Hijau)</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">Dismiss Button (X)</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">Countdown Timer</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅ (3 sec)</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅ (instant)</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">Auto-Redirect</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅ (login)</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅ (dashboard)</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">Animations</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅ (pulse)</td>
                        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">✅ (pulse)</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Responsive Design</td>
                        <td style="padding: 10px; text-align: center;">✅</td>
                        <td style="padding: 10px; text-align: center;">✅</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- QUICK TEST GUIDE -->
        <div class="card">
            <h2>📋 Quick Test Guide</h2>
            <ol style="font-size: 13px; line-height: 2; color: #555; margin-left: 20px;">
                <li><strong>Test Register Error:</strong> Klik tombol "Test Error: Email Duplicate" di atas</li>
                <li><strong>Test Login Error:</strong> Klik tombol "Test Error: Wrong Credentials" di atas</li>
                <li><strong>Test Register Success:</strong> Klik "Go to Register Page", isi form dengan data valid</li>
                <li><strong>Test Login Success:</strong> Klik "Go to Login Page", login dengan akun yang terdaftar</li>
                <li><strong>Expected Result:</strong> Badge muncul dengan animasi slide-in dari kanan</li>
                <li><strong>Error Badge:</strong> Bisa dismiss dengan klik X atau coba lagi</li>
                <li><strong>Success Badge:</strong> Auto-redirect ke halaman berikutnya</li>
            </ol>

            <div class="note">
                <strong>💡 Catatan:</strong> Kedua halaman (register & login) sekarang memiliki notification badge system yang 
                sama dengan CSS animations, responsive design, dan semua fitur yang sama. Error badge bisa di-dismiss, 
                success badge auto-redirect sesuai destination masing-masing halaman.
            </div>
        </div>

        <div style="text-align: center; color: white; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
            <p>✅ Notification Badge System Implemented on Both Pages!</p>
            <p style="font-size: 12px; opacity: 0.8; margin-top: 10px;">Register & Login pages now have matching badge notifications</p>
        </div>
    </div>
</body>
</html>
