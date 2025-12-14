<?php
require_once 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security error. Please try again.';
        $messageType = 'error';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!validateEmail($email)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            $result = authenticateUser($email, $password);

            if ($result['success']) {
                redirect('dashboard.php', $result['message']);
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--constellation-gradient);
            padding: var(--space-5);
            position: relative;
            overflow: hidden;
        }

        .auth-page .constellation-bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .auth-page .constellation-bg::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 10% 20%, rgba(255,255,255,0.8) 0%, transparent 100%),
                radial-gradient(2px 2px at 30% 60%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(1px 1px at 50% 30%, rgba(255,255,255,0.9) 0%, transparent 100%),
                radial-gradient(2px 2px at 70% 80%, rgba(255,255,255,0.7) 0%, transparent 100%),
                radial-gradient(1px 1px at 90% 50%, rgba(255,255,255,0.5) 0%, transparent 100%);
            animation: twinkle 4s ease-in-out infinite alternate;
        }

        .auth-nebula {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.3;
        }

        .auth-nebula-1 {
            width: 400px;
            height: 400px;
            background: var(--cosmic-purple);
            top: -150px;
            right: -100px;
        }

        .auth-nebula-2 {
            width: 300px;
            height: 300px;
            background: var(--nebula-pink);
            bottom: -100px;
            left: -50px;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
        }

        .auth-header {
            text-align: center;
            margin-bottom: var(--space-6);
        }

        .auth-logo {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--space-4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            color: var(--pure-white);
        }

        .auth-header h1 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--pure-white);
            margin-bottom: var(--space-2);
        }

        .auth-header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: var(--space-7);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-alert {
            padding: var(--space-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-5);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .auth-alert-error {
            background: var(--error-light);
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        .auth-alert-success {
            background: var(--success-light);
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .auth-form .form-group {
            margin-bottom: var(--space-5);
        }

        .auth-form .form-label {
            color: var(--space-dark);
            margin-bottom: var(--space-2);
        }

        .auth-form .form-input {
            background: var(--stardust-100);
            border: 2px solid transparent;
        }

        .auth-form .form-input:focus {
            background: var(--pure-white);
            border-color: var(--cosmic-purple);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .auth-submit {
            width: 100%;
            padding: var(--space-4);
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--pure-white);
            background: var(--starburst-gradient);
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-bounce);
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
        }

        .auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.4);
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--space-5);
            padding-top: var(--space-5);
            border-top: 1px solid var(--stardust-200);
        }

        .auth-footer p {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: var(--cosmic-purple);
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .auth-back {
            text-align: center;
            margin-top: var(--space-5);
        }

        .auth-back a {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            transition: color var(--duration-fast) var(--ease-out);
        }

        .auth-back a:hover {
            color: var(--pure-white);
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="constellation-bg"></div>
        <div class="auth-nebula auth-nebula-1"></div>
        <div class="auth-nebula auth-nebula-2"></div>

        <div class="auth-container">
            <div class="auth-header">
                <a href="index.php" class="auth-logo">
                    <span>✨</span> CourseMatch
                </a>
                <h1>Welcome Back</h1>
                <p>Continue your stellar journey</p>
            </div>

            <div class="auth-card">
                <?php if ($message): ?>
                    <div class="auth-alert auth-alert-<?php echo $messageType; ?>">
                        <span><?php echo $messageType === 'error' ? '⚠️' : '✓'; ?></span>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="your.email@example.com"
                            required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit" class="auth-submit">
                        🚀 Log In
                    </button>

                    <div class="auth-footer">
                        <p>Don't have an account? <a href="signup.php">Sign up free</a></p>
                    </div>
                </form>
            </div>

            <div class="auth-back">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
