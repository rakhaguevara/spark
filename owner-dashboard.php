<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

session_start();

// Get statistics
$pdo = getDBConnection();

// Count owners
$stmt = $pdo->query("SELECT COUNT(*) as total FROM data_pengguna WHERE role_pengguna = 3");
$total_owners = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count parkir
$stmt = $pdo->query("SELECT COUNT(*) as total FROM owner_parkir");
$total_parkir = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent owners
$stmt = $pdo->query("
    SELECT dp.id_pengguna, dp.nama_pengguna, dp.email_pengguna, op.nama_parkir
    FROM data_pengguna dp
    LEFT JOIN owner_parkir op ON dp.id_pengguna = op.id_owner
    WHERE dp.role_pengguna = 3
    ORDER BY dp.id_pengguna DESC
    LIMIT 5
");
$recent_owners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Module - Quick Start Dashboard</title>
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
            font-size: 32px;
        }
        .subtitle {
            color: rgba(255,255,255,0.9);
            text-align: center;
            margin-bottom: 40px;
            font-size: 16px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .card h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn {
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            font-size: 14px;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .btn-success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
        }
        .feature-list {
            background: #f0f8ff;
            border-left: 4px solid #3498db;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .feature-list li {
            margin-bottom: 6px;
            color: #2c3e50;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            font-size: 13px;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
            color: #555;
        }
        .table tr:hover {
            background: #f9f9f9;
        }
        .icon {
            font-size: 24px;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .highlight {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe6cc 100%);
            border-left: 4px solid #f39c12;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .highlight strong {
            color: #d68910;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Owner Parkir Module Dashboard</h1>
        <p class="subtitle">Sistem registrasi dan login owner dengan notification badge</p>

        <div class="highlight">
            <strong>🚀 Status:</strong> Owner module sudah production-ready dengan notification badge system!
            Notification badge menampilkan pesan error/success secara real-time saat user registrasi.
        </div>

        <div class="grid">
            <!-- Card 1: Registration -->
            <div class="card">
                <h2><span class="icon">📋</span> Registrasi Owner</h2>
                <p>Halaman registrasi owner dengan form validation dan notification badge system untuk error/success messages.</p>
                <div class="feature-list">
                    <strong>✨ Fitur:</strong>
                    <ul>
                        <li>✅ Form validation (email, password, fields)</li>
                        <li>✅ Error badge merah dengan dismiss button</li>
                        <li>✅ Success badge hijau dengan countdown</li>
                        <li>✅ Auto-redirect ke login</li>
                        <li>✅ Password hashing (bcrypt)</li>
                    </ul>
                </div>
                <div class="button-group">
                    <a href="<?= BASEURL ?>/owner/register.php" class="btn btn-primary">Buka Registrasi</a>
                </div>
            </div>

            <!-- Card 2: Login -->
            <div class="card">
                <h2><span class="icon">🔐</span> Login Owner</h2>
                <p>Halaman login untuk owner dengan session management dan password verification.</p>
                <div class="feature-list">
                    <strong>✨ Fitur:</strong>
                    <ul>
                        <li>✅ Email & password authentication</li>
                        <li>✅ Session management</li>
                        <li>✅ Password verify (bcrypt)</li>
                        <li>✅ Auto-redirect ke dashboard</li>
                        <li>✅ "Remember me" optional</li>
                    </ul>
                </div>
                <div class="button-group">
                    <a href="<?= BASEURL ?>/owner/login.php" class="btn btn-primary">Buka Login</a>
                </div>
            </div>

            <!-- Card 3: Dashboard -->
            <div class="card">
                <h2><span class="icon">📊</span> Dashboard Owner</h2>
                <p>Dashboard untuk owner menampilkan statistik parkir dan data bisnis mereka.</p>
                <div class="feature-list">
                    <strong>✨ Fitur:</strong>
                    <ul>
                        <li>✅ Summary cards (Total, Aktif, Income)</li>
                        <li>✅ Query database untuk real data</li>
                        <li>✅ Session protection</li>
                        <li>✅ Owner-only access</li>
                        <li>✅ Logout functionality</li>
                    </ul>
                </div>
                <div class="button-group">
                    <a href="<?= BASEURL ?>/owner/dashboard.php" class="btn btn-primary">Buka Dashboard</a>
                </div>
            </div>

            <!-- Card 4: Test Error Badges -->
            <div class="card">
                <h2><span class="icon">🧪</span> Test Error Badges</h2>
                <p>Testing page dengan 5 pre-configured scenarios untuk test error dan success notifications.</p>
                <div class="feature-list">
                    <strong>✨ Test Cases:</strong>
                    <ul>
                        <li>❌ Email sudah terdaftar</li>
                        <li>❌ Password tidak cocok</li>
                        <li>❌ Password terlalu pendek</li>
                        <li>✅ Registrasi berhasil</li>
                        <li>📋 Manual test form</li>
                    </ul>
                </div>
                <div class="button-group">
                    <a href="<?= BASEURL ?>/owner-test-error-badges.php" class="btn btn-success">Test Error Scenarios</a>
                </div>
            </div>

            <!-- Card 5: System Verification -->
            <div class="card">
                <h2><span class="icon">✅</span> System Test</h2>
                <p>Halaman verifikasi lengkap untuk check database, tables, functions, dan code quality.</p>
                <div class="feature-list">
                    <strong>✨ Check:</strong>
                    <ul>
                        <li>✅ Database connection</li>
                        <li>✅ Tables existence</li>
                        <li>✅ Auth functions</li>
                        <li>✅ Session status</li>
                        <li>✅ Code analysis</li>
                    </ul>
                </div>
                <div class="button-group">
                    <a href="<?= BASEURL ?>/owner-test-flow.php" class="btn btn-success">Buka Verification</a>
                </div>
            </div>

            <!-- Card 6: Database -->
            <div class="card">
                <h2><span class="icon">💾</span> phpMyAdmin</h2>
                <p>Access database langsung untuk verifikasi data owner yang terdaftar.</p>
                <div class="feature-list">
                    <strong>📊 Database Info:</strong>
                    <ul>
                        <li>Host: localhost:3308</li>
                        <li>Username: root</li>
                        <li>Password: rootpassword</li>
                        <li>Database: spark</li>
                    </ul>
                </div>
                <div class="button-group">
                    <a href="http://localhost:8081" target="_blank" class="btn btn-secondary">Buka phpMyAdmin</a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card full-width">
                <h2><span class="icon">📈</span> Statistics</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div>
                        <div class="stat-number"><?= $total_owners ?></div>
                        <div class="stat-label">Total Owner Terdaftar</div>
                    </div>
                    <div>
                        <div class="stat-number"><?= $total_parkir ?></div>
                        <div class="stat-label">Total Parkir</div>
                    </div>
                    <div>
                        <div class="stat-number" style="color: #2ecc71;"><?= $total_owners > 0 ? '✓' : '○' ?></div>
                        <div class="stat-label">System Status</div>
                    </div>
                </div>

                <h3 style="color: #333; margin-top: 20px; margin-bottom: 12px;">Recent Registered Owners</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Owner</th>
                            <th>Email</th>
                            <th>Nama Parkir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_owners as $owner): ?>
                            <tr>
                                <td><?= htmlspecialchars($owner['id_pengguna']) ?></td>
                                <td><?= htmlspecialchars($owner['nama_pengguna']) ?></td>
                                <td><code><?= htmlspecialchars($owner['email_pengguna']) ?></code></td>
                                <td><?= htmlspecialchars($owner['nama_parkir'] ?? '-') ?></td>
                                <td><span class="status-badge active">Active</span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_owners)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999;">No data yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Documentation -->
            <div class="card full-width">
                <h2><span class="icon">📚</span> Documentation & Files</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <strong style="color: #333; display: block; margin-bottom: 8px;">📄 Main Files:</strong>
                        <ul style="font-size: 13px; color: #666; line-height: 1.8;">
                            <li><code>/owner/register.php</code></li>
                            <li><code>/owner/login.php</code></li>
                            <li><code>/owner/dashboard.php</code></li>
                            <li><code>/owner/logout.php</code></li>
                        </ul>
                    </div>
                    <div>
                        <strong style="color: #333; display: block; margin-bottom: 8px;">⚙️ Functions:</strong>
                        <ul style="font-size: 13px; color: #666; line-height: 1.8;">
                            <li><code>/functions/owner-auth.php</code></li>
                            <li><code>/functions/owner-login-proses.php</code></li>
                            <li><code>/functions/owner-register-proses.php</code></li>
                        </ul>
                    </div>
                    <div>
                        <strong style="color: #333; display: block; margin-bottom: 8px;">📖 Documentation:</strong>
                        <ul style="font-size: 13px; color: #666; line-height: 1.8;">
                            <li><code>OWNER_NOTIFICATION_BADGE_GUIDE.md</code></li>
                            <li><code>OWNER_MODULE.md</code></li>
                            <li><code>00_START_HERE.md</code></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Start Guide -->
            <div class="card full-width" style="background: linear-gradient(135deg, #e6ffe6 0%, #f0fff4 100%); border: 2px solid #2ecc71;">
                <h2 style="color: #27ae60;"><span class="icon">🚀</span> Quick Start Guide</h2>
                <ol style="font-size: 14px; color: #555; line-height: 2; margin-left: 20px;">
                    <li><strong>Test Error Badge:</strong> Klik "Test Error Scenarios" → Pilih test case → Lihat badge error muncul</li>
                    <li><strong>Test Success Badge:</strong> Registrasi dengan email baru → Lihat badge hijau + countdown 3 detik → Auto-redirect ke login</li>
                    <li><strong>Verify Data:</strong> Buka phpMyAdmin → Check data_pengguna & owner_parkir tables</li>
                    <li><strong>Login Test:</strong> Login dengan email yang baru didaftarkan → Lihat dashboard dengan data dari database</li>
                    <li><strong>System Check:</strong> Klik "System Test" untuk verifikasi lengkap database & functions</li>
                </ol>
            </div>
        </div>

        <div style="text-align: center; color: white; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
            <p>✅ Owner Module dengan Notification Badge System - Production Ready!</p>
            <p style="font-size: 12px; opacity: 0.8; margin-top: 10px;">Last updated: <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
