<?php
require_once 'functions.php';
require_once __DIR__ . '/ml/courses_config.php';
requireLogin();

$user = getCurrentUser();
$assessmentData = $_SESSION['assessment_data'] ?? null;
$mlRecommendations = $_SESSION['recommendations'] ?? null;

if (!$assessmentData || !$mlRecommendations) {
    redirect('assessment.php', 'Please complete the assessment first.');
}

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

// Format recommendations from ML output
$recommendations = [];
foreach ($mlRecommendations as $rec) {
    // Generate personalized "why recommended" message
    $whyRecommended = generateWhyRecommended(
        $rec['course_name'],
        $assessmentData,
        $rec['match_score']
    );
    
    $recommendations[] = [
        'course_name' => $rec['full_name'] ?? $rec['course_name'],
        'short_name' => $rec['course_name'],
        'course_code' => $rec['code'] ?? '',
        'university' => $rec['universities'][0] ?? 'Various Universities',
        'all_universities' => $rec['universities'] ?? [],
        'match_score' => round($rec['match_score']),
        'description' => $rec['description'] ?? '',
        'duration' => $rec['duration'] ?? '4 years',
        'careers' => $rec['careers'] ?? [],
        'skills_required' => $rec['skills'] ?? [],
        'icon' => $rec['icon'] ?? '📚',
        'why_recommended' => $whyRecommended,
        'is_primary' => $rec['is_primary'] ?? false
    ];
}

/**
 * Generate personalized explanation for recommendation
 */
function generateWhyRecommended($courseName, $assessment, $matchScore) {
    $grades = $assessment['grades'];
    $interest = $assessment['interest'];
    $personality = $assessment['personality'];
    
    // Find highest grades
    arsort($grades);
    $topSubjects = array_slice(array_keys($grades), 0, 2);
    
    $subjectNames = [
        'mathematics' => 'Mathematics',
        'science' => 'Science',
        'english' => 'English',
        'filipino' => 'Filipino',
        'mapeh' => 'MAPEH',
        'tle' => 'TLE/ICT',
        'araling_panlipunan' => 'Araling Panlipunan'
    ];
    
    $interestPhrases = [
        'problem_solving' => 'love for problem-solving',
        'art_creativity' => 'creative talents',
        'technology' => 'passion for technology',
        'business' => 'business mindset',
        'culinary' => 'culinary interests'
    ];
    
    $personalityPhrases = [
        'analytical' => 'analytical thinking',
        'empathetic' => 'empathetic nature',
        'adventurous' => 'adventurous spirit',
        'creative' => 'creative mindset',
        'independent' => 'independent drive'
    ];
    
    $topSubjectStr = $subjectNames[$topSubjects[0]] ?? $topSubjects[0];
    $interestStr = $interestPhrases[$interest] ?? 'interests';
    $personalityStr = $personalityPhrases[$personality] ?? 'personality';
    
    $messages = [
        "Your strong performance in {$topSubjectStr} ({$grades[$topSubjects[0]]}%), combined with your {$interestStr} and {$personalityStr}, makes this course an excellent fit with a {$matchScore}% match!",
        "Based on your academic strengths in {$topSubjectStr} and your {$interestStr}, this course aligns perfectly with your profile. Your {$personalityStr} will help you excel!",
        "With a {$matchScore}% match, this course leverages your {$topSubjectStr} skills and {$interestStr}. Students with your {$personalityStr} typically thrive here!"
    ];
    
    return $messages[array_rand($messages)];
}

$primaryCourse = $recommendations[0];
$altCourses = array_slice($recommendations, 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Constellation - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        /* Navigation */
        .results-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .results-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-back {
            color: var(--stardust-500);
            font-size: 0.95rem;
        }

        .nav-back:hover {
            color: var(--cosmic-purple);
        }

        /* Hero Section */
        .results-hero {
            background: var(--constellation-gradient);
            padding: var(--space-10) 0;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .results-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(2px 2px at 20% 30%, rgba(255,255,255,0.6) 0%, transparent 100%),
                radial-gradient(1px 1px at 80% 70%, rgba(255,255,255,0.4) 0%, transparent 100%),
                radial-gradient(2px 2px at 60% 20%, rgba(255,255,255,0.5) 0%, transparent 100%);
            animation: twinkle 4s ease-in-out infinite alternate;
        }

        .results-hero .container {
            position: relative;
            z-index: 10;
        }

        .results-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: var(--pure-white);
            margin-bottom: var(--space-3);
        }

        .results-hero p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.1rem;
            margin-bottom: var(--space-6);
        }

        /* Match Score Circle */
        .match-score-wrapper {
            display: inline-block;
        }

        .match-score-circle {
            width: 160px;
            height: 160px;
            background: var(--pure-white);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2), var(--shadow-glow-purple);
            position: relative;
        }

        .match-score-circle::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: var(--aurora-gradient);
            z-index: -1;
        }

        .match-number {
            font-family: var(--font-mono);
            font-size: 3rem;
            font-weight: 700;
            background: var(--starburst-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .match-label {
            font-size: 0.9rem;
            color: var(--stardust-500);
            font-weight: 500;
        }

        /* Main Content */
        .results-content {
            padding: var(--space-8) 0;
        }

        /* Primary Course Card */
        .primary-course {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            margin-bottom: var(--space-8);
            border: 2px solid var(--cosmic-purple-light);
        }

        .course-header {
            background: var(--starburst-gradient);
            padding: var(--space-6);
            text-align: center;
            color: var(--pure-white);
        }

        .course-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: var(--space-3);
        }

        .course-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--space-2);
        }

        .course-meta {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Tabs */
        .course-tabs {
            display: flex;
            border-bottom: 2px solid var(--stardust-200);
            background: var(--stardust-100);
        }

        .tab-btn {
            flex: 1;
            padding: var(--space-4);
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--stardust-500);
            background: transparent;
            border: none;
            cursor: pointer;
            position: relative;
            transition: all var(--duration-fast) var(--ease-out);
        }

        .tab-btn::after {
            content: '';
            position: absolute;
            bottom: -2px;
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

        /* Tab Content */
        .tab-content {
            display: none;
            padding: var(--space-6);
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s var(--ease-out);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Why Recommended */
        .why-box {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%);
            border-left: 4px solid var(--cosmic-purple);
            padding: var(--space-5);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-5);
        }

        .why-box h4 {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            color: var(--cosmic-purple-dark);
            margin-bottom: var(--space-2);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .why-box p {
            color: var(--stardust-600);
            line-height: 1.7;
        }

        /* Description */
        .course-description {
            color: var(--stardust-600);
            line-height: 1.8;
            font-size: 1.05rem;
            margin-bottom: var(--space-5);
        }

        /* Skills */
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
        }

        .skill-tag {
            background: var(--stardust-100);
            color: var(--stardust-600);
            padding: var(--space-2) var(--space-3);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        /* Careers Grid */
        .careers-grid {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }

        .career-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-5);
            background: var(--stardust-100);
            border-radius: var(--radius-lg);
            border: 1px solid var(--stardust-200);
            transition: all var(--duration-normal) var(--ease-out);
        }

        .career-card:hover {
            border-color: var(--cosmic-purple-light);
            box-shadow: var(--shadow-md);
            transform: translateX(5px);
        }

        .career-info h4 {
            font-family: var(--font-display);
            font-size: 1.15rem;
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
            color: var(--success);
            font-weight: 600;
        }

        .demand-badge {
            padding: var(--space-2) var(--space-3);
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .demand-badge.very-high {
            background: var(--success-light);
            color: #065F46;
        }

        .demand-badge.high {
            background: rgba(59, 130, 246, 0.1);
            color: #1E40AF;
        }

        /* Quiz Section */
        .quiz-container {
            background: var(--stardust-100);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
        }

        .quiz-question {
            margin-bottom: var(--space-5);
        }

        .quiz-question h4 {
            font-family: var(--font-display);
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-3);
        }

        .quiz-options {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }

        .quiz-option {
            padding: var(--space-3) var(--space-4);
            background: var(--pure-white);
            border: 2px solid var(--stardust-200);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--duration-fast) var(--ease-out);
        }

        .quiz-option:hover {
            border-color: var(--cosmic-purple-light);
            background: rgba(124, 58, 237, 0.05);
        }

        .quiz-option.selected {
            border-color: var(--cosmic-purple);
            background: rgba(124, 58, 237, 0.1);
        }

        .quiz-result {
            margin-top: var(--space-5);
            padding: var(--space-5);
            border-radius: var(--radius-md);
            display: none;
        }

        .quiz-result.success {
            background: var(--success-light);
            border: 1px solid #6EE7B7;
        }

        .quiz-result.warning {
            background: var(--warning-light);
            border: 1px solid #FCD34D;
        }

        /* Alternative Courses */
        .alt-section {
            margin-top: var(--space-8);
        }

        .alt-section h2 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--space-dark);
            text-align: center;
            margin-bottom: var(--space-6);
        }

        .alt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-5);
        }

        .alt-card {
            background: var(--pure-white);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            border: 1px solid var(--stardust-200);
            transition: all var(--duration-normal) var(--ease-bounce);
            cursor: pointer;
        }

        .alt-card:hover {
            border-color: var(--cosmic-purple-light);
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }

        .alt-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--space-3);
        }

        .alt-card h3 {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--space-dark);
        }

        .alt-match {
            background: var(--cosmic-gradient);
            color: var(--pure-white);
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 700;
        }

        .alt-card p {
            color: var(--stardust-500);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: var(--space-2);
        }

        .alt-card .course-meta {
            color: var(--stardust-400);
            font-size: 0.85rem;
        }

        /* Actions */
        .actions-section {
            display: flex;
            justify-content: center;
            gap: var(--space-4);
            margin-top: var(--space-8);
            flex-wrap: wrap;
        }

        /* Footer */
        .results-footer {
            background: var(--space-black);
            color: var(--pure-white);
            padding: var(--space-6) 0;
            text-align: center;
            margin-top: var(--space-10);
        }

        .results-footer p {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .course-title {
                font-size: 1.5rem;
            }

            .tab-btn {
                padding: var(--space-3);
                font-size: 0.85rem;
            }

            .career-card {
                flex-direction: column;
                text-align: center;
                gap: var(--space-3);
            }

            .alt-grid {
                grid-template-columns: 1fr;
            }

            .actions-section {
                flex-direction: column;
            }

            .actions-section .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="results-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <a href="dashboard.php" class="nav-back">← Back to Dashboard</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="results-hero">
        <div class="container">
            <h1>🌟 Your Constellation is Ready!</h1>
            <p>Based on your grades, interests, and personality</p>
            <div class="match-score-wrapper">
                <div class="match-score-circle">
                    <span class="match-number" id="matchScore">0</span>
                    <span class="match-label">Match Score</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="results-content">
        <div class="container">
            <!-- Primary Course -->
            <div class="primary-course">
                <div class="course-header">
                    <div class="course-badge">⭐ Top Recommendation</div>
                    <div style="font-size: 3rem; margin-bottom: var(--space-3);"><?php echo $primaryCourse['icon'] ?? '🎓'; ?></div>
                    <h2 class="course-title"><?php echo $primaryCourse['course_name']; ?></h2>
                    <p class="course-meta"><?php echo $primaryCourse['course_code']; ?> • <?php echo $primaryCourse['duration']; ?> • <?php echo $primaryCourse['university']; ?></p>
                </div>

                <!-- Tabs -->
                <div class="course-tabs">
                    <button class="tab-btn active" data-tab="overview">🌟 Overview</button>
                    <button class="tab-btn" data-tab="careers">💼 Careers</button>
                    <button class="tab-btn" data-tab="quiz">❓ Quiz</button>
                </div>

                <!-- Tab: Overview -->
                <div class="tab-content active" id="overview">
                    <div class="why-box">
                        <h4>✨ Why We Recommend This</h4>
                        <p><?php echo $primaryCourse['why_recommended']; ?></p>
                    </div>

                    <h3 style="font-family: var(--font-display); margin-bottom: var(--space-3); color: var(--space-dark);">Course Description</h3>
                    <p class="course-description"><?php echo $primaryCourse['description']; ?></p>

                    <h3 style="font-family: var(--font-display); margin-bottom: var(--space-3); color: var(--space-dark);">Key Skills You'll Develop</h3>
                    <div class="skills-list">
                        <?php foreach ($primaryCourse['skills_required'] as $skill): ?>
                            <span class="skill-tag">✓ <?php echo $skill; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab: Careers -->
                <div class="tab-content" id="careers">
                    <h3 style="font-family: var(--font-display); margin-bottom: var(--space-5); text-align: center; color: var(--space-dark);">💼 Career Opportunities</h3>
                    
                    <div class="careers-grid">
                        <?php foreach ($primaryCourse['careers'] as $career): ?>
                            <div class="career-card">
                                <div class="career-info">
                                    <h4><?php echo $career['title']; ?></h4>
                                    <p class="career-desc"><?php echo $career['description'] ?? ''; ?></p>
                                    <p class="career-salary">💰 <?php echo $career['salary']; ?></p>
                                </div>
                                <span class="demand-badge <?php echo strtolower(str_replace(' ', '-', $career['demand'])); ?>">
                                    <?php echo $career['demand']; ?> Demand
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background: var(--warning-light); border-left: 4px solid var(--warning); padding: var(--space-4); margin-top: var(--space-6); border-radius: var(--radius-md);">
                        <p style="color: #92400E; font-size: 0.9rem;">
                            💡 Salary ranges are estimates based on 2025 Philippine market data. Actual salaries may vary based on experience and location.
                        </p>
                    </div>
                </div>

                <!-- Tab: Quiz -->
                <div class="tab-content" id="quiz">
                    <div class="quiz-container" style="text-align: center; padding: var(--space-8);">
                        <div style="font-size: 5rem; margin-bottom: var(--space-5);">🎯</div>
                        <h3 style="font-family: var(--font-display); font-size: 1.75rem; margin-bottom: var(--space-3); color: var(--space-dark);">Ready to Explore This Field?</h3>
                        <p style="color: var(--stardust-500); margin-bottom: var(--space-6); max-width: 500px; margin-left: auto; margin-right: auto; line-height: 1.7;">
                            Take our 20-question quiz to discover what it's really like to work in <?php echo htmlspecialchars($primaryCourse['short_name'] ?? $primaryCourse['course_name']); ?>. 
                            No prior knowledge needed - just honest answers!
                        </p>

                        <div style="display: flex; flex-wrap: wrap; gap: var(--space-4); justify-content: center; margin-bottom: var(--space-6);">
                            <div style="text-align: center; padding: var(--space-4);">
                                <div style="font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--cosmic-purple);">20</div>
                                <div style="font-size: 0.85rem; color: var(--stardust-500);">Questions</div>
                            </div>
                            <div style="text-align: center; padding: var(--space-4);">
                                <div style="font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--cosmic-purple);">5-10</div>
                                <div style="font-size: 0.85rem; color: var(--stardust-500);">Minutes</div>
                            </div>
                            <div style="text-align: center; padding: var(--space-4);">
                                <div style="font-family: var(--font-mono); font-size: 2rem; font-weight: 700; color: var(--cosmic-purple);">🏆</div>
                                <div style="font-size: 0.85rem; color: var(--stardust-500);">Leaderboard</div>
                            </div>
                        </div>

                        <div style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(236, 72, 153, 0.05) 100%); padding: var(--space-5); border-radius: var(--radius-lg); margin-bottom: var(--space-6);">
                            <h4 style="font-family: var(--font-display); font-weight: 600; color: var(--cosmic-purple); margin-bottom: var(--space-2);">✨ What You'll Learn</h4>
                            <ul style="text-align: left; color: var(--stardust-600); line-height: 1.8; max-width: 400px; margin: 0 auto;">
                                <li>• What a typical day in this field looks like</li>
                                <li>• If your personality fits the work environment</li>
                                <li>• Skills and mindsets needed for success</li>
                                <li>• Whether this career aligns with your values</li>
                            </ul>
                        </div>

                        <a href="quiz.php?course=<?php echo urlencode($primaryCourse['short_name'] ?? $primaryCourse['course_name']); ?>" class="btn btn-stellar btn-lg">
                            🚀 Start the Quiz
                        </a>

                        <p style="font-size: 0.85rem; color: var(--stardust-400); margin-top: var(--space-4);">
                            Score well and appear on the leaderboard! 🏆
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alternative Courses -->
            <?php if (!empty($altCourses)): ?>
                <div class="alt-section">
                    <h2>🌌 Other Stellar Matches</h2>
                    <p style="text-align: center; color: var(--stardust-500); margin-bottom: var(--space-6);">Click on any course to view full details, careers, and take the quiz</p>
                    <div class="alt-grid">
                        <?php foreach ($altCourses as $course): ?>
                            <a href="course-detail.php?course=<?php echo urlencode($course['short_name'] ?? $course['course_name']); ?>" class="alt-card" style="text-decoration: none;">
                                <div class="alt-card-header">
                                    <div style="display: flex; align-items: center; gap: var(--space-3);">
                                        <span style="font-size: 2rem;"><?php echo $course['icon'] ?? '🎓'; ?></span>
                                        <h3><?php echo htmlspecialchars($course['short_name'] ?? $course['course_name']); ?></h3>
                                    </div>
                                    <span class="alt-match"><?php echo $course['match_score']; ?>%</span>
                                </div>
                                <p><?php echo htmlspecialchars(substr($course['description'] ?? '', 0, 120)); ?>...</p>
                                <p class="course-meta">🏛️ <?php echo htmlspecialchars($course['university']); ?></p>
                                <div style="margin-top: var(--space-3); display: flex; gap: var(--space-2); flex-wrap: wrap;">
                                    <span style="font-size: 0.8rem; padding: var(--space-1) var(--space-2); background: rgba(124, 58, 237, 0.1); color: var(--cosmic-purple); border-radius: var(--radius-sm);">View Details</span>
                                    <span style="font-size: 0.8rem; padding: var(--space-1) var(--space-2); background: rgba(59, 130, 246, 0.1); color: var(--stellar-blue); border-radius: var(--radius-sm);">Take Quiz</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="actions-section">
                <a href="course-detail.php?course=<?php echo urlencode($primaryCourse['short_name'] ?? $primaryCourse['course_name']); ?>" class="btn btn-orbit btn-lg">📖 View Full Course Details</a>
                <a href="history.php" class="btn btn-orbit btn-lg">📜 View History</a>
                <a href="assessment.php" class="btn btn-ghost btn-lg">🔄 Retake Assessment</a>
                <a href="dashboard.php" class="btn btn-stellar btn-lg">🏠 Back to Dashboard</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="results-footer">
        <div class="container">
            <p>© 2025 CourseMatch. Made with ❤️ for Filipino Students 🇵🇭</p>
        </div>
    </footer>

    <script>
        // Animate match score
        const matchScoreEl = document.getElementById('matchScore');
        const targetScore = <?php echo $primaryCourse['match_score']; ?>;
        let currentScore = 0;

        function animateScore() {
            if (currentScore < targetScore) {
                currentScore += 1;
                matchScoreEl.textContent = currentScore + '%';
                setTimeout(animateScore, 20);
            }
        }

        // Start animation when page loads
        setTimeout(animateScore, 500);

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

        // Quiz option selection
        document.querySelectorAll('.quiz-option').forEach(option => {
            option.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                const name = radio.name;

                document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                    input.closest('.quiz-option').classList.remove('selected');
                });

                this.classList.add('selected');
                radio.checked = true;
            });
        });

        // Quiz evaluation
        function evaluateQuiz() {
            const q1 = document.querySelector('input[name="q1"]:checked');
            const q2 = document.querySelector('input[name="q2"]:checked');
            const q3 = document.querySelector('input[name="q3"]:checked');

            if (!q1 || !q2 || !q3) {
                alert('Please answer all questions first! 😊');
                return;
            }

            const score = parseInt(q1.value) + parseInt(q2.value) + parseInt(q3.value);
            const resultDiv = document.getElementById('quizResult');
            let html = '';

            if (score >= 7) {
                html = `
                    <div style="text-align: center;">
                        <h4 style="color: #065F46; font-family: var(--font-display); margin-bottom: var(--space-2);">🎉 Excellent Match!</h4>
                        <p style="color: #047857;"><?php echo $primaryCourse['course_code']; ?> seems like a perfect fit for you! Your interests align well with what this program offers.</p>
                    </div>
                `;
                resultDiv.className = 'quiz-result success';
            } else if (score >= 5) {
                html = `
                    <div style="text-align: center;">
                        <h4 style="color: #92400E; font-family: var(--font-display); margin-bottom: var(--space-2);">🤔 Good Match</h4>
                        <p style="color: #78350F;">You show moderate interest in <?php echo $primaryCourse['course_code']; ?>. Consider exploring more about the program.</p>
                    </div>
                `;
                resultDiv.className = 'quiz-result warning';
            } else {
                html = `
                    <div style="text-align: center;">
                        <h4 style="color: #92400E; font-family: var(--font-display); margin-bottom: var(--space-2);">💭 Consider Alternatives</h4>
                        <p style="color: #78350F;">Based on your responses, you might want to explore our alternative recommendations below!</p>
                    </div>
                `;
                resultDiv.className = 'quiz-result warning';
            }

            resultDiv.innerHTML = html;
            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>
