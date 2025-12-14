<?php
require_once 'functions.php';
require_once __DIR__ . '/ml/predictor.php';
requireLogin();

$user = getCurrentUser();

// Interest text to code mapping
$interestMap = [
    'problem_solving' => 1,
    'art_creativity' => 2,
    'technology' => 3,
    'business' => 4,
    'culinary' => 5
];

// Personality text to code mapping
$personalityMap = [
    'analytical' => 1,
    'empathetic' => 2,
    'adventurous' => 3,
    'creative' => 4,
    'independent' => 5
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Security error. Please try again.');
    }

    // Collect grade inputs
    $grades = [
        'mathematics' => floatval($_POST['math_grade'] ?? 0),
        'science' => floatval($_POST['science_grade'] ?? 0),
        'english' => floatval($_POST['english_grade'] ?? 0),
        'filipino' => floatval($_POST['filipino_grade'] ?? 0),
        'mapeh' => floatval($_POST['mapeh_grade'] ?? 0),
        'tle' => floatval($_POST['tle_grade'] ?? 0),
        'araling_panlipunan' => floatval($_POST['araling_panlipunan_grade'] ?? 0)
    ];

    $interestText = sanitizeInput($_POST['interest'] ?? '');
    $personalityText = sanitizeInput($_POST['personality'] ?? '');
    
    // Convert to numeric codes for ML model
    $interestCode = $interestMap[$interestText] ?? 0;
    $personalityCode = $personalityMap[$personalityText] ?? 0;

    // Validate grades
    $gradeValid = true;
    foreach ($grades as $subject => $grade) {
        if ($grade < 75 || $grade > 100) {
            $gradeValid = false;
            break;
        }
    }

    if (!$gradeValid || $interestCode === 0 || $personalityCode === 0) {
        $error = "Please fill in all fields correctly. Grades should be between 75 and 100.";
    } else {
        // Prepare input for ML predictor
        $mlInput = [
            'interest' => $interestCode,
            'personality' => $personalityCode,
            'mathematics' => $grades['mathematics'],
            'science' => $grades['science'],
            'english' => $grades['english'],
            'filipino' => $grades['filipino'],
            'mapeh' => $grades['mapeh'],
            'tle' => $grades['tle'],
            'araling_panlipunan' => $grades['araling_panlipunan']
        ];
        
        // Get ML predictions
        $predictor = new CoursePredictor();
        
        // Check if model is ready
        if (!$predictor->isModelReady()) {
            $error = "The recommendation system is being prepared. Please try again in a few minutes.";
        } else {
            try {
                // Get top 3 course recommendations
                $recommendations = $predictor->getTopRecommendations($mlInput, 3);
                
                // Save assessment to database
                $pdo = getDBConnection();
                
                $stmt = $pdo->prepare("
                    INSERT INTO user_assessments (user_id, subject_interest_score, grade_performance, career_interests, assessment_completed, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 1, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        subject_interest_score = VALUES(subject_interest_score),
                        grade_performance = VALUES(grade_performance),
                        career_interests = VALUES(career_interests),
                        assessment_completed = 1,
                        updated_at = NOW()
                ");

                $stmt->execute([
                    $user['id'],
                    json_encode(['interest' => $interestText, 'interest_code' => $interestCode, 'personality' => $personalityText, 'personality_code' => $personalityCode]),
                    json_encode($grades),
                    $interestText
                ]);
                
                // Save recommendations to database (compatible with existing schema)
                foreach ($recommendations as $rec) {
                    $stmt = $pdo->prepare("
                        INSERT INTO course_recommendations (user_id, course_name, course_description, university_name, match_score, reasoning, recommended_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user['id'],
                        $rec['course_name'],
                        $rec['description'] ?? '',
                        $rec['universities'][0] ?? 'Various Universities',
                        $rec['match_score'],
                        $rec['why_recommended'] ?? json_encode($rec)
                    ]);
                }

                // Store in session for results page
                $_SESSION['assessment_data'] = [
                    'grades' => $grades,
                    'interest' => $interestText,
                    'interest_code' => $interestCode,
                    'personality' => $personalityText,
                    'personality_code' => $personalityCode
                ];
                
                $_SESSION['recommendations'] = $recommendations;

                redirect('results.php', 'Your constellation is ready! 🌟');
                
            } catch (Exception $e) {
                error_log("ML Prediction Error: " . $e->getMessage());
                $error = "An error occurred while generating recommendations. Please try again.";
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
    <title>Course Assessment - CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        /* Navigation */
        .assess-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .assess-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .breadcrumb {
            color: var(--stardust-500);
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--stardust-500);
        }

        .breadcrumb a:hover {
            color: var(--cosmic-purple);
        }

        /* Progress Bar */
        .progress-container {
            background: var(--stardust-100);
            padding: var(--space-4) 0;
            border-bottom: 1px solid var(--stardust-200);
        }

        .progress-bar-wrap {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .progress-bar {
            flex: 1;
            height: 8px;
            background: var(--stardust-200);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--starburst-gradient);
            width: 0%;
            transition: width 0.5s var(--ease-bounce);
            border-radius: var(--radius-full);
        }

        .progress-text {
            font-family: var(--font-mono);
            font-size: 0.875rem;
            color: var(--stardust-500);
            min-width: 50px;
        }

        /* Main Content */
        .assess-content {
            padding: var(--space-8) 0;
        }

        .assess-card {
            background: var(--pure-white);
            max-width: 800px;
            margin: 0 auto;
            padding: var(--space-8);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--stardust-200);
        }

        .assess-header {
            text-align: center;
            margin-bottom: var(--space-7);
        }

        .assess-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4);
        }

        .assess-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
        }

        .assess-header p {
            color: var(--stardust-500);
            font-size: 1.1rem;
        }

        /* Section Title */
        .section-title {
            font-family: var(--font-display);
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--space-dark);
            margin: var(--space-7) 0 var(--space-4);
            padding-bottom: var(--space-3);
            border-bottom: 2px solid var(--stardust-200);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .section-title span {
            font-size: 1.5rem;
        }

        /* Grade Grid */
        .grade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--space-4);
            margin-bottom: var(--space-5);
        }

        .grade-item {
            background: var(--stardust-100);
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            border: 2px solid transparent;
            transition: all var(--duration-normal) var(--ease-out);
        }

        .grade-item:focus-within {
            border-color: var(--cosmic-purple);
            background: var(--pure-white);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .grade-item label {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-2);
            font-size: 0.9rem;
        }

        .grade-item input {
            width: 100%;
            padding: var(--space-3);
            border: 1px solid var(--stardust-300);
            border-radius: var(--radius-md);
            font-family: var(--font-mono);
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            background: var(--pure-white);
        }

        .grade-item input:focus {
            outline: none;
            border-color: var(--cosmic-purple);
        }

        /* Selection Cards */
        .selection-grid {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .selection-card {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            padding: var(--space-4) var(--space-5);
            background: var(--stardust-100);
            border: 2px solid transparent;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-out);
        }

        .selection-card:hover {
            background: var(--pure-white);
            border-color: var(--cosmic-purple-light);
            transform: translateX(5px);
        }

        .selection-card.selected {
            background: rgba(124, 58, 237, 0.05);
            border-color: var(--cosmic-purple);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .selection-card input[type="radio"] {
            display: none;
        }

        .selection-icon {
            font-size: 2rem;
            width: 50px;
            text-align: center;
        }

        .selection-content h4 {
            font-family: var(--font-display);
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--space-dark);
            margin-bottom: var(--space-1);
        }

        .selection-content p {
            color: var(--stardust-500);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .selection-check {
            margin-left: auto;
            width: 24px;
            height: 24px;
            border: 2px solid var(--stardust-300);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all var(--duration-fast) var(--ease-out);
        }

        .selection-card.selected .selection-check {
            background: var(--cosmic-purple);
            border-color: var(--cosmic-purple);
            color: var(--pure-white);
        }

        /* Error Message */
        .error-message {
            background: var(--error-light);
            color: #991B1B;
            padding: var(--space-4);
            border-radius: var(--radius-md);
            border: 1px solid #FCA5A5;
            margin-bottom: var(--space-5);
            text-align: center;
            font-weight: 500;
        }

        /* Submit Section */
        .submit-section {
            margin-top: var(--space-8);
            padding-top: var(--space-6);
            border-top: 2px solid var(--stardust-200);
            text-align: center;
        }

        .submit-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            color: var(--stardust-400);
            font-size: 0.9rem;
            margin-bottom: var(--space-5);
        }

        .btn-submit {
            padding: var(--space-4) var(--space-8);
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--pure-white);
            background: var(--starburst-gradient);
            border: none;
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-bounce);
            box-shadow: 0 4px 24px rgba(124, 58, 237, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 32px rgba(124, 58, 237, 0.5);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        @media (max-width: 768px) {
            .assess-card {
                padding: var(--space-5);
            }

            .grade-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .selection-card {
                padding: var(--space-3) var(--space-4);
            }

            .selection-icon {
                font-size: 1.5rem;
                width: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="assess-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a> / <span>Assessment</span>
            </div>
        </div>
    </nav>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="container">
            <div class="progress-bar-wrap">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-text" id="progressText">0%</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="assess-content">
        <div class="container">
            <div class="assess-card">
                <div class="assess-header">
                    <div class="assess-icon">🌟</div>
                    <h1>Build Your Constellation</h1>
                    <p>Share your academic journey to discover your stellar path</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message">
                        ⚠️ <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="assessmentForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <!-- Section 1: Grades -->
                    <h2 class="section-title">
                        <span>📚</span> Your SHS Grades
                    </h2>
                    <p style="color: var(--stardust-500); margin-bottom: var(--space-4);">Enter your average grades (75-100)</p>

                    <div class="grade-grid">
                        <div class="grade-item">
                            <label>📐 Mathematics</label>
                            <input type="number" name="math_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                        <div class="grade-item">
                            <label>🔬 Science</label>
                            <input type="number" name="science_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                        <div class="grade-item">
                            <label>📝 English</label>
                            <input type="number" name="english_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                        <div class="grade-item">
                            <label>🇵🇭 Filipino</label>
                            <input type="number" name="filipino_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                        <div class="grade-item">
                            <label>🎨 MAPEH</label>
                            <input type="number" name="mapeh_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                        <div class="grade-item">
                            <label>💻 TLE/ICT</label>
                            <input type="number" name="tle_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                        <div class="grade-item">
                            <label>🌍 Araling Panlipunan</label>
                            <input type="number" name="araling_panlipunan_grade" min="75" max="100" step="0.01" required placeholder="85">
                        </div>
                    </div>

                    <!-- Section 2: Interest -->
                    <h2 class="section-title">
                        <span>💡</span> Your Primary Interest
                    </h2>
                    <p style="color: var(--stardust-500); margin-bottom: var(--space-4);">What excites you the most?</p>

                    <div class="selection-grid">
                        <label class="selection-card">
                            <input type="radio" name="interest" value="problem_solving" required>
                            <span class="selection-icon">🧠</span>
                            <div class="selection-content">
                                <h4>Problem Solving & Logic</h4>
                                <p>You enjoy puzzles, analyzing problems, and finding solutions</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="interest" value="art_creativity" required>
                            <span class="selection-icon">🎨</span>
                            <div class="selection-content">
                                <h4>Art & Creativity</h4>
                                <p>You love expressing yourself through art, design, or writing</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="interest" value="technology" required>
                            <span class="selection-icon">💻</span>
                            <div class="selection-content">
                                <h4>Technology & Innovation</h4>
                                <p>You're fascinated by computers, coding, and digital innovation</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="interest" value="business" required>
                            <span class="selection-icon">💼</span>
                            <div class="selection-content">
                                <h4>Business & Entrepreneurship</h4>
                                <p>You're interested in commerce, management, and starting ventures</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="interest" value="culinary" required>
                            <span class="selection-icon">👨‍🍳</span>
                            <div class="selection-content">
                                <h4>Culinary & Hospitality</h4>
                                <p>You enjoy cooking, food service, and hospitality industries</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>
                    </div>

                    <!-- Section 3: Personality -->
                    <h2 class="section-title">
                        <span>🌟</span> Your Personality Type
                    </h2>
                    <p style="color: var(--stardust-500); margin-bottom: var(--space-4);">Which best describes you?</p>

                    <div class="selection-grid">
                        <label class="selection-card">
                            <input type="radio" name="personality" value="analytical" required>
                            <span class="selection-icon">📊</span>
                            <div class="selection-content">
                                <h4>Analytical & Logical</h4>
                                <p>You think systematically and make decisions based on facts</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="personality" value="empathetic" required>
                            <span class="selection-icon">💗</span>
                            <div class="selection-content">
                                <h4>Empathetic & Social</h4>
                                <p>You care deeply about people and enjoy helping others</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="personality" value="adventurous" required>
                            <span class="selection-icon">🏔️</span>
                            <div class="selection-content">
                                <h4>Adventurous & Bold</h4>
                                <p>You love challenges and trying new things</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="personality" value="creative" required>
                            <span class="selection-icon">💡</span>
                            <div class="selection-content">
                                <h4>Creative & Innovative</h4>
                                <p>You think outside the box and generate unique ideas</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>

                        <label class="selection-card">
                            <input type="radio" name="personality" value="independent" required>
                            <span class="selection-icon">🚀</span>
                            <div class="selection-content">
                                <h4>Independent & Self-Driven</h4>
                                <p>You prefer working independently and managing your own projects</p>
                            </div>
                            <span class="selection-check">✓</span>
                        </label>
                    </div>

                    <div class="submit-section">
                        <div class="submit-note">
                            <span>🔒</span> Your data is secure and only used for recommendations
                        </div>
                        <button type="submit" class="btn-submit">
                            🌟 Create My Constellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Selection card interaction
        document.querySelectorAll('.selection-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                const name = radio.name;

                document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                    input.closest('.selection-card').classList.remove('selected');
                });

                this.classList.add('selected');
                radio.checked = true;
                updateProgress();
            });
        });

        // Progress tracking
        function updateProgress() {
            const totalFields = 9; // 7 grades + 1 interest + 1 personality
            let filled = 0;

            document.querySelectorAll('input[type="number"]').forEach(input => {
                const value = parseFloat(input.value);
                if (value >= 75 && value <= 100) filled++;
            });

            if (document.querySelector('input[name="interest"]:checked')) filled++;
            if (document.querySelector('input[name="personality"]:checked')) filled++;

            const percentage = Math.round((filled / totalFields) * 100);
            document.getElementById('progressFill').style.width = percentage + '%';
            document.getElementById('progressText').textContent = percentage + '%';
        }

        // Update progress on input
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', updateProgress);
        });

        // Form validation
        document.getElementById('assessmentForm').addEventListener('submit', function(e) {
            let valid = true;

            document.querySelectorAll('input[type="number"]').forEach(input => {
                const value = parseFloat(input.value);
                if (value < 75 || value > 100) {
                    valid = false;
                    input.parentElement.style.borderColor = 'var(--error)';
                } else {
                    input.parentElement.style.borderColor = 'var(--success)';
                }
            });

            const interest = document.querySelector('input[name="interest"]:checked');
            const personality = document.querySelector('input[name="personality"]:checked');

            if (!valid || !interest || !personality) {
                e.preventDefault();
                alert('Please complete all fields correctly before submitting.');
                return false;
            }

            // Show loading
            const btn = this.querySelector('.btn-submit');
            btn.textContent = '⏳ Creating your constellation...';
            btn.disabled = true;
        });

        // Initialize progress
        updateProgress();
    </script>
</body>
</html>
