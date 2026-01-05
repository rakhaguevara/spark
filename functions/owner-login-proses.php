<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL . '/owner/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['error'] = 'Email dan password wajib diisi';
    header('Location: ' . BASEURL . '/owner/login.php');
    exit;
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT 
            dp.id_pengguna,
            dp.nama_pengguna,
            dp.email_pengguna,
            dp.password_pengguna,
            dp.role_pengguna,
            rp.nama_role
        FROM data_pengguna dp
        JOIN role_pengguna rp ON dp.role_pengguna = rp.id_role
        WHERE dp.email_pengguna = ? AND rp.nama_role = 'owner'
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$owner) {
        $_SESSION['error'] = 'Email tidak terdaftar atau bukan owner';
        header('Location: ' . BASEURL . '/owner/login.php');
        exit;
    }

    if ($owner['password_pengguna'] !== $password && !password_verify($password, $owner['password_pengguna'])) {
        $_SESSION['error'] = 'Password salah';
        header('Location: ' . BASEURL . '/owner/login.php');
        exit;
    }

    $_SESSION['owner_id'] = $owner['id_pengguna'];
    $_SESSION['owner'] = [
        'id' => $owner['id_pengguna'],
        'nama' => $owner['nama_pengguna'],
        'email' => $owner['email_pengguna'],
        'role' => $owner['nama_role']
    ];

    $_SESSION['success'] = 'Login berhasil! Selamat datang ' . $owner['nama_pengguna'];
    header('Location: ' . BASEURL . '/owner/dashboard.php');
    exit;
} catch (PDOException $e) {
    error_log('OWNER LOGIN ERROR: ' . $e->getMessage());
    $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    header('Location: ' . BASEURL . '/owner/login.php');
    exit;
}
