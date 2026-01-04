<?php
require_once __DIR__ . '/../config/app.php';

// DEBUG (hapus jika sudah production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register | SPARK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/login-style.css">
</head>

<body>

    <div class="login-container">

        <!-- LEFT -->
        <div class="login-left">
            <div class="login-box">

                <!-- SWITCH -->
                <div class="login-switch">
                    <button type="button" class="switch-btn"
                        onclick="window.location.href='<?= BASEURL ?>/pages/login.php'">
                        Login
                    </button>

                    <button type="button" class="switch-btn active"
                        onclick="window.location.href='<?= BASEURL ?>/pages/register.php'">
                        Sign Up
                    </button>
                </div>

                <h1>Welcome New Member!</h1>
                <p class="subtitle">
                    Welcome to SPARK,<br>
                    Your journey starts here
                </p>

                <!-- FORM REGISTER -->
                <form action="<?= BASEURL ?>/functions/register-proses.php" method="POST">

                    <label>Username</label>
                    <input type="text" name="nama" placeholder="Enter your username" required>

                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email address" required>

                    <label>Password</label>
                    <input type="password" name="password" placeholder="Create a password" required>

                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm your password" required>

                    <label>Phone Number</label>
                    <input type="tel" name="no_hp" placeholder="Enter your phone number" required>

                    <button type="submit" class="btn-primary">Sign Up</button>

                    <div class="divider">OR</div>
                </form>

                <p class="signup-text">
                    Already have an account?
                    <a href="<?= BASEURL ?>/pages/login.php">Login</a>
                </p>

            </div>
        </div>

        <!-- RIGHT -->
        <div class="login-right">

            <!-- BACKGROUND SLIDER -->
            <div class="login-bg-slider active"></div>
            <div class="login-bg-slider next"></div>

            <!-- QUOTE -->
            <div class="quote-card">
                <p class="quote">
                    “With SPARK, I can manage my parking effortlessly—from finding available spaces nearby,
                    reserving a spot in advance, to parking with confidence without wasting time.”
                </p>

                <div class="author">
                    <strong>Stephen Curry</strong>
                    <span>Professional Athlete</span>
                    <span>PARKSTER since 2022</span>
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