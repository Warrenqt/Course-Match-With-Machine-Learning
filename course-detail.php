<?php
require_once 'functions.php';
require_once __DIR__ . '/ml/courses_config.php';
requireLogin();

$user = getCurrentUser();

// Get course from URL
$courseName = $_GET['course'] ?? '';
if (empty($courseName)) {
    redirect('dashboard.php', 'Please select a course to view.');
}

// Get course information
$courseInfo = getCourseInfo($courseName);
if (!$courseInfo) {
    // Try partial match
    $allCourses = getAllCourseNames();
    foreach ($allCourses as $course) {
        if (stripos($course, $courseName) !== false || stripos($courseName, $course) !== false) {
            $courseInfo = getCourseInfo($course);
            $courseName = $course;
            break;
        }
    }
}

if (!$courseInfo) {
    redirect('dashboard.php', 'Course not found.');
}

// Check if user has a recommendation score for this course
$matchScore = 0;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT match_score FROM course_recommendations 
        WHERE user_id = ? AND course_name LIKE ? 
        ORDER BY recommended_at DESC LIMIT 1
    ");
    $stmt->execute([$user['id'], '%' . $courseName . '%']);
    $result = $stmt->fetch();
    if ($result) {
        $matchScore = round($result['match_score']);
    }
} catch (PDOException $e) {
    // Ignore
}

// Get user's quiz score for this course if any
$quizScore = null;
try {
    $stmt = $pdo->prepare("
        SELECT best_percentage, 
               (SELECT COUNT(*) + 1 FROM quiz_leaderboard ql2 
                WHERE ql2.course_name = ql.course_name AND ql2.best_percentage > ql.best_percentage) as rank_position
        FROM quiz_leaderboard ql
        WHERE user_id = ? AND course_name = ?
    ");
    $stmt->execute([$user['id'], $courseName]);
    $quizScore = $stmt->fetch();
} catch (PDOException $e) {
    // Ignore
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($courseName); ?> - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .detail-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .detail-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            background: var(--stardust-100);
            color: var(--stardust-600);
            border-radius: var(--radius-full);
            font-family: var(--font-display);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all var(--duration-normal) var(--ease-out);
        }

        .back-btn:hover {
            background: var(--cosmic-purple);
            color: var(--pure-white);
            transform: translateX(-3px);
        }

        .back-btn svg {
            width: 18px;
            height: 18px;
        }

        .bottom-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-4);
            flex-wrap: wrap;
            margin-top: var(--space-8);
            padding: var(--space-8) 0;
            border-top: 1px solid var(--stardust-200);
        }

        /* Hero */
        .course-hero {
            background: var(--starburst-gradient);
            padding: var(--space-10) 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .course-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 80% 70%, rgba(255,255,255,0.3) 0%, transparent 100%);
            animation: twinkle 4s ease-in-out infinite alternate;
        }

        .course-hero .container {
            position: relative;
            z-index: 10;
        }

        .course-icon {
            font-size: 5rem;
            margin-bottom: var(--space-4);
        }

        .course-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--pure-white);
            margin-bottom: var(--space-2);
        }

        .course-code {
            font-family: var(--font-mono);
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: var(--space-3);
        }

        .course-meta-hero {
            display: flex;
            justify-content: center;
            gap: var(--space-5);
            flex-wrap: wrap;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        .match-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-full);
            color: var(--pure-white);
            font-weight: 600;
            margin-top: var(--space-4);
        }

        /* Tabs */
        .course-tabs {
            background: var(--pure-white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 70px;
            z-index: 50;
        }

        .course-tabs .container {
            display: flex;
            gap: 0;
        }

        .tab-btn {
            flex: 1;
            padding: var(--space-4) var(--space-5);
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--stardust-500);
            background: transparent;
            border: none;
            cursor: pointer;
            position: relative;
            transition: all var(--duration-fast) var(--ease-out);
            text-align: center;
        }

        .tab-btn::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--cosmic-purple);
            transform: scaleX(0);
            transition: transform var(--duration-normal) var(--ease-out);
        }

        .tab-btn.active {
            color: var(--cosmic-purple);
        }

        .tab-btn.active::after {
            transform: scaleX(1);
        }

        .tab-btn:hover {
            color: var(--cosmic-purple);
        }

        /* Content */
        .course-content {
            padding: var(--space-8) 0;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s var(--ease-out);
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Overview Section */
        .overview-card {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            padding: var(--space-7);
            margin-bottom: var(--space-6);
        }

        .overview-card h2 {
            font-family: var(--font-display);
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-4);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .overview-card p {
            color: var(--stardust-600);
            line-height: 1.8;
            margin-bottom: var(--space-5);
        }

        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
        }

        .skill-tag {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            padding: var(--space-2) var(--space-4);
            background: rgba(124, 58, 237, 0.1);
            color: var(--cosmic-purple);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .universities-list {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-3);
            margin-top: var(--space-3);
        }

        .university-tag {
            padding: var(--space-2) var(--space-4);
            background: var(--stardust-100);
            color: var(--stardust-600);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
        }

        /* Careers Section */
        .career-card {
            background: var(--pure-white);
            border-radius: var(--radius-xl);
            padding: var(--space-5);
            margin-bottom: var(--space-4);
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--space-4);
            transition: all var(--duration-normal) var(--ease-out);
            border: 1px solid var(--stardust-200);
        }

        .career-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--cosmic-purple-light);
        }

        .career-info h4 {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-1);
        }

        .career-desc {
            font-size: 0.9rem;
            color: var(--stardust-500);
            line-height: 1.5;
            margin-bottom: var(--space-3);
        }

        .career-salary {
            font-family: var(--font-mono);
            font-size: 0.95rem;
            color: var(--cosmic-purple);
            font-weight: 500;
        }

        .demand-badge {
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .demand-badge.very-high {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.2));
            color: #047857;
        }

        .demand-badge.high {
            background: rgba(59, 130, 246, 0.1);
            color: var(--stellar-blue);
        }

        .demand-badge.medium {
            background: rgba(251, 191, 36, 0.1);
            color: #B45309;
        }

        /* Quiz Section */
        .quiz-promo {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            padding: var(--space-8);
            text-align: center;
        }

        .quiz-promo-icon {
            font-size: 5rem;
            margin-bottom: var(--space-4);
        }

        .quiz-promo h2 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--space-dark);
            margin-bottom: var(--space-3);
        }

        .quiz-promo p {
            color: var(--stardust-500);
            line-height: 1.7;
            max-width: 500px;
            margin: 0 auto var(--space-6);
        }

        .quiz-stats {
            display: flex;
            justify-content: center;
            gap: var(--space-8);
            margin-bottom: var(--space-6);
            flex-wrap: wrap;
        }

        .quiz-stat {
            text-align: center;
        }

        .quiz-stat-value {
            font-family: var(--font-mono);
            font-size: 2rem;
            font-weight: 700;
            color: var(--cosmic-purple);
        }

        .quiz-stat-label {
            font-size: 0.85rem;
            color: var(--stardust-500);
        }

        .quiz-score-card {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(236, 72, 153, 0.05));
            border-radius: var(--radius-xl);
            padding: var(--space-5);
            margin-bottom: var(--space-6);
        }

        .quiz-score-card h3 {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--cosmic-purple);
            margin-bottom: var(--space-2);
        }

        @media (max-width: 768px) {
            .course-tabs .container {
                flex-wrap: nowrap;
                overflow-x: auto;
            }

            .tab-btn {
                white-space: nowrap;
                padding: var(--space-3) var(--space-4);
                font-size: 0.9rem;
            }

            .career-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="detail-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <a href="javascript:history.back()" class="back-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="course-hero">
        <div class="container">
            <div class="course-icon"><?php echo $courseInfo['icon'] ?? '🎓'; ?></div>
            <h1><?php echo htmlspecialchars($courseInfo['full_name'] ?? $courseName); ?></h1>
            <p class="course-code"><?php echo $courseInfo['code'] ?? ''; ?> • <?php echo $courseInfo['duration'] ?? '4 years'; ?></p>
            <div class="course-meta-hero">
                <span>🏛️ <?php echo htmlspecialchars($courseInfo['universities'][0] ?? 'Various Universities'); ?></span>
            </div>
            <?php if ($matchScore > 0): ?>
            <div class="match-badge">
                ⭐ Your Match: <?php echo $matchScore; ?>%
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Tabs -->
    <div class="course-tabs">
        <div class="container">
            <button class="tab-btn active" data-tab="overview">🌟 Overview</button>
            <button class="tab-btn" data-tab="careers">💼 Careers</button>
            <button class="tab-btn" data-tab="quiz">❓ Quiz</button>
        </div>
    </div>

    <!-- Content -->
    <main class="course-content">
        <div class="container">
            <!-- Overview Tab -->
            <div class="tab-content active" id="overview">
                <div class="overview-card">
                    <h2>📖 About This Course</h2>
                    <p><?php echo htmlspecialchars($courseInfo['description'] ?? 'No description available.'); ?></p>

                    <h2>🎯 Skills You'll Develop</h2>
                    <div class="skills-grid">
                        <?php foreach ($courseInfo['skills'] ?? [] as $skill): ?>
                        <span class="skill-tag">✓ <?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="overview-card">
                    <h2>🏛️ Top Universities</h2>
                    <p>These institutions offer excellent programs for this course:</p>
                    <div class="universities-list">
                        <?php foreach ($courseInfo['universities'] ?? [] as $uni): ?>
                        <span class="university-tag"><?php echo htmlspecialchars($uni); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Careers Tab -->
            <div class="tab-content" id="careers">
                <h2 style="font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--space-dark); text-align: center; margin-bottom: var(--space-6);">
                    💼 Career Opportunities
                </h2>

                <?php foreach ($courseInfo['careers'] ?? [] as $career): ?>
                <div class="career-card">
                    <div class="career-info">
                        <h4><?php echo htmlspecialchars($career['title']); ?></h4>
                        <p class="career-desc"><?php echo htmlspecialchars($career['description'] ?? ''); ?></p>
                        <p class="career-salary">💰 <?php echo htmlspecialchars($career['salary']); ?></p>
                    </div>
                    <span class="demand-badge <?php echo strtolower(str_replace(' ', '-', $career['demand'] ?? 'medium')); ?>">
                        <?php echo htmlspecialchars($career['demand'] ?? 'Medium'); ?> Demand
                    </span>
                </div>
                <?php endforeach; ?>

                <div style="background: var(--warning-light); border-left: 4px solid var(--warning); padding: var(--space-4); margin-top: var(--space-6); border-radius: var(--radius-md);">
                    <p style="color: #92400E; font-size: 0.9rem;">
                        💡 Salary ranges are estimates based on 2025 Philippine market data. Actual salaries may vary based on experience, location, and company.
                    </p>
                </div>
            </div>

            <!-- Quiz Tab -->
            <div class="tab-content" id="quiz">
                <div class="quiz-promo">
                    <div class="quiz-promo-icon">🎯</div>
                    <h2>Explore This Field</h2>
                    <p>Take our 20-question quiz to discover what it's really like to work in <?php echo htmlspecialchars($courseName); ?>. No prior knowledge needed!</p>

                    <?php if ($quizScore): ?>
                    <div class="quiz-score-card">
                        <h3>Your Best Score</h3>
                        <div style="font-family: var(--font-mono); font-size: 2.5rem; font-weight: 700; color: var(--cosmic-purple);">
                            <?php echo $quizScore['best_percentage']; ?>%
                        </div>
                        <p style="color: var(--stardust-500);">Rank #<?php echo $quizScore['rank_position']; ?> on the leaderboard</p>
                    </div>
                    <?php endif; ?>

                    <div class="quiz-stats">
                        <div class="quiz-stat">
                            <div class="quiz-stat-value">20</div>
                            <div class="quiz-stat-label">Questions</div>
                        </div>
                        <div class="quiz-stat">
                            <div class="quiz-stat-value">5-10</div>
                            <div class="quiz-stat-label">Minutes</div>
                        </div>
                        <div class="quiz-stat">
                            <div class="quiz-stat-value">🏆</div>
                            <div class="quiz-stat-label">Leaderboard</div>
                        </div>
                    </div>

                    <a href="quiz.php?course=<?php echo urlencode($courseName); ?>" class="btn btn-stellar btn-lg">
                        🚀 <?php echo $quizScore ? 'Retake Quiz' : 'Start the Quiz'; ?>
                    </a>

                    <div style="margin-top: var(--space-5);">
                        <a href="leaderboard.php?course=<?php echo urlencode($courseName); ?>" style="color: var(--cosmic-purple); font-size: 0.95rem;">
                            View Leaderboard for this Course →
                        </a>
                    </div>
                </div>
            </div>
            <!-- Bottom Actions -->
            <div class="bottom-actions">
                <a href="javascript:history.back()" class="btn btn-orbit btn-lg">
                    ← Back to Previous Page
                </a>
                <a href="results.php" class="btn btn-ghost btn-lg">
                    View All Results
                </a>
                <a href="dashboard.php" class="btn btn-ghost btn-lg">
                    Dashboard
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer style="background: var(--space-black); color: var(--pure-white); padding: var(--space-6) 0; text-align: center;">
        <div class="container">
            <p style="color: var(--stardust-500); font-size: 0.9rem;">© 2025 CourseMatch. Made with ❤️ for Filipino Students 🇵🇭</p>
        </div>
    </footer>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.dataset.tab;

                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });
    </script>
</body>
</html>

