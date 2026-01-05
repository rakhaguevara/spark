<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isOwnerLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['owner_id']);
}

function getCurrentOwner()
{
    if (!isOwnerLoggedIn()) return null;

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT dp.*, rp.nama_role, op.id_owner_parkir, op.nama_parkir
        FROM data_pengguna dp
        JOIN role_pengguna rp ON dp.role_pengguna = rp.id_role
        LEFT JOIN owner_parkir op ON dp.id_pengguna = op.id_owner
        WHERE dp.id_pengguna = ? AND rp.nama_role = 'owner'
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['owner_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function requireOwnerLogin()
{
    startSession();
    if (!isOwnerLoggedIn()) {
        header('Location: ' . BASEURL . '/owner/login.php');
        exit;
    }
}

function logoutOwner()
{
    startSession();
    unset($_SESSION['owner_id']);
    unset($_SESSION['owner']);
    session_destroy();
    header('Location: ' . BASEURL . '/owner/login.php');
    exit;
}
