<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

session_start();

$test_results = [];

// Test 1: Try register dengan email yang sudah ada
$test_results[] = [
    'name' => 'Email Sudah Terdaftar',
    'email' => 'owner@parkir.com',
    'password' => 'TestPass123',
    'description' => 'Coba register dengan email yang sudah terdaftar di sistem'
];

// Test 2: Password tidak match
$test_results[] = [
    'name' => 'Password Tidak Cocok',
    'email' => 'newowner@test.com',
    'password' => 'DifferentPass',
    'description' => 'Coba register dengan password dan konfirmasi yang berbeda'
];

// Test 3: Password terlalu pendek
$test_results[] = [
    'name' => 'Password Terlalu Pendek',
    'email' => 'newowner2@test.com',
    'password' => '123',
    'description' => 'Coba register dengan password kurang dari 6 karakter'
];

// Test 4: Field kosong
$test_results[] = [
    'name' => 'Field Kosong',
    'email' => '',
    'password' => '',
    'description' => 'Coba submit form dengan field kosong'
];

// Test 5: Registrasi berhasil
$test_results[] = [
    'name' => 'Registrasi Berhasil',
    'email' => 'owner_success_' . time() . '@test.com',
    'password' => 'SuccessPass123',
    'description' => 'Coba register dengan data valid dan baru'
];

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Badge Testing - Owner Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .test-scenario {
            background: #f9f9f9;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .test-scenario:hover {
            background: #f0f0f0;
            border-left-color: #764ba2;
            transform: translateX(5px);
        }

        .test-scenario h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 6px;
        }

        .test-scenario p {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .test-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 4px;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-test {
            background: #667eea;
            color: white;
            flex: 1;
        }

        .btn-test:hover {
            background: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .badge-demo {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .demo-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .demo-item {
            background: linear-gradient(135deg, rgba(230, 126, 34, 0.1) 0%, rgba(192, 57, 43, 0.1) 100%);
            border: 1px solid #e74c3c;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .demo-item strong {
            color: #c0392b;
        }

        .success-demo {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.1) 0%, rgba(39, 174, 96, 0.1) 100%);
            border-color: #2ecc71;
        }

        .success-demo strong {
            color: #27ae60;
        }

        .feature-list {
            background: #f0f8ff;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.8;
            color: #2c3e50;
        }

        .feature-list ul {
            margin-left: 20px;
            margin-top: 10px;
        }

        .feature-list li {
            margin-bottom: 8px;
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
    </style>
</head>

<body>
    <div class="container">
        <h1>🔐 Error Badge Testing - Owner Registration</h1>

        <div class="card">
            <h2>Test Error Scenarios</h2>
            <div class="feature-list">
                <strong>✨ Fitur Badge Notifikasi:</strong>
                <ul>
                    <li>🎨 <strong>Error Badge (Merah):</strong> Muncul otomatis saat ada error registrasi dengan animasi pulse</li>
                    <li>✓ <strong>Success Badge (Hijau):</strong> Muncul saat registrasi berhasil, auto-redirect ke login dalam 3 detik</li>
                    <li>✕ <strong>Dismiss Button:</strong> Error badge bisa ditutup manual dengan klik tombol X</li>
                    <li>💬 <strong>Error Messages:</strong> Pesan error spesifik ditampilkan (email duplicate, password tidak match, dll)</li>
                </ul>
            </div>

            <?php foreach ($test_results as $index => $test): ?>
                <div class="test-scenario">
                    <h3><?= htmlspecialchars($test['name']) ?></h3>
                    <p><?= htmlspecialchars($test['description']) ?></p>

                    <form method="POST" action="<?= BASEURL ?>/functions/owner-register-proses.php" target="_blank" class="test-form">
                        <div class="form-group">
                            <label>Nama Owner</label>
                            <input type="text" name="nama" value="Test Owner <?= $index + 1 ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($test['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" value="<?= htmlspecialchars($test['password']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="confirm_password" value="<?php
                                                                                    // For test 2, use different password
                                                                                    echo ($test['name'] === 'Password Tidak Cocok') ? 'DifferentPass99' : htmlspecialchars($test['password']);
                                                                                    ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="tel" name="no_hp" value="0812345678<?= str_pad($index, 2, '0', STR_PAD_LEFT) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Nama Parkir</label>
                            <input type="text" name="nama_parkir" value="Test Parkir <?= $index + 1 ?>" required>
                        </div>

                        <div class="button-group">
                            <button type="submit" class="btn-test">Test: <?= htmlspecialchars($test['name']) ?></button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>

            <div class="badge-demo">
                <div class="demo-title">📋 Pesan Error Yang Akan Ditampilkan</div>

                <div class="demo-item">
                    <strong>❌ Email Sudah Terdaftar</strong><br>
                    <small>Saat email sudah ada di database</small>
                </div>

                <div class="demo-item">
                    <strong>❌ Password dan Konfirmasi Password Tidak Cocok</strong><br>
                    <small>Saat password dan confirm password berbeda</small>
                </div>

                <div class="demo-item">
                    <strong>❌ Password Minimal 6 Karakter</strong><br>
                    <small>Saat password kurang dari 6 karakter</small>
                </div>

                <div class="demo-item">
                    <strong>❌ Semua Field Wajib Diisi</strong><br>
                    <small>Saat ada field yang kosong</small>
                </div>

                <div class="demo-item success-demo">
                    <strong>✅ Registrasi Berhasil! Akun Owner Anda Telah Dibuat</strong><br>
                    <small>Saat semua validasi lolos dan data tersimpan di database</small><br>
                    <small style="display: block; margin-top: 5px; font-style: italic;">→ Auto-redirect ke login dalam 3 detik</small>
                </div>
            </div>

            <div class="note">
                <strong>💡 Catatan:</strong> Setiap klik tombol test akan membuka halaman registrasi di tab baru. Badge error/success akan muncul otomatis sesuai hasil validasi. Jika error, user bisa menutup badge dengan klik tombol X atau coba lagi dengan data yang benar.
            </div>
        </div>

        <div class="card">
            <h2>Instruksi Penggunaan</h2>
            <ol style="color: #555; line-height: 2; font-size: 14px; margin-left: 20px;">
                <li>Pilih test scenario di atas (Error atau Success)</li>
                <li>Data sudah terisi otomatis sesuai scenario</li>
                <li>Klik tombol "Test: [Nama Test]"</li>
                <li>Halaman registrasi akan terbuka, form sudah terisi</li>
                <li>Klik tombol "Daftar sebagai Owner"</li>
                <li>Lihat badge notifikasi di atas kanan layar:
                    <ul style="margin: 8px 0 8px 20px;">
                        <li>❌ Error badge (merah) → Bisa ditutup dengan klik X atau coba lagi</li>
                        <li>✅ Success badge (hijau) → Countdown 3 detik → Auto-redirect ke login</li>
                    </ul>
                </li>
                <li>Cek database phpMyAdmin untuk verifikasi data tersimpan</li>
            </ol>
        </div>

        <div class="card">
            <h2>Links Penting</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="<?= BASEURL ?>/owner/register.php" target="_blank" style="padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; text-align: center; font-weight: 600;">
                    📋 Halaman Registrasi
                </a>
                <a href="<?= BASEURL ?>/owner/login.php" target="_blank" style="padding: 15px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; text-decoration: none; border-radius: 6px; text-align: center; font-weight: 600;">
                    🔐 Halaman Login
                </a>
                <a href="http://localhost:8081" target="_blank" style="padding: 15px; background: linear-gradient(135deg, #f39c12 0%, #d68910 100%); color: white; text-decoration: none; border-radius: 6px; text-align: center; font-weight: 600;">
                    💾 phpMyAdmin
                </a>
                <a href="<?= BASEURL ?>/owner-test-flow.php" target="_blank" style="padding: 15px; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; text-decoration: none; border-radius: 6px; text-align: center; font-weight: 600;">
                    ✅ System Test
                </a>
            </div>
        </div>
    </div>
</body>

</html>