<?php
require_once 'functions.php';
requireLogin();

$user = getCurrentUser();
$message = getFlashMessage();

// Check if user has completed assessment
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM user_assessments WHERE user_id = ? AND assessment_completed = 1 ORDER BY updated_at DESC LIMIT 1");
$stmt->execute([$user['id']]);
$assessment = $stmt->fetch();

$assessmentStatus = $assessment ? 'Completed' : 'Not Started';
$matchScore = $assessment ? '92%' : '--';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .dash-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dash-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .dash-nav-links {
            display: flex;
            gap: var(--space-5);
            align-items: center;
        }

        .dash-nav-links a {
            font-family: var(--font-display);
            font-weight: 500;
            color: var(--stardust-500);
            font-size: 0.95rem;
            transition: color var(--duration-fast) var(--ease-out);
        }

        .dash-nav-links a:hover {
            color: var(--cosmic-purple);
        }

        .dash-nav-links .nav-active {
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

        /* Hero Section */
        .dash-hero {
            background: var(--starburst-gradient);
            padding: var(--space-9) 0 var(--space-8);
            position: relative;
            overflow: hidden;
        }

        .dash-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(1px 1px at 20% 30%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 80% 70%, rgba(255,255,255,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 40% 80%, rgba(255,255,255,0.5) 0%, transparent 100%);
        }

        .dash-hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .welcome-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            color: var(--pure-white);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: var(--space-4);
        }

        .dash-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--pure-white);
            margin-bottom: var(--space-3);
        }

        .dash-hero p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        /* Alert */
        .alert-success {
            background: var(--success-light);
            color: #065F46;
            border: 1px solid #6EE7B7;
            padding: var(--space-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-6);
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
        }

        /* Main Content */
        .dash-content {
            padding: var(--space-8) 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-5);
            margin-bottom: var(--space-8);
        }

        .stat-card {
            background: var(--pure-white);
            padding: var(--space-6);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            transition: all var(--duration-normal) var(--ease-bounce);
            border: 1px solid var(--stardust-200);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--cosmic-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-label {
            font-family: var(--font-display);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--stardust-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-family: var(--font-mono);
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--space-dark);
            line-height: 1;
            margin-bottom: var(--space-2);
        }

        .stat-value.pending {
            color: var(--warning);
        }

        .stat-value.success {
            color: var(--success);
        }

        .stat-desc {
            color: var(--stardust-400);
            font-size: 0.9rem;
        }

        /* CTA Card */
        .cta-card {
            background: var(--pure-white);
            padding: var(--space-8);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: var(--starburst-gradient);
            border-radius: inherit;
            z-index: -1;
            opacity: 0;
            transition: opacity var(--duration-normal) var(--ease-out);
        }

        .cta-card:hover::before {
            opacity: 1;
        }

        .cta-icon {
            width: 80px;
            height: 80px;
            background: var(--starburst-gradient);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto var(--space-5);
            box-shadow: var(--shadow-glow-purple);
        }

        .cta-card h2 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--space-dark);
            margin-bottom: var(--space-3);
        }

        .cta-card > p {
            color: var(--stardust-500);
            line-height: 1.7;
            margin-bottom: var(--space-5);
        }

        .cta-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-4);
            margin-bottom: var(--space-6);
            color: var(--stardust-400);
            font-size: 0.9rem;
        }

        /* Footer */
        .dash-footer {
            background: var(--space-black);
            color: var(--pure-white);
            padding: var(--space-6) 0;
            text-align: center;
            margin-top: var(--space-8);
        }

        .dash-footer p {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .dash-nav-links {
                gap: var(--space-3);
                font-size: 0.875rem;
            }

            .dash-hero h1 {
                font-size: 1.75rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .cta-card {
                padding: var(--space-6);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="dash-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <div class="dash-nav-links">
                <a href="dashboard.php" class="nav-active">Dashboard</a>
                <a href="history.php">📜 History</a>
                <a href="leaderboard.php">🏆 Leaderboard</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="dash-hero">
        <div class="container">
            <div class="dash-hero-content">
                <div class="welcome-badge">✨ Welcome back!</div>
                <h1>Hi, <?php echo htmlspecialchars($user['name']); ?> 👋</h1>
                <p>Ready to chart your course among the stars?</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="dash-content">
        <div class="container">
            <?php if ($message): ?>
                <div class="alert-success">
                    <span>🎉</span>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📋</div>
                        <div class="stat-label">Assessment Status</div>
                    </div>
                    <div class="stat-value <?php echo $assessment ? 'success' : 'pending'; ?>">
                        <?php echo $assessmentStatus; ?>
                    </div>
                    <div class="stat-desc">
                        <?php echo $assessment ? 'View your results' : 'Start to get recommendations'; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-label">Courses Matched</div>
                    </div>
                    <div class="stat-value"><?php echo $assessment ? '3' : '0'; ?></div>
                    <div class="stat-desc">Recommended for you</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-label">Match Score</div>
                    </div>
                    <div class="stat-value"><?php echo $matchScore; ?></div>
                    <div class="stat-desc">Based on your profile</div>
                </div>
            </div>

            <!-- CTA Card -->
            <div class="cta-card">
                <div class="cta-icon">🚀</div>
                <h2>
                    <?php echo $assessment ? 'View Your Constellation' : 'Take Course Assessment'; ?>
                </h2>
                <p>
                    <?php if ($assessment): ?>
                        Your unique constellation is ready! See which courses align with your stellar profile and explore career paths.
                    <?php else: ?>
                        Answer questions about your SHS grades, interests, and personality. Our AI will create your unique constellation and recommend the best courses for your future.
                    <?php endif; ?>
                </p>
                <div class="cta-meta">
                    <span>⏱️ Takes 5-7 minutes</span>
                    <span>•</span>
                    <span>🔒 Your data is secure</span>
                </div>
                <a href="<?php echo $assessment ? 'results.php' : 'assessment.php'; ?>" class="btn btn-stellar btn-lg">
                    <?php echo $assessment ? '🌟 View Results' : '🚀 Start Assessment'; ?>
                </a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="dash-footer">
        <div class="container">
            <p>© 2025 CourseMatch. Made with ❤️ for Filipino Students 🇵🇭</p>
        </div>
    </footer>
</body>
</html>
