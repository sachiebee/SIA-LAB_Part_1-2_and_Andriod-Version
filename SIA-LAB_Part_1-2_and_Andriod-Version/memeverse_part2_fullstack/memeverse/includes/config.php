<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt');

// Database configuration - CHANGE THESE
define('DB_HOST', 'localhost');
define('DB_NAME', 'memeverse');
define('DB_USER', 'root');
define('DB_PASS', 'rooting3');
define('DB_PORT', 3308); // Change to your MySQL port

// Auto-detect site URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('SITE_URL', $protocol . '://' . $host . $uri . '/');
error_log("SITE_URL = " . SITE_URL);

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('AVATAR_DIR', __DIR__ . '/../avatars/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('DEFAULT_AVATAR', SITE_URL . 'avatars/default.png');

// Email (SMTP) - configure for production
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@memeverse.com');
define('SMTP_FROM_NAME', 'MemeVerse');

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Simple logging function
function logError($msg, $ctx = []) {
    $log = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if ($ctx) $log .= ' | ' . json_encode($ctx);
    error_log($log . PHP_EOL, 3, __DIR__ . '/../error_log.txt');
}
?>