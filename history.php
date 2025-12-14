<?php
require_once 'functions.php';
require_once __DIR__ . '/ml/courses_config.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get all assessments for this user
$stmt = $pdo->prepare("
    SELECT 
        ua.id,
        ua.subject_interest_score,
        ua.grade_performance,
        ua.career_interests,
        ua.created_at,
        ua.updated_at
    FROM user_assessments ua
    WHERE ua.user_id = ?
    ORDER BY ua.created_at DESC
");
$stmt->execute([$user['id']]);
$assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommendations for each assessment
foreach ($assessments as &$assessment) {
    $stmt = $pdo->prepare("
        SELECT course_name, match_score, course_description, university_name, reasoning
        FROM course_recommendations
        WHERE user_id = ? 
        AND recommended_at >= ? 
        AND recommended_at <= DATE_ADD(?, INTERVAL 1 MINUTE)
        ORDER BY match_score DESC
        LIMIT 3
    ");
    $stmt->execute([
        $user['id'], 
        $assessment['created_at'],
        $assessment['created_at']
    ]);
    $assessment['recommendations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON data
    $assessment['interests'] = json_decode($assessment['subject_interest_score'], true) ?? [];
    $assessment['grades'] = json_decode($assessment['grade_performance'], true) ?? [];
}
unset($assessment);

// Interest and personality display names
$interestNames = [
    'problem_solving' => 'Problem Solving & Logic',
    'art_creativity' => 'Art & Creativity',
    'technology' => 'Technology & Innovation',
    'business' => 'Business & Entrepreneurship',
    'culinary' => 'Culinary & Hospitality'
];

$personalityNames = [
    'analytical' => 'Analytical & Logical',
    'empathetic' => 'Empathetic & Social',
    'adventurous' => 'Adventurous & Bold',
    'creative' => 'Creative & Innovative',
    'independent' => 'Independent & Self-Driven'
];

// Helper function to determine key factors for a course recommendation
function getCourseFactor($courseName, $grades, $interests) {
    // Define which subjects are key for each course
    $courseSubjectMap = [
        'Computer Science' => ['mathematics', 'science', 'tle'],
        'Information Technology' => ['tle', 'mathematics', 'english'],
        'Engineering' => ['mathematics', 'science', 'tle'],
        'Business Administration' => ['mathematics', 'english', 'araling_panlipunan'],
        'Accounting' => ['mathematics', 'english', 'araling_panlipunan'],
        'Nursing' => ['science', 'mapeh', 'english'],
        'Education' => ['english', 'filipino', 'araling_panlipunan'],
        'Fine Arts' => ['mapeh', 'english', 'filipino'],
        'Multimedia Arts' => ['mapeh', 'tle', 'english'],
        'Culinary Arts' => ['tle', 'mapeh', 'science'],
        'Hotel & Restaurant Management' => ['tle', 'english', 'mapeh'],
        'Communication Arts' => ['english', 'filipino', 'araling_panlipunan'],
        'Psychology' => ['science', 'english', 'mapeh'],
        'Tourism' => ['english', 'araling_panlipunan', 'mapeh']
    ];
    
    // Course interest alignment
    $courseInterestMap = [
        'Computer Science' => ['Problem Solving / Logical Thinking', 'Technology'],
        'Information Technology' => ['Technology', 'Problem Solving / Logical Thinking'],
        'Engineering' => ['Problem Solving / Logical Thinking', 'Technology'],
        'Business Administration' => ['Business', 'Problem Solving / Logical Thinking'],
        'Accounting' => ['Business', 'Problem Solving / Logical Thinking'],
        'Nursing' => ['Empathetic / Social', 'Analytical / Logical'],
        'Education' => ['Empathetic / Social', 'Creative / Innovative'],
        'Fine Arts' => ['Art / Creativity', 'Creative / Innovative'],
        'Multimedia Arts' => ['Art / Creativity', 'Technology'],
        'Culinary Arts' => ['Cooking / Culinary Skills', 'Creative / Innovative'],
        'Hotel & Restaurant Management' => ['Cooking / Culinary Skills', 'Business'],
        'Communication Arts' => ['Art / Creativity', 'Creative / Innovative'],
        'Psychology' => ['Empathetic / Social', 'Analytical / Logical'],
        'Tourism' => ['Adventurous / Risk-Taker', 'Empathetic / Social']
    ];
    
    // Course personality alignment
    $coursePersonalityMap = [
        'Computer Science' => 'Analytical / Logical',
        'Information Technology' => 'Analytical / Logical',
        'Engineering' => 'Analytical / Logical',
        'Business Administration' => 'Independent / Self-Starter',
        'Accounting' => 'Analytical / Logical',
        'Nursing' => 'Empathetic / Social',
        'Education' => 'Empathetic / Social',
        'Fine Arts' => 'Creative / Innovative',
        'Multimedia Arts' => 'Creative / Innovative',
        'Culinary Arts' => 'Creative / Innovative',
        'Hotel & Restaurant Management' => 'Empathetic / Social',
        'Communication Arts' => 'Creative / Innovative',
        'Psychology' => 'Empathetic / Social',
        'Tourism' => 'Adventurous / Risk-Taker'
    ];
    
    // Why messages for each course
    $courseWhyMap = [
        'Computer Science' => 'Your strong math and analytical skills, combined with interest in problem-solving, make you ideal for software development.',
        'Information Technology' => 'Your technical aptitude and practical skills align well with IT infrastructure and support roles.',
        'Engineering' => 'Your mathematical precision and scientific thinking are perfect for designing solutions to complex problems.',
        'Business Administration' => 'Your understanding of systems and people, plus entrepreneurial thinking, suit business leadership.',
        'Accounting' => 'Your attention to detail and numerical precision make you well-suited for financial analysis.',
        'Nursing' => 'Your caring nature combined with scientific knowledge prepares you for healthcare excellence.',
        'Education' => 'Your communication skills and patience make you a natural fit for shaping young minds.',
        'Fine Arts' => 'Your creative expression and artistic vision align perfectly with visual arts careers.',
        'Multimedia Arts' => 'Your blend of creativity and technical skills is ideal for digital media production.',
        'Culinary Arts' => 'Your passion for cooking and creativity make you perfect for the culinary world.',
        'Hotel & Restaurant Management' => 'Your service orientation and organizational skills suit hospitality leadership.',
        'Communication Arts' => 'Your way with words and storytelling ability make you ideal for media careers.',
        'Psychology' => 'Your understanding of people and analytical mind suit behavioral science.',
        'Tourism' => 'Your love for exploration and people skills are perfect for the travel industry.'
    ];
    
    $keySubjects = $courseSubjectMap[$courseName] ?? ['mathematics', 'english', 'science'];
    $keySubject = $keySubjects[0];
    $keySubjectGrade = $grades[$keySubject] ?? 'N/A';
    
    // Format subject name nicely
    $subjectNames = [
        'mathematics' => 'Mathematics',
        'science' => 'Science',
        'english' => 'English',
        'filipino' => 'Filipino',
        'mapeh' => 'MAPEH',
        'tle' => 'TLE/ICT',
        'araling_panlipunan' => 'Araling Panlipunan'
    ];
    
    $keySubjectDisplay = $subjectNames[$keySubject] ?? ucwords($keySubject);
    if ($keySubjectGrade !== 'N/A') {
        $keySubjectDisplay .= ' (' . $keySubjectGrade . ')';
    }
    
    $interestMatches = $courseInterestMap[$courseName] ?? ['General Interest'];
    $personalityMatch = $coursePersonalityMap[$courseName] ?? 'Various Personalities';
    $why = $courseWhyMap[$courseName] ?? 'Based on your unique combination of grades, interests, and personality traits.';
    
    return [
        'key_subject' => $keySubjectDisplay,
        'key_subject_grade' => $keySubjectGrade,
        'interest_match' => $interestMatches[0] ?? 'General',
        'personality_match' => $personalityMatch,
        'why' => $why
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment History - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .history-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .history-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .history-nav-links {
            display: flex;
            gap: var(--space-5);
            align-items: center;
        }

        .history-nav-links a {
            font-family: var(--font-display);
            font-weight: 500;
            color: var(--stardust-500);
            font-size: 0.95rem;
            transition: color var(--duration-fast) var(--ease-out);
        }

        .history-nav-links a:hover {
            color: var(--cosmic-purple);
        }

        .history-nav-links .nav-active {
            color: var(--cosmic-purple);
        }

        .history-nav-links .btn-logout {
            background: var(--error);
            color: var(--pure-white) !important;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.875rem !important;
        }

        .history-nav-links .btn-logout:hover {
            background: #DC2626 !important;
        }

        .history-hero {
            background: var(--twilight-gradient);
            padding: var(--space-10) 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .history-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 80% 70%, rgba(255,255,255,0.3) 0%, transparent 100%);
            animation: twinkle 4s ease-in-out infinite alternate;
        }

        .history-hero .container {
            position: relative;
            z-index: 10;
        }

        .history-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--pure-white);
            margin-bottom: var(--space-3);
        }

        .history-hero p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.1rem;
        }

        .history-content {
            padding: var(--space-8) 0;
        }

        /* Assessment Card */
        .assessment-card {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            margin-bottom: var(--space-6);
            overflow: hidden;
            border: 1px solid var(--stardust-200);
        }

        .assessment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-5) var(--space-6);
            background: var(--stardust-100);
            border-bottom: 1px solid var(--stardust-200);
            flex-wrap: wrap;
            gap: var(--space-4);
        }

        .assessment-date {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .assessment-date-icon {
            width: 48px;
            height: 48px;
            background: var(--cosmic-gradient);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .assessment-date-text h3 {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--space-dark);
        }

        .assessment-date-text p {
            font-size: 0.9rem;
            color: var(--stardust-500);
        }

        .assessment-badge {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            background: var(--success-light);
            color: var(--success);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .assessment-body {
            padding: var(--space-6);
        }

        /* Recommendations Grid */
        .rec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-4);
            margin-bottom: var(--space-5);
        }

        .rec-card {
            background: var(--stardust-100);
            border-radius: var(--radius-xl);
            padding: var(--space-5);
            position: relative;
            transition: all var(--duration-normal) var(--ease-bounce);
            border: 2px solid transparent;
        }

        .rec-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--cosmic-purple-light);
        }

        .rec-card.primary {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%);
            border-color: var(--cosmic-purple-light);
        }

        .rec-rank {
            position: absolute;
            top: var(--space-3);
            right: var(--space-3);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .rec-rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #7C5800;
        }

        .rec-rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
            color: #555;
        }

        .rec-rank-3 {
            background: linear-gradient(135deg, #CD7F32, #A05A2C);
            color: #fff;
        }

        .rec-icon {
            font-size: 2.5rem;
            margin-bottom: var(--space-3);
        }

        .rec-name {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
            padding-right: var(--space-8);
        }

        .rec-score {
            display: inline-flex;
            align-items: center;
            gap: var(--space-1);
            padding: var(--space-1) var(--space-3);
            background: var(--cosmic-gradient);
            color: var(--pure-white);
            border-radius: var(--radius-full);
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: var(--space-3);
        }

        .rec-university {
            font-size: 0.9rem;
            color: var(--stardust-500);
        }

        /* Details Grid - Redesigned for balance */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-4);
            padding: var(--space-5);
            background: var(--stardust-100);
            border-radius: var(--radius-lg);
        }

        .detail-item {
            background: var(--pure-white);
            border-radius: var(--radius-md);
            padding: var(--space-4);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100px;
            border: 1px solid var(--stardust-200);
            transition: all var(--duration-fast) var(--ease-out);
        }

        .detail-item:hover {
            border-color: var(--cosmic-purple-light);
            box-shadow: var(--shadow-sm);
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--stardust-400);
            margin-bottom: var(--space-2);
        }

        .detail-value {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--space-dark);
            line-height: 1.4;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
            min-height: auto;
            padding: var(--space-4) var(--space-5);
        }

        .detail-item.full-width .detail-value {
            font-weight: 400;
            font-size: 0.95rem;
            color: var(--stardust-600);
        }

        /* Smooth transition for detail items */
        .detail-item {
            transition: transform 0.2s ease, opacity 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        @media (max-width: 640px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Actions */
        .assessment-actions {
            display: flex;
            gap: var(--space-3);
            flex-wrap: wrap;
            margin-top: var(--space-5);
            padding-top: var(--space-5);
            border-top: 1px solid var(--stardust-200);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--space-12);
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
        }

        .empty-icon {
            font-size: 5rem;
            margin-bottom: var(--space-5);
        }

        .empty-title {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-3);
        }

        .empty-text {
            color: var(--stardust-500);
            margin-bottom: var(--space-6);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Selectable Course Cards */
        .rec-card {
            cursor: pointer;
        }

        .rec-card.selected {
            border-color: var(--cosmic-purple);
            box-shadow: var(--shadow-glow-purple);
        }

        .rec-card.selected::after {
            content: '✓ Selected';
            position: absolute;
            bottom: var(--space-3);
            right: var(--space-3);
            font-size: 0.75rem;
            color: var(--cosmic-purple);
            font-weight: 600;
        }

        /* Dynamic Details */
        .details-grid {
            transition: all var(--duration-normal) var(--ease-out);
        }

        .details-grid.updating {
            opacity: 0.6;
        }

        .detail-value.highlight {
            color: var(--cosmic-purple);
            animation: pulse-highlight 0.5s ease;
        }

        @keyframes pulse-highlight {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .course-factors-title {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-4);
            padding: var(--space-3) var(--space-4);
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.08), rgba(236, 72, 153, 0.05));
            border-radius: var(--radius-md);
            border-left: 3px solid var(--cosmic-purple);
        }

        .course-factors-title strong {
            color: var(--cosmic-purple);
        }

        @media (max-width: 768px) {
            .assessment-header {
                flex-direction: column;
                text-align: center;
            }

            .rec-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="history-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <div class="history-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="history.php" class="nav-active">📜 History</a>
                <a href="leaderboard.php">🏆 Leaderboard</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="history-hero">
        <div class="container">
            <h1>📜 Assessment History</h1>
            <p>View your past assessments and recommendations</p>
        </div>
    </section>

    <!-- Content -->
    <main class="history-content">
        <div class="container">
            <?php if (empty($assessments)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h2 class="empty-title">No Assessments Yet</h2>
                <p class="empty-text">You haven't taken any course assessments yet. Start your first assessment to discover your perfect course match!</p>
                <a href="assessment.php" class="btn btn-stellar btn-lg">🚀 Take Assessment</a>
            </div>
            <?php else: ?>
            
            <?php foreach ($assessments as $index => $assessment): ?>
            <div class="assessment-card">
                <div class="assessment-header">
                    <div class="assessment-date">
                        <div class="assessment-date-icon">📅</div>
                        <div class="assessment-date-text">
                            <h3>Assessment #<?php echo count($assessments) - $index; ?></h3>
                            <p><?php echo date('F d, Y - g:i A', strtotime($assessment['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="assessment-badge">
                        <span>✓</span> Completed
                    </div>
                </div>

                <div class="assessment-body">
                    <!-- Recommendations -->
                    <?php if (!empty($assessment['recommendations'])): ?>
                    <h4 style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; color: var(--space-dark); margin-bottom: var(--space-4);">
                        🎯 Recommended Courses <span style="font-size: 0.85rem; font-weight: 400; color: var(--stardust-400);">(Click to see why)</span>
                    </h4>
                    <div class="rec-grid" data-assessment-id="<?php echo $index; ?>">
                        <?php 
                        $rank = 1;
                        foreach ($assessment['recommendations'] as $rec): 
                            $courseInfo = getCourseInfo($rec['course_name']);
                            // Determine key factors for this course
                            $courseFactors = getCourseFactor($rec['course_name'], $assessment['grades'], $assessment['interests']);
                        ?>
                        <div class="rec-card <?php echo $rank === 1 ? 'primary selected' : ''; ?>" 
                             data-course="<?php echo htmlspecialchars($rec['course_name']); ?>"
                             data-key-subject="<?php echo htmlspecialchars($courseFactors['key_subject']); ?>"
                             data-key-subject-grade="<?php echo htmlspecialchars($courseFactors['key_subject_grade']); ?>"
                             data-interest-match="<?php echo htmlspecialchars($courseFactors['interest_match']); ?>"
                             data-personality-match="<?php echo htmlspecialchars($courseFactors['personality_match']); ?>"
                             data-why="<?php echo htmlspecialchars($courseFactors['why']); ?>"
                             onclick="selectCourse(this, <?php echo $index; ?>)">
                            <div class="rec-rank rec-rank-<?php echo $rank; ?>"><?php echo $rank; ?></div>
                            <div class="rec-icon"><?php echo $courseInfo['icon'] ?? '🎓'; ?></div>
                            <h5 class="rec-name"><?php echo htmlspecialchars($rec['course_name']); ?></h5>
                            <div class="rec-score"><?php echo round($rec['match_score']); ?>% Match</div>
                            <p class="rec-university">🏛️ <?php echo htmlspecialchars($rec['university_name'] ?? 'Various Universities'); ?></p>
                        </div>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </div>
                    <?php endif; ?>

                    <!-- Assessment Details (Dynamic based on selected course) -->
                    <?php 
                        // Get factors for the primary (first) course
                        $primaryCourse = $assessment['recommendations'][0] ?? null;
                        $primaryFactors = $primaryCourse ? getCourseFactor($primaryCourse['course_name'], $assessment['grades'], $assessment['interests']) : null;
                    ?>
                    <div class="course-factors-title" id="factors-title-<?php echo $index; ?>">
                        📊 Why <strong><?php echo htmlspecialchars($primaryCourse['course_name'] ?? 'this course'); ?></strong> matches you
                    </div>
                    <div class="details-grid" id="details-grid-<?php echo $index; ?>">
                        <div class="detail-item">
                            <div class="detail-label">📚 Key Subject</div>
                            <div class="detail-value" id="key-subject-<?php echo $index; ?>">
                                <?php echo htmlspecialchars($primaryFactors['key_subject'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">💡 Interest Match</div>
                            <div class="detail-value" id="interest-match-<?php echo $index; ?>">
                                <?php echo htmlspecialchars($primaryFactors['interest_match'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">🧠 Personality Fit</div>
                            <div class="detail-value" id="personality-match-<?php echo $index; ?>">
                                <?php echo htmlspecialchars($primaryFactors['personality_match'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        <div class="detail-item full-width">
                            <div class="detail-label">💬 Why This Course?</div>
                            <div class="detail-value" id="why-<?php echo $index; ?>">
                                <?php echo htmlspecialchars($primaryFactors['why'] ?? 'Based on your unique profile'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="assessment-actions">
                        <?php if (!empty($assessment['recommendations'])): ?>
                        <a href="results.php?view=history&date=<?php echo urlencode($assessment['created_at']); ?>" class="btn btn-stellar">
                            🌟 View Full Results
                        </a>
                        <?php foreach ($assessment['recommendations'] as $i => $rec): ?>
                        <a href="course-detail.php?course=<?php echo urlencode($rec['course_name']); ?>" class="btn btn-orbit">
                            View <?php echo htmlspecialchars($rec['course_name']); ?>
                        </a>
                        <?php break; // Only show button for first course ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="assessment.php" class="btn btn-ghost">
                            🔄 Retake Assessment
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer style="background: var(--space-black); color: var(--pure-white); padding: var(--space-6) 0; text-align: center; margin-top: var(--space-8);">
        <div class="container">
            <p style="color: var(--stardust-500); font-size: 0.9rem;">© 2025 CourseMatch. Made with ❤️ for Filipino Students 🇵🇭</p>
        </div>
    </footer>

    <script>
        function selectCourse(cardElement, assessmentIndex) {
            // Get the assessment container
            const recGrid = cardElement.closest('.rec-grid');
            
            // Remove selected class from all cards in this grid
            recGrid.querySelectorAll('.rec-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            cardElement.classList.add('selected');
            
            // Get data from the selected card
            const courseName = cardElement.dataset.course;
            const keySubject = cardElement.dataset.keySubject;
            const interestMatch = cardElement.dataset.interestMatch;
            const personalityMatch = cardElement.dataset.personalityMatch;
            const why = cardElement.dataset.why;
            
            // Update the details section with animation
            const detailsGrid = document.getElementById('details-grid-' + assessmentIndex);
            const factorsTitle = document.getElementById('factors-title-' + assessmentIndex);
            
            // Add updating class for animation
            detailsGrid.classList.add('updating');
            
            setTimeout(() => {
                // Update title
                factorsTitle.innerHTML = '📊 Why <strong>' + courseName + '</strong> matches you';
                
                // Update values
                document.getElementById('key-subject-' + assessmentIndex).textContent = keySubject;
                document.getElementById('interest-match-' + assessmentIndex).textContent = interestMatch;
                document.getElementById('personality-match-' + assessmentIndex).textContent = personalityMatch;
                document.getElementById('why-' + assessmentIndex).textContent = why;
                
                // Remove updating class
                detailsGrid.classList.remove('updating');
                
                // Add subtle animation to all detail items
                detailsGrid.querySelectorAll('.detail-item').forEach((item, i) => {
                    item.style.transform = 'scale(0.95)';
                    item.style.opacity = '0.7';
                    setTimeout(() => {
                        item.style.transform = 'scale(1)';
                        item.style.opacity = '1';
                    }, 50 + (i * 50));
                });
            }, 150);
        }
    </script>
</body>
</html>

