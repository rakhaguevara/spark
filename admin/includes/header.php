<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/admin-auth.php';

startSession();
requireAdminLogin();

$admin = getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SPARK</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/admin.css">

    <style>
        /* Force dark background for admin pages */
        html,
        body {
            background: #1a1a1a !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background: #1a1a1a;">