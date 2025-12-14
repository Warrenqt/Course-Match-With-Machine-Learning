<?php
// Database Configuration with Security Best Practices
// NEVER commit this file with real credentials to version control

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_recommendation');
define('DB_USER', 'root'); // Change this to your database user
define('DB_PASS', ''); // Usually empty for XAMPP default

// Application settings
define('APP_NAME', 'Course Recommendation System');
define('APP_URL', 'http://localhost/Course_Reco');

// Security settings
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_LENGTH', 32);

// Admin credentials (hardcoded for simplicity)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT)); // Change 'admin123' to your password

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('Asia/Manila');

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_set_cookie_params(SESSION_LIFETIME, '/', '', false, true);
session_start();

// Database connection function
function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error instead of displaying it
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }

    return $pdo;
}

// Rate limiting helper
function checkRateLimit($identifier, $max_attempts, $time_window) {
    $pdo = getDBConnection();

    // Clean old entries
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$time_window]);

    // Check current attempts
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM rate_limits WHERE identifier = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$identifier, $time_window]);
    $result = $stmt->fetch();

    return $result['attempts'] < $max_attempts;
}

function recordRateLimitAttempt($identifier) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO rate_limits (identifier, created_at) VALUES (?, NOW())");
    $stmt->execute([$identifier]);
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Content Security Policy (adjust as needed)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;");

// Prevent clickjacking
header('X-Frame-Options: SAMEORIGIN');
?>
