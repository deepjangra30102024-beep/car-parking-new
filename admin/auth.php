<?php
// admin/auth.php - Session guard for admin pages
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}
