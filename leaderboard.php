<?php
require_once 'functions.php';
require_once __DIR__ . '/ml/courses_config.php';
requireLogin();

$user = getCurrentUser();

// Get course from URL (optional - show all courses if not specified)
$selectedCourse = $_GET['course'] ?? '';

// Get all course names for the filter
$allCourses = getAllCourseNames();

try {
    $pdo = getDBConnection();
    
    // Get leaderboard data
    if (!empty($selectedCourse)) {
        // Specific course leaderboard
        $stmt = $pdo->prepare("
            SELECT 
                ql.id,
                ql.user_id,
                u.name as user_name,
                ql.course_name,
                ql.best_score,
                ql.best_percentage,
                ql.attempts,
                ql.last_attempt_at
            FROM quiz_leaderboard ql
            JOIN users u ON ql.user_id = u.id
            WHERE ql.course_name = ?
            ORDER BY ql.best_percentage DESC, ql.last_attempt_at ASC
            LIMIT 100
        ");
        $stmt->execute([$selectedCourse]);
    } else {
        // Top scorers across all courses
        $stmt = $pdo->prepare("
            SELECT 
                ql.id,
                ql.user_id,
                u.name as user_name,
                ql.course_name,
                ql.best_score,
                ql.best_percentage,
                ql.attempts,
                ql.last_attempt_at
            FROM quiz_leaderboard ql
            JOIN users u ON ql.user_id = u.id
            ORDER BY ql.best_percentage DESC, ql.last_attempt_at ASC
            LIMIT 100
        ");
        $stmt->execute();
    }
    
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user's rank for selected course
    $userRank = null;
    if (!empty($selectedCourse)) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) + 1 as rank_position
            FROM quiz_leaderboard
            WHERE course_name = ? AND best_percentage > (
                SELECT COALESCE(best_percentage, 0)
                FROM quiz_leaderboard
                WHERE user_id = ? AND course_name = ?
            )
        ");
        $stmt->execute([$selectedCourse, $user['id'], $selectedCourse]);
        $userRankResult = $stmt->fetch();
        
        // Check if user has a score
        $stmt = $pdo->prepare("SELECT best_percentage FROM quiz_leaderboard WHERE user_id = ? AND course_name = ?");
        $stmt->execute([$user['id'], $selectedCourse]);
        $userScore = $stmt->fetch();
        
        if ($userScore) {
            $userRank = [
                'position' => $userRankResult['rank_position'],
                'percentage' => $userScore['best_percentage']
            ];
        }
    }
    
    // Get course-specific stats
    $courseStats = [];
    if (!empty($selectedCourse)) {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_participants,
                AVG(best_percentage) as avg_score,
                MAX(best_percentage) as top_score
            FROM quiz_leaderboard
            WHERE course_name = ?
        ");
        $stmt->execute([$selectedCourse]);
        $courseStats = $stmt->fetch();
    }
    
} catch (PDOException $e) {
    $leaderboard = [];
    $userRank = null;
    $courseStats = [];
    error_log("Leaderboard error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .lead-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .lead-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .lead-nav-links {
            display: flex;
            gap: var(--space-5);
            align-items: center;
        }

        .lead-nav-links a {
            font-family: var(--font-display);
            font-weight: 500;
            color: var(--stardust-500);
            font-size: 0.95rem;
            transition: color var(--duration-fast) var(--ease-out);
        }

        .lead-nav-links a:hover {
            color: var(--cosmic-purple);
        }

        .lead-nav-links .nav-active {
            color: var(--cosmic-purple);
        }

        .lead-nav-links .btn-logout {
            background: var(--error);
            color: var(--pure-white) !important;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.875rem !important;
        }

        .lead-nav-links .btn-logout:hover {
            background: #DC2626 !important;
        }

        /* Hero */
        .lead-hero {
            background: var(--starburst-gradient);
            padding: var(--space-10) 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .lead-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 80% 70%, rgba(255,255,255,0.3) 0%, transparent 100%);
            animation: twinkle 4s ease-in-out infinite alternate;
        }

        .lead-hero .container {
            position: relative;
            z-index: 10;
        }

        .lead-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--pure-white);
            margin-bottom: var(--space-3);
        }

        .lead-hero p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        /* Course Filter */
        .filter-section {
            background: var(--pure-white);
            padding: var(--space-5) 0;
            border-bottom: 1px solid var(--stardust-200);
        }

        .filter-wrapper {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            flex-wrap: wrap;
        }

        .filter-label {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--space-dark);
        }

        .filter-select {
            padding: var(--space-3) var(--space-5);
            font-size: 1rem;
            border: 2px solid var(--stardust-200);
            border-radius: var(--radius-full);
            background: var(--pure-white);
            min-width: 250px;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--cosmic-purple);
        }

        /* Stats Cards */
        .stats-section {
            padding: var(--space-6) 0;
            background: var(--stardust-100);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-4);
        }

        .stat-card {
            background: var(--pure-white);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .stat-card-value {
            font-family: var(--font-mono);
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--cosmic-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card-label {
            color: var(--stardust-500);
            font-size: 0.9rem;
            margin-top: var(--space-1);
        }

        /* Leaderboard Table */
        .lead-content {
            padding: var(--space-8) 0;
        }

        .lead-card {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .lead-header {
            padding: var(--space-5) var(--space-6);
            border-bottom: 1px solid var(--stardust-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lead-header h2 {
            font-family: var(--font-display);
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--space-dark);
        }

        .lead-table {
            width: 100%;
            border-collapse: collapse;
        }

        .lead-table th {
            background: var(--stardust-100);
            padding: var(--space-4) var(--space-5);
            text-align: left;
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--stardust-600);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .lead-table td {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--stardust-100);
        }

        .lead-table tr:last-child td {
            border-bottom: none;
        }

        .lead-table tr:hover td {
            background: rgba(124, 58, 237, 0.02);
        }

        .lead-table tr.current-user td {
            background: rgba(124, 58, 237, 0.08);
        }

        /* Rank Badge */
        .rank-badge {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            font-family: var(--font-display);
            font-weight: 700;
        }

        .rank-1 {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #7C5800;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        }

        .rank-1::after {
            content: '🥇';
            font-size: 1.25rem;
        }

        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0 0%, #A0A0A0 100%);
            color: #555;
            font-size: 1.25rem;
            box-shadow: 0 4px 15px rgba(192, 192, 192, 0.4);
        }

        .rank-2::after {
            content: '🥈';
            font-size: 1.25rem;
        }

        .rank-3 {
            background: linear-gradient(135deg, #CD7F32 0%, #A05A2C 100%);
            color: #fff;
            font-size: 1.25rem;
            box-shadow: 0 4px 15px rgba(205, 127, 50, 0.4);
        }

        .rank-3::after {
            content: '🥉';
            font-size: 1.25rem;
        }

        .rank-other {
            background: var(--stardust-100);
            color: var(--stardust-500);
        }

        .user-name {
            font-weight: 600;
            color: var(--space-dark);
        }

        .user-name.is-you {
            color: var(--cosmic-purple);
        }

        .user-name.is-you::after {
            content: ' (You)';
            font-weight: 400;
            color: var(--cosmic-purple-light);
        }

        .course-tag {
            display: inline-block;
            padding: var(--space-1) var(--space-3);
            background: rgba(124, 58, 237, 0.1);
            color: var(--cosmic-purple);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .score-value {
            font-family: var(--font-mono);
            font-weight: 700;
            color: var(--space-dark);
            font-size: 1.1rem;
        }

        .attempts-count {
            color: var(--stardust-400);
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--space-10);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4);
        }

        .empty-title {
            font-family: var(--font-display);
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
        }

        .empty-text {
            color: var(--stardust-500);
            margin-bottom: var(--space-5);
        }

        /* Your Rank Card */
        .your-rank-card {
            background: var(--starburst-gradient);
            color: var(--pure-white);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-5);
            flex-wrap: wrap;
            gap: var(--space-4);
        }

        .your-rank-info h3 {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: var(--space-1);
        }

        .your-rank-info p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .your-rank-position {
            font-family: var(--font-mono);
            font-size: 3rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .filter-wrapper {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                min-width: 100%;
            }

            .lead-table th,
            .lead-table td {
                padding: var(--space-3);
            }

            .lead-table th:nth-child(4),
            .lead-table td:nth-child(4) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="lead-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <div class="lead-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="history.php">📜 History</a>
                <a href="leaderboard.php" class="nav-active">🏆 Leaderboard</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="lead-hero">
        <div class="container">
            <h1>🏆 Course Quiz Leaderboard</h1>
            <p>See how you rank against other students!</p>
        </div>
    </section>

    <!-- Filter -->
    <section class="filter-section">
        <div class="container">
            <div class="filter-wrapper">
                <span class="filter-label">Filter by Course:</span>
                <select class="filter-select" onchange="window.location.href='leaderboard.php?course=' + encodeURIComponent(this.value)">
                    <option value="">All Courses</option>
                    <?php foreach ($allCourses as $course): ?>
                    <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $selectedCourse === $course ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <!-- Stats (if course selected) -->
    <?php if (!empty($selectedCourse) && !empty($courseStats)): ?>
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $courseStats['total_participants'] ?? 0; ?></div>
                    <div class="stat-card-label">Total Participants</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo round($courseStats['avg_score'] ?? 0); ?>%</div>
                    <div class="stat-card-label">Average Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $courseStats['top_score'] ?? 0; ?>%</div>
                    <div class="stat-card-label">Top Score</div>
                </div>
                <?php if ($userRank): ?>
                <div class="stat-card">
                    <div class="stat-card-value">#<?php echo $userRank['position']; ?></div>
                    <div class="stat-card-label">Your Rank</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Leaderboard -->
    <main class="lead-content">
        <div class="container">
            <?php if ($userRank && !empty($selectedCourse)): ?>
            <div class="your-rank-card">
                <div class="your-rank-info">
                    <h3>Your Position in <?php echo htmlspecialchars($selectedCourse); ?></h3>
                    <p>Your best score: <?php echo $userRank['percentage']; ?>%</p>
                </div>
                <div class="your-rank-position">#<?php echo $userRank['position']; ?></div>
            </div>
            <?php endif; ?>

            <div class="lead-card">
                <div class="lead-header">
                    <h2><?php echo !empty($selectedCourse) ? htmlspecialchars($selectedCourse) . ' Rankings' : 'Top Scorers Across All Courses'; ?></h2>
                </div>

                <?php if (empty($leaderboard)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🏆</div>
                    <h3 class="empty-title">No quiz results yet!</h3>
                    <p class="empty-text">Be the first to take the quiz and claim the top spot!</p>
                    <?php if (!empty($selectedCourse)): ?>
                    <a href="quiz.php?course=<?php echo urlencode($selectedCourse); ?>" class="btn btn-stellar">Take the Quiz</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <table class="lead-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <?php if (empty($selectedCourse)): ?>
                            <th>Course</th>
                            <?php endif; ?>
                            <th>Score</th>
                            <th>Attempts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($leaderboard as $entry): 
                            $isCurrentUser = $entry['user_id'] == $user['id'];
                        ?>
                        <tr class="<?php echo $isCurrentUser ? 'current-user' : ''; ?>">
                            <td>
                                <div class="rank-badge <?php echo $rank <= 3 ? 'rank-' . $rank : 'rank-other'; ?>">
                                    <?php echo $rank > 3 ? $rank : ''; ?>
                                </div>
                            </td>
                            <td>
                                <span class="user-name <?php echo $isCurrentUser ? 'is-you' : ''; ?>">
                                    <?php echo htmlspecialchars($entry['user_name']); ?>
                                </span>
                            </td>
                            <?php if (empty($selectedCourse)): ?>
                            <td>
                                <span class="course-tag"><?php echo htmlspecialchars($entry['course_name']); ?></span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <span class="score-value"><?php echo $entry['best_percentage']; ?>%</span>
                            </td>
                            <td>
                                <span class="attempts-count"><?php echo $entry['attempts']; ?> <?php echo $entry['attempts'] == 1 ? 'attempt' : 'attempts'; ?></span>
                            </td>
                        </tr>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer style="background: var(--space-black); color: var(--pure-white); padding: var(--space-6) 0; text-align: center;">
        <div class="container">
            <p style="color: var(--stardust-500); font-size: 0.9rem;">© 2025 CourseMatch. Made with ❤️ for Filipino Students 🇵🇭</p>
        </div>
    </footer>
</body>
</html>

