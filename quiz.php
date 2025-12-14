<?php
require_once 'functions.php';
require_once __DIR__ . '/ml/quiz_config.php';
requireLogin();

$user = getCurrentUser();

// Get course from URL
$courseName = $_GET['course'] ?? '';
if (empty($courseName)) {
    redirect('dashboard.php', 'Please select a course first.');
}

// Get questions for this course
$questions = getQuizQuestions($courseName);
if (empty($questions)) {
    // No matching quiz available - redirect with message
    redirect('results.php', 'Quiz for this course is not yet available. Please try another course.');
}

// Handle quiz submission
$quizCompleted = false;
$quizResults = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Security error. Please try again.');
    }
    
    $answers = [];
    for ($i = 0; $i < count($questions); $i++) {
        $answers[$i] = $_POST["answer_$i"] ?? '';
    }
    
    $score = calculateQuizScore($answers, $questions);
    $interpretation = getScoreInterpretation($score['percentage']);
    $timeTaken = intval($_POST['time_taken'] ?? 0);
    
    // Save to database
    try {
        $pdo = getDBConnection();
        
        // Insert quiz result
        $stmt = $pdo->prepare("
            INSERT INTO quiz_results (user_id, course_name, score, total_questions, percentage, time_taken_seconds, answers_json, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user['id'],
            $courseName,
            $score['correct'],
            $score['total'],
            $score['percentage'],
            $timeTaken,
            json_encode($answers)
        ]);
        
        // Update leaderboard
        $stmt = $pdo->prepare("
            INSERT INTO quiz_leaderboard (user_id, course_name, best_score, best_percentage, attempts, last_attempt_at)
            VALUES (?, ?, ?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE
                best_score = GREATEST(best_score, VALUES(best_score)),
                best_percentage = GREATEST(best_percentage, VALUES(best_percentage)),
                attempts = attempts + 1,
                last_attempt_at = NOW()
        ");
        $stmt->execute([
            $user['id'],
            $courseName,
            $score['correct'],
            $score['percentage']
        ]);
        
        // Get user's rank
        $stmt = $pdo->prepare("
            SELECT COUNT(*) + 1 as rank_position
            FROM quiz_leaderboard
            WHERE course_name = ? AND best_percentage > ?
        ");
        $stmt->execute([$courseName, $score['percentage']]);
        $rankResult = $stmt->fetch();
        $userRank = $rankResult['rank_position'] ?? 0;
        
        $quizCompleted = true;
        $quizResults = [
            'score' => $score,
            'interpretation' => $interpretation,
            'answers' => $answers,
            'rank' => $userRank,
            'time' => $timeTaken
        ];
        
    } catch (PDOException $e) {
        error_log("Quiz save error: " . $e->getMessage());
        // Continue without saving - still show results
        $quizCompleted = true;
        $quizResults = [
            'score' => $score,
            'interpretation' => $interpretation,
            'answers' => $answers,
            'rank' => 0,
            'time' => $timeTaken
        ];
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Quiz - <?php echo htmlspecialchars($courseName); ?> | CourseMatch ✨</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--galaxy-white);
        }

        .quiz-nav {
            background: var(--pure-white);
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .quiz-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .quiz-info {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .quiz-timer {
            font-family: var(--font-mono);
            font-size: 1rem;
            color: var(--cosmic-purple);
            background: rgba(124, 58, 237, 0.1);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
        }

        /* Progress Bar */
        .quiz-progress {
            background: var(--stardust-100);
            padding: var(--space-4) 0;
            border-bottom: 1px solid var(--stardust-200);
        }

        .progress-wrapper {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .progress-bar {
            flex: 1;
            height: 10px;
            background: var(--stardust-200);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--starburst-gradient);
            border-radius: var(--radius-full);
            transition: width 0.3s var(--ease-bounce);
        }

        .progress-text {
            font-family: var(--font-mono);
            font-size: 0.9rem;
            color: var(--stardust-500);
            min-width: 80px;
            text-align: right;
        }

        /* Quiz Container */
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--space-8) var(--space-5);
        }

        /* Question Card */
        .question-card {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            padding: var(--space-7);
            margin-bottom: var(--space-6);
            display: none;
            animation: slideIn 0.4s var(--ease-bounce);
        }

        .question-card.active {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .question-number {
            font-family: var(--font-mono);
            font-size: 0.85rem;
            color: var(--cosmic-purple);
            background: rgba(124, 58, 237, 0.1);
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            display: inline-block;
            margin-bottom: var(--space-4);
        }

        .question-text {
            font-family: var(--font-display);
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--space-dark);
            line-height: 1.5;
            margin-bottom: var(--space-6);
        }

        /* Options */
        .options-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .option-item {
            display: flex;
            align-items: flex-start;
            gap: var(--space-4);
            padding: var(--space-4) var(--space-5);
            background: var(--stardust-100);
            border: 2px solid transparent;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-out);
        }

        .option-item:hover {
            background: var(--pure-white);
            border-color: var(--cosmic-purple-light);
            transform: translateX(5px);
        }

        .option-item.selected {
            background: rgba(124, 58, 237, 0.1);
            border-color: var(--cosmic-purple);
        }

        .option-item.correct {
            background: var(--success-light);
            border-color: var(--success);
        }

        .option-item.incorrect {
            background: var(--error-light);
            border-color: var(--error);
        }

        .option-item.disabled {
            pointer-events: none;
        }

        .option-item input {
            display: none;
        }

        .option-letter {
            width: 36px;
            height: 36px;
            background: var(--stardust-200);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--stardust-600);
            flex-shrink: 0;
            transition: all var(--duration-fast) var(--ease-out);
        }

        .option-item.selected .option-letter {
            background: var(--cosmic-purple);
            color: var(--pure-white);
        }

        .option-item.correct .option-letter {
            background: var(--success);
            color: var(--pure-white);
        }

        .option-item.incorrect .option-letter {
            background: var(--error);
            color: var(--pure-white);
        }

        .option-text {
            font-size: 1.05rem;
            color: var(--space-dark);
            line-height: 1.5;
            padding-top: 5px;
        }

        /* Explanation Box */
        .explanation-box {
            display: none;
            margin-top: var(--space-5);
            padding: var(--space-5);
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-left: 4px solid var(--cosmic-purple);
            border-radius: var(--radius-md);
            animation: fadeIn 0.3s var(--ease-out);
        }

        .explanation-box.show {
            display: block;
        }

        .explanation-title {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--cosmic-purple);
            margin-bottom: var(--space-2);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .explanation-text {
            color: var(--stardust-600);
            line-height: 1.7;
        }

        /* Navigation Buttons */
        .quiz-nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: var(--space-6);
            gap: var(--space-4);
        }

        .btn-quiz {
            padding: var(--space-3) var(--space-6);
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-bounce);
        }

        .btn-next {
            background: var(--starburst-gradient);
            color: var(--pure-white);
            border: none;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
            margin-left: auto;
        }

        .btn-next:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(124, 58, 237, 0.4);
        }

        .btn-next:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Results Section */
        .results-container {
            display: none;
        }

        .results-container.show {
            display: block;
        }

        .results-card {
            background: var(--pure-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            padding: var(--space-8);
            text-align: center;
        }

        .results-icon {
            font-size: 5rem;
            margin-bottom: var(--space-4);
        }

        .results-score {
            font-family: var(--font-mono);
            font-size: 4rem;
            font-weight: 700;
            background: var(--starburst-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: var(--space-2);
        }

        .results-title {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--space-dark);
            margin-bottom: var(--space-3);
        }

        .results-message {
            font-size: 1.1rem;
            color: var(--stardust-500);
            line-height: 1.7;
            max-width: 500px;
            margin: 0 auto var(--space-6);
        }

        .results-stats {
            display: flex;
            justify-content: center;
            gap: var(--space-8);
            margin-bottom: var(--space-6);
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-family: var(--font-mono);
            font-size: 2rem;
            font-weight: 700;
            color: var(--cosmic-purple);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--stardust-500);
        }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-5);
            background: var(--aurora-gradient);
            color: var(--pure-white);
            border-radius: var(--radius-full);
            font-family: var(--font-display);
            font-weight: 600;
            margin-bottom: var(--space-6);
        }

        .results-actions {
            display: flex;
            justify-content: center;
            gap: var(--space-4);
            flex-wrap: wrap;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .quiz-container {
                padding: var(--space-5) var(--space-4);
            }

            .question-card {
                padding: var(--space-5);
            }

            .question-text {
                font-size: 1.2rem;
            }

            .option-item {
                padding: var(--space-3) var(--space-4);
            }

            .results-stats {
                gap: var(--space-5);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="quiz-nav">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">✨</span>
                CourseMatch
            </a>
            <div class="quiz-info">
                <span class="quiz-timer" id="quizTimer">00:00</span>
            </div>
        </div>
    </nav>

    <?php if (!$quizCompleted): ?>
    <!-- Progress Bar -->
    <div class="quiz-progress">
        <div class="container">
            <div class="progress-wrapper">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-text" id="progressText">Question 1 of <?php echo count($questions); ?></div>
            </div>
        </div>
    </div>

    <!-- Quiz Content -->
    <main class="quiz-container">
        <form method="POST" id="quizForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="time_taken" id="timeTaken" value="0">
            <input type="hidden" name="submit_quiz" value="1">

            <?php foreach ($questions as $index => $q): ?>
            <div class="question-card" id="question-<?php echo $index; ?>" data-index="<?php echo $index; ?>">
                <span class="question-number">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></span>
                <h2 class="question-text"><?php echo htmlspecialchars($q['question']); ?></h2>

                <div class="options-list">
                    <?php foreach ($q['options'] as $letter => $text): ?>
                    <label class="option-item" data-answer="<?php echo $letter; ?>">
                        <input type="radio" name="answer_<?php echo $index; ?>" value="<?php echo $letter; ?>">
                        <span class="option-letter"><?php echo $letter; ?></span>
                        <span class="option-text"><?php echo htmlspecialchars($text); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="explanation-box" id="explanation-<?php echo $index; ?>">
                    <div class="explanation-title">
                        <span>💡</span> Did you know?
                    </div>
                    <p class="explanation-text"><?php echo htmlspecialchars($q['explanation']); ?></p>
                </div>

                <div class="quiz-nav-buttons">
                    <button type="button" class="btn-quiz btn-next" id="nextBtn-<?php echo $index; ?>" disabled onclick="nextQuestion(<?php echo $index; ?>)">
                        <?php echo $index < count($questions) - 1 ? 'Next Question →' : 'See Results 🌟'; ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </form>
    </main>

    <script>
        // Quiz state
        const totalQuestions = <?php echo count($questions); ?>;
        const correctAnswers = <?php echo json_encode(array_column($questions, 'correct')); ?>;
        let currentQuestion = 0;
        let answeredQuestions = new Set();
        let startTime = Date.now();
        let timerInterval;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            showQuestion(0);
            startTimer();
            setupOptionListeners();
        });

        // Timer
        function startTimer() {
            timerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                document.getElementById('quizTimer').textContent = `${minutes}:${seconds}`;
                document.getElementById('timeTaken').value = elapsed;
            }, 1000);
        }

        // Show question
        function showQuestion(index) {
            document.querySelectorAll('.question-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById(`question-${index}`).classList.add('active');
            currentQuestion = index;
            updateProgress();
        }

        // Update progress
        function updateProgress() {
            const progress = ((currentQuestion + 1) / totalQuestions) * 100;
            document.getElementById('progressFill').style.width = `${progress}%`;
            document.getElementById('progressText').textContent = `Question ${currentQuestion + 1} of ${totalQuestions}`;
        }

        // Setup option listeners
        function setupOptionListeners() {
            document.querySelectorAll('.option-item').forEach(option => {
                option.addEventListener('click', function() {
                    const card = this.closest('.question-card');
                    const questionIndex = parseInt(card.dataset.index);
                    const selectedAnswer = this.dataset.answer;
                    const correctAnswer = correctAnswers[questionIndex];

                    // If already answered, ignore
                    if (answeredQuestions.has(questionIndex)) return;

                    // Mark as answered
                    answeredQuestions.add(questionIndex);

                    // Check answer
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;

                    // Disable all options
                    card.querySelectorAll('.option-item').forEach(opt => {
                        opt.classList.add('disabled');
                        if (opt.dataset.answer === correctAnswer) {
                            opt.classList.add('correct');
                        } else if (opt === this && selectedAnswer !== correctAnswer) {
                            opt.classList.add('incorrect');
                        }
                    });

                    // Show explanation
                    document.getElementById(`explanation-${questionIndex}`).classList.add('show');

                    // Enable next button
                    document.getElementById(`nextBtn-${questionIndex}`).disabled = false;
                });
            });
        }

        // Next question
        function nextQuestion(currentIndex) {
            if (currentIndex < totalQuestions - 1) {
                showQuestion(currentIndex + 1);
            } else {
                // Submit form
                clearInterval(timerInterval);
                document.getElementById('quizForm').submit();
            }
        }
    </script>

    <?php else: ?>
    <!-- Results -->
    <main class="quiz-container">
        <div class="results-container show">
            <div class="results-card">
                <div class="results-icon">
                    <?php echo $quizResults['score']['percentage'] >= 70 ? '🌟' : ($quizResults['score']['percentage'] >= 50 ? '✨' : '💫'); ?>
                </div>
                
                <div class="results-score"><?php echo $quizResults['score']['percentage']; ?>%</div>
                
                <h1 class="results-title"><?php echo $quizResults['interpretation']['title']; ?></h1>
                
                <p class="results-message"><?php echo $quizResults['interpretation']['message']; ?></p>

                <?php if ($quizResults['rank'] > 0): ?>
                <div class="rank-badge">
                    🏆 You ranked #<?php echo $quizResults['rank']; ?> for <?php echo htmlspecialchars($courseName); ?>!
                </div>
                <?php endif; ?>

                <div class="results-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $quizResults['score']['correct']; ?>/<?php echo $quizResults['score']['total']; ?></div>
                        <div class="stat-label">Correct Answers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo floor($quizResults['time'] / 60); ?>:<?php echo str_pad($quizResults['time'] % 60, 2, '0', STR_PAD_LEFT); ?></div>
                        <div class="stat-label">Time Taken</div>
                    </div>
                </div>

                <div class="results-actions">
                    <a href="quiz.php?course=<?php echo urlencode($courseName); ?>" class="btn btn-orbit btn-lg">🔄 Try Again</a>
                    <a href="leaderboard.php?course=<?php echo urlencode($courseName); ?>" class="btn btn-stellar btn-lg">🏆 View Leaderboard</a>
                    <a href="results.php" class="btn btn-ghost btn-lg">← Back to Results</a>
                </div>
            </div>
        </div>
    </main>
    <?php endif; ?>
</body>
</html>

