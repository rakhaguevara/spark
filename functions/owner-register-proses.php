<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL . '/owner/register.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$no_hp = trim($_POST['no_hp'] ?? '');
$nama_parkir = trim($_POST['nama_parkir'] ?? '');

if ($nama === '' || $email === '' || $password === '' || $no_hp === '' || $nama_parkir === '') {
    $_SESSION['error'] = 'Semua field wajib diisi';
    header('Location: ' . BASEURL . '/owner/register.php');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Password dan konfirmasi password tidak cocok';
    header('Location: ' . BASEURL . '/owner/register.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password minimal 6 karakter';
    header('Location: ' . BASEURL . '/owner/register.php');
    exit;
}

try {
    $pdo = getDBConnection();

    // Cek email sudah terdaftar
    $stmt = $pdo->prepare(
        "SELECT id_pengguna FROM data_pengguna WHERE email_pengguna = ? LIMIT 1"
    );
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email sudah terdaftar';
        header('Location: ' . BASEURL . '/owner/register.php');
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert owner baru (role = 3 / owner)
    $stmt = $pdo->prepare("
        INSERT INTO data_pengguna 
            (role_pengguna, nama_pengguna, email_pengguna, password_pengguna, noHp_pengguna)
        VALUES 
            (3, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $nama,
        $email,
        $hashedPassword,
        $no_hp
    ]);

    // Get owner ID yang baru dibuat
    $owner_id = $pdo->lastInsertId();

    // Insert data parkir pemilik
    $stmt = $pdo->prepare("
        INSERT INTO owner_parkir 
            (id_owner, nama_parkir)
        VALUES 
            (?, ?)
    ");
    $stmt->execute([
        $owner_id,
        $nama_parkir
    ]);

    $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
    header('Location: ' . BASEURL . '/owner/login.php');
    exit;

} catch (PDOException $e) {
    error_log('OWNER REGISTER ERROR: ' . $e->getMessage());
    $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    header('Location: ' . BASEURL . '/owner/register.php');
    exit;
}
