<?php
require_once '../config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        // Log admin login
        $pdo = getDBConnection();
        try {
            $stmt = $pdo->prepare("INSERT INTO rate_limits (identifier, created_at) VALUES (?, NOW())");
            $stmt->execute(['admin_login_' . $_SERVER['REMOTE_ADDR']]);
        } catch (Exception $e) {
            // Ignore logging errors
        }
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CourseMatch</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: var(--constellation-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-card {
            background: var(--pure-white);
            padding: var(--space-8);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .admin-logo {
            font-size: 3rem;
            margin-bottom: var(--space-4);
        }

        .admin-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
        }

        .admin-subtitle {
            color: var(--stardust-500);
            font-size: 0.95rem;
            margin-bottom: var(--space-6);
        }

        .admin-form .form-group {
            text-align: left;
        }

        .admin-error {
            background: var(--error-light);
            color: var(--error);
            padding: var(--space-3);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-4);
            font-size: 0.9rem;
        }

        .admin-footer {
            margin-top: var(--space-6);
            padding-top: var(--space-4);
            border-top: 1px solid var(--stardust-200);
        }

        .admin-footer a {
            color: var(--stardust-400);
            font-size: 0.85rem;
        }

        .admin-footer a:hover {
            color: var(--cosmic-purple);
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div class="admin-logo">🔐</div>
        <h1 class="admin-title">Admin Panel</h1>
        <p class="admin-subtitle">CourseMatch Administration</p>

        <?php if ($error): ?>
        <div class="admin-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="btn btn-stellar btn-lg" style="width: 100%;">
                🚀 Login to Admin
            </button>
        </form>

        <div class="admin-footer">
            <a href="../index.php">← Back to Main Site</a>
        </div>
    </div>
</body>
</html>

