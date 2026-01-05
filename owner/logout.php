<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../functions/owner-auth.php';

startSession();

if (!isOwnerLoggedIn()) {
    header('Location: ' . BASEURL . '/owner/login.php');
    exit;
}

logoutOwner();
