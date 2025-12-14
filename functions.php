<?php
require_once 'config.php';

// Input sanitization and validation functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 254;
}

function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

function validateName($name) {
    // Allow letters, spaces, hyphens, apostrophes (2-50 characters)
    return preg_match('/^[a-zA-Z\s\'-]{2,50}$/', $name) && strlen($name) >= 2 && strlen($name) <= 50;
}

function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,
        'threads' => 3
    ]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// User authentication functions
function registerUser($name, $email, $password) {
    $pdo = getDBConnection();

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email address already registered'];
        }

        // Hash password
        $passwordHash = generatePasswordHash($password);

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$name, $email, $passwordHash]);

        $userId = $pdo->lastInsertId();

        // Log successful registration (you might want to log this)
        error_log("New user registered: $email (ID: $userId)");

        return ['success' => true, 'message' => 'Registration successful!', 'user_id' => $userId];

    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function authenticateUser($email, $password) {
    $pdo = getDBConnection();

    try {
        // Check if account is locked due to too many failed attempts
        $identifier = 'login_' . $email;
        if (!checkRateLimit($identifier, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
            return ['success' => false, 'message' => 'Account temporarily locked due to too many failed login attempts. Please try again later.'];
        }

        // Get user by email
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            recordRateLimitAttempt($identifier);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is not active. Please contact support.'];
        }

        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            recordRateLimitAttempt($identifier);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Successful login - clear rate limit attempts
        $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE identifier = ?");
        $stmt->execute([$identifier]);

        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);

        return ['success' => true, 'message' => 'Login successful!', 'user' => $user];

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

function logoutUser() {
    // Clear all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Clear the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    return true;
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        logoutUser();
        return null;
    }

    return $user;
}

// Security utility functions
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit();
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Email functions (for future use)
function sendVerificationEmail($email, $token) {
    // Implement email sending logic here
    // For now, just log it
    error_log("Verification email would be sent to $email with token $token");
    return true;
}

function verifyEmailToken($token) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT user_id FROM email_verifications
        WHERE token = ? AND expires_at > NOW() AND used = 0
    ");
    $stmt->execute([$token]);
    $result = $stmt->fetch();

    if ($result) {
        // Mark token as used
        $stmt = $pdo->prepare("UPDATE email_verifications SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);

        // Activate user account
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$result['user_id']]);

        return $result['user_id'];
    }

    return false;
}

// Password reset functions
function initiatePasswordReset($email) {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'No account found with this email address.'];
    }

    $token = generateRandomToken();
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)
    ");
    $stmt->execute([$user['id'], $token, $expires]);

    // Send reset email (implement later)
    sendPasswordResetEmail($email, $token);

    return ['success' => true, 'message' => 'Password reset instructions have been sent to your email.'];
}

function resetPassword($token, $newPassword) {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT user_id FROM password_resets
        WHERE token = ? AND expires_at > NOW() AND used = 0
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        return ['success' => false, 'message' => 'Invalid or expired reset token.'];
    }

    $passwordHash = generatePasswordHash($newPassword);

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$passwordHash, $reset['user_id']]);

    // Mark token as used
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);

    return ['success' => true, 'message' => 'Password reset successful!'];
}

function sendPasswordResetEmail($email, $token) {
    // Implement email sending logic here
    error_log("Password reset email would be sent to $email with token $token");
    return true;
}

// CSRF token functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
