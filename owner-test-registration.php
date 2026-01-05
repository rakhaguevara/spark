<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

session_start();

// Generate unique test data
$timestamp = time();
$test_owner_id = "test_owner_{$timestamp}";
$test_email = "owner_test_" . $timestamp . "@spark-test.local";
$test_password = "TestPassword123";
$test_nama_parkir = "Test Parkir Unit " . $timestamp;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Registration Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: white; text-align: center; margin-bottom: 40px; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h2 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            flex: 1;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .info-box {
            background: #f0f8ff;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #2c3e50;
        }
        .success-box {
            background: #e6ffe6;
            border-left: 4px solid #2ecc71;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            color: #27ae60;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
        .instructions {
            background: #fff9e6;
            border-left: 4px solid #f39c12;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        ol { margin-left: 20px; }
        li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Owner Registration Feature Test</h1>

        <div class="card">
            <h2>Test Data Generator</h2>
            <div class="info-box">
                <strong>ℹ️ Info:</strong> Form ini akan membuat test data untuk menguji fitur registrasi dan notifikasi badge.
            </div>

            <form id="test-form">
                <div class="form-group">
                    <label>Nama Owner</label>
                    <input type="text" id="nama" placeholder="Nama pemilik parkir" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" placeholder="Contoh: owner@mail.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="password" placeholder="Minimum 6 karakter" required>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="tel" id="no_hp" placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="form-group">
                    <label>Nama Parkir / Lokasi</label>
                    <input type="text" id="nama_parkir" placeholder="Nama tempat parkir" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">Kirim Registrasi</button>
                    <button type="reset" class="btn-secondary">Reset Form</button>
                </div>
            </form>

            <div class="instructions">
                <strong>📋 Instruksi Testing:</strong>
                <ol>
                    <li>Isi semua field form di atas dengan data test</li>
                    <li>Klik "Kirim Registrasi"</li>
                    <li>Lihat notifikasi badge di atas kanan layar:</li>
                    <ul style="margin: 10px 0 10px 20px;">
                        <li>✅ <strong>SUKSES</strong>: Badge hijau akan muncul dengan countdown 3 detik lalu otomatis redirect ke login</li>
                        <li>❌ <strong>ERROR</strong>: Badge merah akan menunjukkan pesan error (contoh: email sudah terdaftar)</li>
                    </ul>
                    <li>Setelah registrasi sukses, cek database phpMyAdmin untuk verifikasi data</li>
                    <li>Login dengan email dan password yang baru didaftarkan</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <h2>Test Data Reference</h2>
            <div class="info-box">
                <strong>Default Test Account (Already Registered):</strong><br>
                Email: <code>owner@parkir.com</code><br>
                Password: <code>123456</code><br>
                <small>Gunakan akun ini untuk test login setelah registration</small>
            </div>

            <div class="success-box">
                <strong>Database Info:</strong><br>
                phpMyAdmin: <a href="http://localhost:8081" target="_blank">http://localhost:8081</a><br>
                Username: <code>root</code><br>
                Password: <code>rootpassword</code><br>
                Database: <code>spark</code>
            </div>
        </div>

        <div class="card">
            <h2>Feature Checklist</h2>
            <div style="line-height: 2; font-size: 14px; color: #555;">
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Notifikasi badge berhasil muncul saat registrasi sukses
                </label>
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Notifikasi menampilkan pesan "Registrasi Berhasil!" dengan icon ✓
                </label>
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Countdown timer berjalan (3, 2, 1, 0)
                </label>
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Setelah countdown selesai, otomatis redirect ke login.php
                </label>
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Data berhasil tersimpan di database (check phpMyAdmin)
                </label>
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Error badge muncul dengan pesan jika ada error (email duplicate, password tidak match, dll)
                </label>
                <label style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox"> Bisa login dengan akun yang baru didaftarkan
                </label>
                <label style="display: flex; gap: 10px;">
                    <input type="checkbox"> Dashboard menampilkan data owner dari database
                </label>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('test-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData();
            formData.append('nama', document.getElementById('nama').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('confirm_password', document.getElementById('password').value);
            formData.append('no_hp', document.getElementById('no_hp').value);
            formData.append('nama_parkir', document.getElementById('nama_parkir').value);

            try {
                const response = await fetch('/functions/owner-register-proses.php', {
                    method: 'POST',
                    body: formData
                });

                // Wait for redirect or session to be set
                setTimeout(() => {
                    window.location.href = '/owner/register.php';
                }, 500);

            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
            }
        });
    </script>
</body>
</html>
