<?php
require_once 'functions.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

$message = '';
$errors = [];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security error. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters long.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Check if email is taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already taken.';
        }

        // Password change validation
        if (!empty($newPassword) || !empty($confirmPassword)) {
            if (empty($currentPassword)) {
                $errors[] = 'Enter current password to change it.';
            } else {
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $userData = $stmt->fetch();

                if (!password_verify($currentPassword, $userData['password_hash'])) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    if (strlen($newPassword) < 8) {
                        $errors[] = 'New password must be at least 8 characters.';
                    }
                    if ($newPassword !== $confirmPassword) {
                        $errors[] = 'New passwords do not match.';
                    }
                }
            }
        }

        if (empty($errors)) {
            try {
                if (!empty($newPassword)) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $email, $hashedPassword, $user['id']]);
                    $message = 'Profile and password updated successfully! ✨';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $email, $user['id']]);
                    $message = 'Profile updated successfully! ✨';
                }

                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $user = getCurrentUser();
            } catch (Exception $e) {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Get assessment data
$stmt = $pdo->prepare("SELECT * FROM user_assessments WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
$stmt->execute([$user['id']]);
$assessment = $stmt->fetch();

// Get user's quiz rankings
$userQuizRankings = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            ql.course_name,
            ql.best_score,
            ql.best_percentage,
            ql.attempts,
            (SELECT COUNT(*) + 1 FROM quiz_leaderboard ql2 
             WHERE ql2.course_name = ql.course_name AND ql2.best_percentage > ql.best_percentage) as rank_position,
            (SELECT COUNT(*) FROM quiz_leaderboard ql3 WHERE ql3.course_name = ql.course_name) as total_participants
        FROM quiz_leaderboard ql
        WHERE ql.user_id = ?
        ORDER BY ql.best_percentage DESC
    ");
    $stmt->execute([$user['id']]);
    $userQuizRankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table might not exist yet
    $userQuizRankings = [];
}

// Check if user is a top scorer (rank 1-3) in any course
$topScorerCourses = array_filter($userQuizRankings, function($r) {
    return $r['rank_position'] <= 3;
});

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        /* Navigation */
        .profile-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .profile-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .profile-nav-links {
            display: flex;
            gap: var(--space-5);
            align-items: center;
        }

        .profile-nav-links a {
            font-family: var(--font-display);
            font-weight: 500;
            color: var(--stardust-500);
            font-size: 0.95rem;
        }

        .profile-nav-links a:hover {
            color: var(--cosmic-purple);
        }

        .profile-nav-links .nav-active {
            color: var(--cosmic-purple);
        }

        .btn-logout {
            background: var(--error);
            color: var(--pure-white) !important;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.875rem !important;
        }

        .btn-logout:hover {
            background: #DC2626 !important;
        }

        /* Main Content */
        .profile-content {
            padding: var(--space-8) 0;
        }

        .profile-header {
            text-align: center;
            margin-bottom: var(--space-7);
        }

        .profile-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
        }

        .profile-header p {
            color: var(--stardust-500);
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: var(--space-6);
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Sidebar */
        .profile-sidebar {
            background: var(--pure-white);
            padding: var(--space-6);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            height: fit-content;
        }

        .avatar-section {
            text-align: center;
            margin-bottom: var(--space-5);
            padding-bottom: var(--space-5);
            border-bottom: 2px solid var(--stardust-200);
        }

        .avatar {
            width: 100px;
            height: 100px;
            background: var(--starburst-gradient);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--pure-white);
            margin: 0 auto var(--space-4);
            box-shadow: var(--shadow-glow-purple);
        }

        .avatar-section h2 {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-1);
        }

        .avatar-section p {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        /* Stats */
        .profile-stats {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: var(--space-3);
            background: var(--stardust-100);
            border-radius: var(--radius-md);
        }

        .stat-item-label {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        .stat-item-value {
            font-family: var(--font-mono);
            font-weight: 600;
            color: var(--space-dark);
        }

        .stat-item-value.success {
            color: var(--success);
        }

        .stat-item-value.pending {
            color: var(--warning);
        }

        .sidebar-link {
            display: block;
            text-align: center;
            margin-top: var(--space-5);
            padding-top: var(--space-5);
            border-top: 2px solid var(--stardust-200);
            color: var(--cosmic-purple);
            font-weight: 600;
        }

        .sidebar-link:hover {
            text-decoration: underline;
        }

        /* Form Card */
        .profile-form-card {
            background: var(--pure-white);
            padding: var(--space-7);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
        }

        /* Alerts */
        .alert {
            padding: var(--space-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-5);
            font-weight: 500;
        }

        .alert-success {
            background: var(--success-light);
            color: #065F46;
            border: 1px solid #6EE7B7;
        }

        .alert-error {
            background: var(--error-light);
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }

        .alert-error ul {
            margin: var(--space-2) 0 0 var(--space-4);
        }

        .alert-error li {
            list-style: disc;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: var(--space-7);
            padding-bottom: var(--space-6);
            border-bottom: 2px solid var(--stardust-200);
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-4);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .form-section-desc {
            color: var(--stardust-500);
            margin-bottom: var(--space-4);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: var(--space-4);
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: var(--space-3) var(--space-4);
            border: 2px solid var(--stardust-200);
            border-radius: var(--radius-md);
            font-size: 1rem;
            background: var(--stardust-100);
            transition: all var(--duration-fast) var(--ease-out);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--cosmic-purple);
            background: var(--pure-white);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .form-hint {
            font-size: 0.85rem;
            color: var(--stardust-400);
            margin-top: var(--space-1);
        }

        /* Submit */
        .form-submit {
            text-align: center;
            margin-top: var(--space-6);
        }

        .btn-save {
            padding: var(--space-4) var(--space-8);
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--pure-white);
            background: var(--starburst-gradient);
            border: none;
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-bounce);
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.4);
        }

        /* Footer */
        .profile-footer {
            background: var(--space-black);
            color: var(--pure-white);
            padding: var(--space-6) 0;
            text-align: center;
            margin-top: var(--space-8);
        }

        .profile-footer p {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        @media (max-width: 968px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="profile-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <div class="profile-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="history.php">📜 History</a>
                <a href="leaderboard.php">🏆 Leaderboard</a>
                <a href="profile.php" class="nav-active">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="profile-content">
        <div class="container">
            <div class="profile-header">
                <h1>⚙️ My Profile</h1>
                <p>Manage your account settings</p>
            </div>

            <div class="profile-grid">
                <!-- Sidebar -->
                <aside class="profile-sidebar">
                    <div class="avatar-section">
                        <div class="avatar">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-item-label">Member Since</span>
                            <span class="stat-item-value">
                                <?php echo date('M Y', strtotime($user['created_at'])); ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-item-label">Assessment</span>
                            <span class="stat-item-value <?php echo $assessment ? 'success' : 'pending'; ?>">
                                <?php echo $assessment ? '✓ Done' : 'Pending'; ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-item-label">Last Updated</span>
                            <span class="stat-item-value">
                                <?php echo date('M d', strtotime($user['updated_at'])); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($topScorerCourses)): ?>
                    <div class="trophy-section" style="margin-top: var(--space-5); padding: var(--space-4); background: linear-gradient(135deg, rgba(255,215,0,0.1) 0%, rgba(255,165,0,0.1) 100%); border-radius: var(--radius-lg); border: 1px solid rgba(255,215,0,0.3);">
                        <div style="text-align: center; margin-bottom: var(--space-2);">
                            <span style="font-size: 2rem;">🏆</span>
                        </div>
                        <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--space-dark); text-align: center; margin-bottom: var(--space-2);">Top Scorer!</h4>
                        <?php foreach ($topScorerCourses as $trophy): ?>
                        <div style="font-size: 0.85rem; color: var(--stardust-600); text-align: center;">
                            #<?php echo $trophy['rank_position']; ?> in <?php echo htmlspecialchars(substr($trophy['course_name'], 0, 20)); ?><?php echo strlen($trophy['course_name']) > 20 ? '...' : ''; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($userQuizRankings)): ?>
                    <div class="quiz-rankings-section" style="margin-top: var(--space-5);">
                        <h4 style="font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--space-dark); margin-bottom: var(--space-3); display: flex; align-items: center; gap: var(--space-2);">
                            <span>📊</span> My Quiz Rankings
                        </h4>
                        <?php foreach ($userQuizRankings as $rank): ?>
                        <div style="padding: var(--space-3); background: var(--stardust-100); border-radius: var(--radius-md); margin-bottom: var(--space-2);">
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--space-dark); margin-bottom: var(--space-1);">
                                <?php echo htmlspecialchars($rank['course_name']); ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                                <span style="color: var(--cosmic-purple); font-weight: 600;">
                                    #<?php echo $rank['rank_position']; ?> of <?php echo $rank['total_participants']; ?>
                                </span>
                                <span style="color: var(--stardust-500);">
                                    <?php echo $rank['best_percentage']; ?>%
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <a href="leaderboard.php" style="display: block; text-align: center; margin-top: var(--space-3); font-size: 0.9rem; color: var(--cosmic-purple);">View All Leaderboards →</a>
                    </div>
                    <?php endif; ?>

                    <a href="dashboard.php" class="sidebar-link">← Back to Dashboard</a>
                </aside>

                <!-- Form Card -->
                <div class="profile-form-card">
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <strong>⚠️ Please fix:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <!-- Basic Info -->
                        <div class="form-section">
                            <h3 class="form-section-title">👤 Basic Information</h3>

                            <div class="form-group">
                                <label class="form-label" for="name">Full Name</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    class="form-input" 
                                    value="<?php echo htmlspecialchars($user['name']); ?>" 
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="email">Email Address</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    value="<?php echo htmlspecialchars($user['email']); ?>" 
                                    required
                                >
                                <p class="form-hint">Used for login and notifications</p>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="form-section">
                            <h3 class="form-section-title">🔒 Change Password</h3>
                            <p class="form-section-desc">Leave blank if you don't want to change your password</p>

                            <div class="form-group">
                                <label class="form-label" for="current_password">Current Password</label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    class="form-input"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="new_password">New Password</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    class="form-input"
                                >
                                <p class="form-hint">At least 8 characters with uppercase, lowercase, and numbers</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="confirm_password">Confirm New Password</label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-input"
                                >
                            </div>
                        </div>

                        <div class="form-submit">
                            <button type="submit" class="btn-save">
                                💾 Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="profile-footer">
        <div class="container">
            <p>© 2025 CourseMatch. Made with ❤️ for Filipino Students 🇵🇭</p>
        </div>
    </footer>
</body>
</html>
