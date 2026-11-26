<?php
// admin/logout.php
require_once __DIR__ . '/../config/db.php';
$_SESSION = [];
if (session_id()) {
    session_destroy();
}
header('Location: ' . BASE_URL . 'admin/login.php');
exit;
