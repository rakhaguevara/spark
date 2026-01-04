<?php
// Enable error reporting (DEV ONLY)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load config
require_once __DIR__ . '/../config/app.php';

// Start session
session_start();

// Debug log
error_log("=== LOGIN PAGE LOADED ===");
error_log("BASEURL: " . BASEURL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | SPARK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/login-style.css">
</head>
<body>

<div class="login-container">
    <div class="login-left">
        <div class="login-box">

            <!-- SWITCH -->
            <div class="login-switch">
                <button type="button" class="switch-btn active"
                        onclick="window.location.href='<?= BASEURL ?>/pages/login.php'">
                    Login
                </button>
                <button type="button" class="switch-btn"
                        onclick="window.location.href='<?= BASEURL ?>/pages/register.php'">
                    Sign Up
                </button>
            </div>

            <h1>Welcome to SPARK!</h1>
            <p class="subtitle">Please enter your details to login.</p>

            <!-- ERROR MESSAGE -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- SUCCESS MESSAGE -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-success">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <form action="<?= BASEURL ?>/functions/login-proses.php" method="POST">

                <label>Email Address</label>
                <input type="email" name="email" required placeholder="your@email.com">

                <div class="password-row">
                    <label>Password</label>
                    <a href="<?= BASEURL ?>/pages/forgot-password.php">
                        Forgot Password?
                    </a>
                </div>

                <input type="password" name="password" required placeholder="Enter your password">

                <button type="submit" class="btn-primary">Log In</button>

                <div class="divider"><span>OR</span></div>

                <!-- SOCIAL LOGIN BUTTONS -->
                <div class="social-buttons">
                    <!-- Google (Active) -->
                    <button type="button" class="btn-social" onclick="alert('Google login coming soon!')">
                        <img src="<?= BASEURL ?>/assets/img/google.svg" alt="Google">
                        Continue with Google
                    </button>

                    <!-- Apple (Disabled) -->
                    <button type="button" class="btn-social" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                        </svg>
                        Continue with Apple
                        <span class="coming-soon">Coming Soon</span>
                    </button>

                    <!-- MetaMask (Disabled) -->
                    <button type="button" class="btn-social" disabled>
                        <svg width="20" height="20" viewBox="0 0 40 40" fill="none">
                            <path d="M32.96 3.08L20.68 12.36l2.26-5.32 10.02-4.96z" fill="#E17726"/>
                            <path d="M7.04 3.08l12.16 9.36-2.14-5.4L7.04 3.08zM28.24 28.64l-3.26 4.98 6.98 1.92 2-6.76-5.72-.14zM5.06 28.78l2 6.76 6.98-1.92-3.26-4.98-5.72.14z" fill="#E27625"/>
                            <path d="M13.52 17.64l-1.92 2.9 6.92.32-.24-7.44-4.76 4.22zM26.48 17.64l-4.84-4.3-.16 7.52 6.92-.32-1.92-2.9zM14.04 33.62l4.16-2.02-3.58-2.8-.58 4.82zM21.8 31.6l4.16 2.02-.58-4.82-3.58 2.8z" fill="#E27625"/>
                            <path d="M25.96 33.62l-4.16-2.02.34 2.72-.04 1.16 3.86-1.86zM14.04 33.62l3.86 1.86-.02-1.16.32-2.72-4.16 2.02z" fill="#D5BFB2"/>
                            <path d="M17.98 25.48l-3.44-1.02 2.44-1.12 1 2.14zM22.02 25.48l1-2.14 2.46 1.12-3.46 1.02z" fill="#233447"/>
                            <path d="M14.04 33.62l.6-4.98-3.86.14 3.26 4.84zM25.36 28.64l.6 4.98 3.26-4.84-3.86-.14zM28.4 20.54l-6.92.32.64 3.62 1-2.14 2.46 1.12 2.82-2.92zM14.54 23.46l2.44-1.12 1 2.14.64-3.62-6.92-.32 2.84 2.92z" fill="#CC6228"/>
                            <path d="M11.6 20.54l2.98 5.82-.1-2.9-2.88-2.92zM25.58 23.46l-.12 2.9 2.98-5.82-2.86 2.92zM18.62 20.86l-.64 3.62.8 4.14.18-5.44-.34-2.32zM21.38 20.86l-.32 2.3.14 5.46.8-4.14-.62-3.62z" fill="#E27525"/>
                            <path d="M22.02 25.48l-.8 4.14.58.4 3.58-2.8.12-2.9-3.48 1.16zM14.54 23.46l.1 2.9 3.58 2.8.58-.4-.8-4.14-3.46-1.16z" fill="#F5841F"/>
                            <path d="M22.08 35.48l.04-1.16-.32-.28h-3.6l-.3.28.02 1.16-3.86-1.86 1.36 1.1 2.72 1.88h3.68l2.74-1.88 1.36-1.1-3.84 1.86z" fill="#C0AC9D"/>
                            <path d="M21.8 31.6l-.58-.4h-2.44l-.58.4-.32 2.72.3-.28h3.6l.32.28-.3-2.72z" fill="#161616"/>
                            <path d="M33.48 12.92l1.02-4.9-1.54-4.6-11.76 8.72 4.52 3.82 6.38 1.86 1.42-1.64-.62-.44.98-.9-.76-.58.98-.74-.64-.5zM5.5 8.02l1.02 4.9-.66.5.98.74-.74.58.98.9-.62.44 1.4 1.64 6.38-1.86 4.52-3.82L6.98 3.42 5.5 8.02z" fill="#763E1A"/>
                            <path d="M32.02 19.4l-6.38-1.86 1.92 2.9-2.98 5.82 3.92-.06h5.86l-2.34-6.8zM13.52 17.54l-6.38 1.86-2.32 6.8h5.84l3.92.06-2.98-5.82 1.92-2.9zM21.38 20.86l.42-7.22 1.88-5.08h-8.36l1.86 5.08.44 7.22.14 2.34.02 5.42h2.44l.02-5.42.14-2.34z" fill="#F5841F"/>
                        </svg>
                        Continue with MetaMask
                        <span class="coming-soon">Coming Soon</span>
                    </button>
                </div>
            </form>

            <p class="signup-text">
                Don't have an account yet?
                <a href="<?= BASEURL ?>/pages/register.php">Sign Up</a>
            </p>
        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="login-right">
        <div class="login-bg-slider active"></div>
        <div class="login-bg-slider next"></div>

        <div class="quote-card">
            <p class="quote">
                "With SPARK, I can manage my parking effortlessly—from finding available spaces nearby,
                reserving a spot in advance, to parking with confidence without wasting time."
            </p>
            <div class="author">
                <strong>Stephen Curry</strong>
                <span>Professional Athlete</span>
                <span>PARKSTER since 2022</span>
            </div>
        </div>
    </div>
</div>

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

    if (!active || !next) return;

    active.style.backgroundImage = `url(${images[index]})`;

    setInterval(() => {
        const nextIndex = (index + 1) % images.length;
        next.style.backgroundImage = `url(${images[nextIndex]})`;

        active.classList.add('slide-out');
        next.classList.add('slide-in');

        setTimeout(() => {
            active.classList.remove('slide-out', 'active');
            next.classList.remove('slide-in', 'next');

            active.classList.add('next');
            next.classList.add('active');

            [active, next] = [next, active];
            index = nextIndex;
        }, 800);
    }, 4000);
});
</script>

</body>
</html>
