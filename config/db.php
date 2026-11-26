<?php
// config/db.php - Database connection & configuration

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'car_parking_db');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseDir = rtrim(explode('/admin', $scriptDir)[0], '/');
$baseUrl = $protocol . $host . $baseDir;

define('BASE_URL', rtrim($baseUrl, '/') . '/');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Graceful error display
    die("<h3>Database Connection Error:</h3> <p>" . htmlspecialchars($e->getMessage()) . "</p><p>Please make sure MySQL is running in XAMPP/WAMP and <code>database.sql</code> is imported.</p>");
}

// Ensure session save path is valid across drive letters
if (!is_dir(session_save_path()) || !is_writable(session_save_path())) {
    if (is_dir('C:/xampp/tmp') && is_writable('C:/xampp/tmp')) {
        session_save_path('C:/xampp/tmp');
    } else {
        $localSessionDir = __DIR__ . '/../sessions';
        if (!is_dir($localSessionDir)) {
            @mkdir($localSessionDir, 0777, true);
        }
        session_save_path($localSessionDir);
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
