<?php
require_once '../config.php';
require_once '../functions.php';
require_once '../ml/courses_config.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

try {
    // Course recommendation stats
    $stmt = $pdo->query("
        SELECT 
            course_name,
            COUNT(*) as recommendation_count,
            AVG(match_score) as avg_match_score,
            MIN(match_score) as min_score,
            MAX(match_score) as max_score
        FROM course_recommendations
        GROUP BY course_name
        ORDER BY recommendation_count DESC
    ");
    $courseStats = $stmt->fetchAll();

    // Quiz stats by course
    $stmt = $pdo->query("
        SELECT 
            course_name,
            COUNT(*) as quiz_count,
            AVG(percentage) as avg_score,
            MAX(percentage) as top_score,
            COUNT(DISTINCT user_id) as unique_users
        FROM quiz_results
        GROUP BY course_name
        ORDER BY quiz_count DESC
    ");
    $quizStats = $stmt->fetchAll();
    $quizStatsByName = [];
    foreach ($quizStats as $stat) {
        $quizStatsByName[$stat['course_name']] = $stat;
    }

    // Leaderboard overview
    $stmt = $pdo->query("
        SELECT 
            ql.course_name,
            u.name as top_scorer,
            ql.best_percentage
        FROM quiz_leaderboard ql
        JOIN users u ON ql.user_id = u.id
        WHERE (ql.course_name, ql.best_percentage) IN (
            SELECT course_name, MAX(best_percentage)
            FROM quiz_leaderboard
            GROUP BY course_name
        )
        ORDER BY ql.best_percentage DESC
    ");
    $leaderboardTop = $stmt->fetchAll();
    $leadersByName = [];
    foreach ($leaderboardTop as $leader) {
        $leadersByName[$leader['course_name']] = $leader;
    }

    // Interest distribution
    $stmt = $pdo->query("
        SELECT 
            career_interests as interest,
            COUNT(*) as count
        FROM user_assessments
        WHERE career_interests IS NOT NULL AND career_interests != ''
        GROUP BY career_interests
        ORDER BY count DESC
    ");
    $interestDist = $stmt->fetchAll();

    // Total stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM course_recommendations");
    $totalRecommendations = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quiz_results");
    $totalQuizzes = $stmt->fetch()['total'];

} catch (PDOException $e) {
    error_log("Admin courses error: " . $e->getMessage());
    $courseStats = $quizStats = $leaderboardTop = $interestDist = [];
    $totalRecommendations = $totalQuizzes = 0;
    $quizStatsByName = $leadersByName = [];
}

// Get all course names for display
$allCourses = getAllCourseNames();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Analytics - CourseMatch Admin</title>
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
            background: var(--nebula-gradient);
            padding: var(--space-8) 0;
            color: var(--pure-white);
        }

        .admin-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--space-2);
        }

        .admin-content {
            padding: var(--space-8) 0;
        }

        /* Stats Summary */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-5);
            margin-bottom: var(--space-8);
        }

        .summary-card {
            background: var(--pure-white);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            text-align: center;
            border: 1px solid var(--stardust-200);
        }

        .summary-value {
            font-family: var(--font-mono);
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--cosmic-purple);
        }

        .summary-label {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        /* Course Cards Grid */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--space-5);
            margin-bottom: var(--space-8);
        }

        .course-card {
            background: var(--pure-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--stardust-200);
        }

        .course-card-header {
            background: var(--stardust-100);
            padding: var(--space-4);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .course-icon {
            font-size: 2rem;
        }

        .course-name {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--space-dark);
        }

        .course-card-body {
            padding: var(--space-5);
        }

        .course-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-4);
        }

        .course-stat {
            text-align: center;
            padding: var(--space-3);
            background: var(--stardust-100);
            border-radius: var(--radius-md);
        }

        .course-stat-value {
            font-family: var(--font-mono);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--cosmic-purple);
        }

        .course-stat-label {
            font-size: 0.75rem;
            color: var(--stardust-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .course-leader {
            margin-top: var(--space-4);
            padding: var(--space-3);
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(236, 72, 153, 0.05));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .course-leader-icon {
            font-size: 1.5rem;
        }

        .course-leader-info {
            flex: 1;
        }

        .course-leader-name {
            font-weight: 600;
            color: var(--space-dark);
            font-size: 0.9rem;
        }

        .course-leader-score {
            font-size: 0.8rem;
            color: var(--cosmic-purple);
        }

        /* Interest Distribution */
        .interest-panel {
            background: var(--pure-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: var(--space-6);
            border: 1px solid var(--stardust-200);
        }

        .panel-title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-5);
        }

        .interest-bars {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }

        .interest-bar {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .interest-label {
            width: 200px;
            font-size: 0.9rem;
            color: var(--space-dark);
        }

        .interest-progress {
            flex: 1;
            height: 24px;
            background: var(--stardust-100);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .interest-fill {
            height: 100%;
            background: var(--cosmic-gradient);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: var(--space-3);
            color: var(--pure-white);
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 40px;
        }

        .no-data {
            text-align: center;
            padding: var(--space-6);
            color: var(--stardust-400);
        }

        @media (max-width: 768px) {
            .stats-summary {
                grid-template-columns: 1fr;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .interest-label {
                width: 120px;
                font-size: 0.8rem;
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
                <a href="dashboard.php">📊 Dashboard</a>
                <a href="users.php">👥 Users</a>
                <a href="courses.php" class="active">📈 Courses</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <h1>📈 Course Analytics</h1>
            <p>Detailed breakdown of course recommendations and quiz engagement</p>
        </div>
    </header>

    <!-- Content -->
    <main class="admin-content">
        <div class="container">
            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="summary-card">
                    <div class="summary-value"><?php echo count($courseStats); ?></div>
                    <div class="summary-label">Courses Recommended</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo number_format($totalRecommendations); ?></div>
                    <div class="summary-label">Total Recommendations</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value"><?php echo number_format($totalQuizzes); ?></div>
                    <div class="summary-label">Quizzes Completed</div>
                </div>
            </div>

            <!-- Interest Distribution -->
            <?php if (!empty($interestDist)): ?>
            <div class="interest-panel" style="margin-bottom: var(--space-8);">
                <h3 class="panel-title">💡 Interest Distribution</h3>
                <div class="interest-bars">
                    <?php 
                    $maxInterest = max(array_column($interestDist, 'count'));
                    foreach ($interestDist as $interest): 
                        $percentage = ($interest['count'] / $maxInterest) * 100;
                    ?>
                    <div class="interest-bar">
                        <div class="interest-label"><?php echo htmlspecialchars($interest['interest']); ?></div>
                        <div class="interest-progress">
                            <div class="interest-fill" style="width: <?php echo $percentage; ?>%;">
                                <?php echo $interest['count']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Courses Grid -->
            <h3 class="panel-title" style="margin-bottom: var(--space-5);">🎓 Course Performance</h3>
            
            <?php if (empty($courseStats)): ?>
                <div class="no-data">
                    <p style="font-size: 3rem; margin-bottom: var(--space-4);">📊</p>
                    <p>No course data yet. Recommendations will appear here once users take assessments.</p>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($courseStats as $course): 
                        $courseInfo = getCourseInfo($course['course_name']);
                        $quizData = $quizStatsByName[$course['course_name']] ?? null;
                        $leader = $leadersByName[$course['course_name']] ?? null;
                    ?>
                    <div class="course-card">
                        <div class="course-card-header">
                            <span class="course-icon"><?php echo $courseInfo['icon'] ?? '🎓'; ?></span>
                            <span class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></span>
                        </div>
                        <div class="course-card-body">
                            <div class="course-stats-grid">
                                <div class="course-stat">
                                    <div class="course-stat-value"><?php echo $course['recommendation_count']; ?></div>
                                    <div class="course-stat-label">Recommendations</div>
                                </div>
                                <div class="course-stat">
                                    <div class="course-stat-value"><?php echo round($course['avg_match_score'], 1); ?>%</div>
                                    <div class="course-stat-label">Avg Match</div>
                                </div>
                                <div class="course-stat">
                                    <div class="course-stat-value"><?php echo $quizData ? $quizData['quiz_count'] : 0; ?></div>
                                    <div class="course-stat-label">Quizzes Taken</div>
                                </div>
                                <div class="course-stat">
                                    <div class="course-stat-value"><?php echo $quizData ? round($quizData['avg_score'], 1) . '%' : '-'; ?></div>
                                    <div class="course-stat-label">Quiz Avg</div>
                                </div>
                            </div>

                            <?php if ($leader): ?>
                            <div class="course-leader">
                                <span class="course-leader-icon">🏆</span>
                                <div class="course-leader-info">
                                    <div class="course-leader-name"><?php echo htmlspecialchars($leader['top_scorer']); ?></div>
                                    <div class="course-leader-score">Top Score: <?php echo round($leader['best_percentage'], 1); ?>%</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

