<?php
require_once '../config.php';
require_once '../functions.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

// Get analytics data
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];

    // Assessments taken
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user_assessments WHERE assessment_completed = 1");
    $totalAssessments = $stmt->fetch()['total'];

    // Quiz results
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quiz_results");
    $totalQuizzes = $stmt->fetch()['total'];

    // Average match score
    $stmt = $pdo->query("SELECT AVG(match_score) as avg_score FROM course_recommendations");
    $avgMatchScore = round($stmt->fetch()['avg_score'] ?? 0, 1);

    // Daily active users (last 7 days)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(DISTINCT user_id) as users 
        FROM user_assessments 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $dailyUsers = $stmt->fetchAll();

    // Weekly active users
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as weekly_active 
        FROM user_assessments 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $weeklyActive = $stmt->fetch()['weekly_active'];

    // Top 5 recommended courses
    $stmt = $pdo->query("
        SELECT course_name, COUNT(*) as count, AVG(match_score) as avg_score
        FROM course_recommendations 
        GROUP BY course_name 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $topCourses = $stmt->fetchAll();

    // Quiz completion by course
    $stmt = $pdo->query("
        SELECT course_name, COUNT(*) as completions, AVG(percentage) as avg_score
        FROM quiz_results 
        GROUP BY course_name 
        ORDER BY completions DESC 
        LIMIT 5
    ");
    $quizStats = $stmt->fetchAll();

    // Recent activity
    $stmt = $pdo->query("
        SELECT u.name, 'assessment' as type, ua.created_at 
        FROM user_assessments ua 
        JOIN users u ON ua.user_id = u.id 
        ORDER BY ua.created_at DESC 
        LIMIT 10
    ");
    $recentActivity = $stmt->fetchAll();

    // New users this week
    $stmt = $pdo->query("
        SELECT COUNT(*) as new_users 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $newUsersWeek = $stmt->fetch()['new_users'];

} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $totalUsers = $totalAssessments = $totalQuizzes = $avgMatchScore = 0;
    $dailyUsers = $topCourses = $quizStats = $recentActivity = [];
    $weeklyActive = $newUsersWeek = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CourseMatch</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .admin-nav {
            background: var(--space-dark);
            padding: var(--space-4) 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-nav-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            color: var(--pure-white);
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .admin-nav-brand span {
            background: var(--cosmic-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-nav-links {
            display: flex;
            gap: var(--space-5);
            align-items: center;
        }

        .admin-nav-links a {
            color: var(--stardust-400);
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            color: var(--pure-white);
        }

        .admin-nav-links .btn-logout {
            background: var(--error);
            color: var(--pure-white) !important;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
        }

        .admin-header {
            background: var(--starburst-gradient);
            padding: var(--space-8) 0;
            color: var(--pure-white);
        }

        .admin-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--space-2);
        }

        .admin-header p {
            opacity: 0.9;
        }

        .admin-content {
            padding: var(--space-8) 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-5);
            margin-bottom: var(--space-8);
        }

        .stat-card {
            background: var(--pure-white);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            text-align: center;
            border: 1px solid var(--stardust-200);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: var(--space-3);
        }

        .stat-value {
            font-family: var(--font-mono);
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--cosmic-purple);
            line-height: 1;
            margin-bottom: var(--space-2);
        }

        .stat-label {
            color: var(--stardust-500);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-change {
            font-size: 0.8rem;
            margin-top: var(--space-2);
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--error);
        }

        /* Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-6);
        }

        .panel {
            background: var(--pure-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: var(--space-6);
            border: 1px solid var(--stardust-200);
        }

        .panel-title {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-5);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        /* Course List */
        .course-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .course-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-3) var(--space-4);
            background: var(--stardust-100);
            border-radius: var(--radius-md);
        }

        .course-item-name {
            font-weight: 500;
            color: var(--space-dark);
        }

        .course-item-stats {
            display: flex;
            gap: var(--space-4);
            font-size: 0.85rem;
        }

        .course-item-count {
            color: var(--cosmic-purple);
            font-weight: 600;
        }

        .course-item-score {
            color: var(--stardust-500);
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3);
            border-bottom: 1px solid var(--stardust-100);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            background: var(--cosmic-gradient);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-text {
            flex: 1;
        }

        .activity-name {
            font-weight: 500;
            color: var(--space-dark);
        }

        .activity-action {
            font-size: 0.85rem;
            color: var(--stardust-500);
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--stardust-400);
        }

        /* Daily Chart */
        .daily-chart {
            display: flex;
            align-items: flex-end;
            gap: var(--space-2);
            height: 120px;
            margin-top: var(--space-4);
        }

        .daily-bar {
            flex: 1;
            background: var(--cosmic-gradient);
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
            min-height: 20px;
            position: relative;
        }

        .daily-bar-label {
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.7rem;
            color: var(--stardust-400);
        }

        .daily-bar-value {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--cosmic-purple);
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-nav-links {
                gap: var(--space-3);
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <div class="admin-nav-brand">
                🔐 <span>CourseMatch</span> Admin
            </div>
            <div class="admin-nav-links">
                <a href="dashboard.php" class="active">📊 Dashboard</a>
                <a href="users.php">👥 Users</a>
                <a href="courses.php">📈 Courses</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <h1>📊 Analytics Dashboard</h1>
            <p>Welcome back, Admin! Here's what's happening with CourseMatch.</p>
        </div>
    </header>

    <!-- Content -->
    <main class="admin-content">
        <div class="container">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change">+<?php echo $newUsersWeek; ?> this week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?php echo number_format($totalAssessments); ?></div>
                    <div class="stat-label">Assessments Taken</div>
                    <div class="stat-change"><?php echo $weeklyActive; ?> active this week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-value"><?php echo number_format($totalQuizzes); ?></div>
                    <div class="stat-label">Quizzes Completed</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-value"><?php echo $avgMatchScore; ?>%</div>
                    <div class="stat-label">Avg Match Score</div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Left Column -->
                <div>
                    <!-- Top Recommended Courses -->
                    <div class="panel" style="margin-bottom: var(--space-6);">
                        <h3 class="panel-title">🏆 Top Recommended Courses</h3>
                        <div class="course-list">
                            <?php if (empty($topCourses)): ?>
                                <p style="color: var(--stardust-400); text-align: center;">No data yet</p>
                            <?php else: ?>
                                <?php foreach ($topCourses as $course): ?>
                                <div class="course-item">
                                    <span class="course-item-name"><?php echo htmlspecialchars($course['course_name']); ?></span>
                                    <div class="course-item-stats">
                                        <span class="course-item-count"><?php echo $course['count']; ?> recommendations</span>
                                        <span class="course-item-score"><?php echo round($course['avg_score'], 1); ?>% avg</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quiz Engagement -->
                    <div class="panel">
                        <h3 class="panel-title">❓ Quiz Engagement by Course</h3>
                        <div class="course-list">
                            <?php if (empty($quizStats)): ?>
                                <p style="color: var(--stardust-400); text-align: center;">No quiz data yet</p>
                            <?php else: ?>
                                <?php foreach ($quizStats as $quiz): ?>
                                <div class="course-item">
                                    <span class="course-item-name"><?php echo htmlspecialchars($quiz['course_name']); ?></span>
                                    <div class="course-item-stats">
                                        <span class="course-item-count"><?php echo $quiz['completions']; ?> quizzes</span>
                                        <span class="course-item-score"><?php echo round($quiz['avg_score'], 1); ?>% avg score</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Daily Active Users -->
                    <div class="panel" style="margin-bottom: var(--space-6);">
                        <h3 class="panel-title">📈 Daily Activity (Last 7 Days)</h3>
                        <?php if (empty($dailyUsers)): ?>
                            <p style="color: var(--stardust-400); text-align: center;">No activity data yet</p>
                        <?php else: ?>
                            <?php
                            $maxUsers = max(array_column($dailyUsers, 'users'));
                            $maxUsers = max($maxUsers, 1);
                            ?>
                            <div class="daily-chart">
                                <?php foreach (array_reverse($dailyUsers) as $day): ?>
                                    <?php $height = ($day['users'] / $maxUsers) * 100; ?>
                                    <div class="daily-bar" style="height: <?php echo max($height, 10); ?>%;">
                                        <span class="daily-bar-value"><?php echo $day['users']; ?></span>
                                        <span class="daily-bar-label"><?php echo date('D', strtotime($day['date'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Activity -->
                    <div class="panel">
                        <h3 class="panel-title">🕐 Recent Activity</h3>
                        <div class="activity-list">
                            <?php if (empty($recentActivity)): ?>
                                <p style="color: var(--stardust-400); text-align: center;">No recent activity</p>
                            <?php else: ?>
                                <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">📋</div>
                                    <div class="activity-text">
                                        <div class="activity-name"><?php echo htmlspecialchars($activity['name']); ?></div>
                                        <div class="activity-action">Completed assessment</div>
                                    </div>
                                    <div class="activity-time">
                                        <?php 
                                        $time = strtotime($activity['created_at']);
                                        $diff = time() - $time;
                                        if ($diff < 3600) {
                                            echo floor($diff / 60) . 'm ago';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . 'h ago';
                                        } else {
                                            echo floor($diff / 86400) . 'd ago';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

