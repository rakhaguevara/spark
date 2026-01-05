<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../functions/owner-auth.php';

startSession();

// Redirect jika sudah login
if (isOwnerLoggedIn()) {
    header('Location: ' . BASEURL . '/owner/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login | SPARK</title>

    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/login-style.css">

    <style>
        /* Notification Badge Styles */
        .notification-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 14px;
            z-index: 9999;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.4s ease-out;
            max-width: 380px;
        }

        .notification-badge.success {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }

        .notification-badge.error {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            animation: slideIn 0.4s ease-out, pulse 2s ease-in-out 0.5s;
        }

        .notification-badge svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        .notification-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .notification-title {
            font-weight: 700;
            font-size: 15px;
        }

        .notification-message {
            font-weight: 500;
            font-size: 13px;
            opacity: 0.95;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 24px;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
            transition: opacity 0.2s;
            flex-shrink: 0;
        }

        .notification-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }

            50% {
                box-shadow: 0 10px 40px rgba(231, 76, 60, 0.4);
            }
        }

        .notification-badge.fadeOut {
            animation: slideOut 0.4s ease-out forwards;
        }

        @media (max-width: 480px) {
            .notification-badge {
                max-width: calc(100vw - 40px);
                right: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-left">
            <div class="login-box">

                <!-- SWITCH -->
                <div class="login-switch">
                    <button type="button" class="switch-btn active">
                        Owner Login
                    </button>
                    <button type="button" class="switch-btn"
                        onclick="window.location.href='<?= BASEURL ?>/owner/register.php'">
                        Owner Sign Up
                    </button>
                </div>

                <h1>Selamat Datang Owner Parkir!</h1>
                <p class="subtitle">Urus parkiran mu lebih mudah dan fleksibel.</p>

                <!-- NOTIFICATION BADGES -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="notification-badge error" id="notification-badge">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="notification-content">
                            <div class="notification-title">Login Gagal</div>
                            <div class="notification-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
                        </div>
                        <button type="button" class="notification-close" onclick="document.getElementById('notification-badge')?.remove()">✕</button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="notification-badge success" id="notification-badge">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="notification-content">
                            <div class="notification-title">✓ Login Berhasil!</div>
                            <div class="notification-message">Selamat datang, owner parkir!</div>
                        </div>
                    </div>
                    <script>
                        // Auto redirect to dashboard
                        setTimeout(() => {
                            window.location.href = '<?= BASEURL ?>/owner/dashboard.php';
                        }, 500);
                    </script>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- LOGIN FORM -->
                <form action="<?= BASEURL ?>/functions/owner-login-proses.php" method="POST">

                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="owner@spark.com" autofocus>

                    <div class="password-row">
                        <label>Password</label>
                    </div>

                    <input type="password" name="password" required placeholder="Masukkan password anda">

                    <button type="submit" class="btn-primary">Log In</button>
                </form>

                <p class="signup-text">
                    Belum punya akun?
                    <a href="<?= BASEURL ?>/owner/register.php">Daftar sebagai Owner</a>
                </p>

                <p class="signup-text">
                    <a href="<?= BASEURL ?>">
                        <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
                    </a>
                </p>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="login-right">
            <div class="login-bg-slider active"></div>
            <div class="login-bg-slider next"></div>

            <div class="quote-card">
                <p class="quote">
                    "SPARK Owner Panel memudahkan pengelolaan parkiran Anda—dari monitoring ruang parkir,
                    mengelola tarif, melacak transaksi, hingga menganalisis statistik penggunaan untuk
                    pengambilan keputusan yang lebih baik."
                </p>
                <div class="author">
                    <strong>SPARK Owner Team</strong>
                    <span>Mitra Pemilik</span>
                    <span>SPARK Management System</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BACKGROUND SLIDE SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const images = [
                '<?= BASEURL ?>/assets/img/login1.jpg',
                '<?= BASEURL ?>/assets/img/login2.jpg',
                '<?= BASEURL ?>/assets/img/login3.jpg'
            ];

            let index = 0;
            let active = document.querySelector('.login-bg-slider.active');
            let next = document.querySelector('.login-bg-slider.next');

            active.style.backgroundImage = `url(${images[index]})`;

            setInterval(() => {
                const nextIndex = (index + 1) % images.length;
                next.style.backgroundImage = `url(${images[nextIndex]})`;

                active.classList.add('slide-out');
                next.classList.add('slide-in');

                setTimeout(() => {
                    active.classList.remove('slide-out');
                    next.classList.remove('slide-in');

                    active.classList.remove('active');
                    active.classList.add('next');

                    next.classList.remove('next');
                    next.classList.add('active');

                    [active, next] = [next, active];
                    index = nextIndex;
                }, 800);

            }, 4000);
        });
    </script>

</body>

</html>