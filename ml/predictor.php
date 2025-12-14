<?php
/**
 * Course Recommendation Predictor
 * Loads trained KNN model and makes predictions
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/courses_config.php';

use Phpml\Classification\KNearestNeighbors;

class CoursePredictor {
    private $classifier;
    private $modelFile;
    private $isLoaded = false;
    
    // Feature order must match training data
    private $featureOrder = [
        'interest',
        'personality', 
        'mathematics',
        'science',
        'english',
        'filipino',
        'mapeh',
        'tle',
        'araling_panlipunan'
    ];
    
    public function __construct() {
        $this->modelFile = __DIR__ . '/models/course_classifier.model';
    }
    
    /**
     * Load the trained model
     */
    public function loadModel() {
        if ($this->isLoaded) {
            return true;
        }
        
        if (!file_exists($this->modelFile)) {
            throw new Exception("Model file not found. Please run train_model.php first.");
        }
        
        $modelData = file_get_contents($this->modelFile);
        $this->classifier = unserialize($modelData);
        
        if (!$this->classifier instanceof KNearestNeighbors) {
            throw new Exception("Invalid model file format.");
        }
        
        $this->isLoaded = true;
        return true;
    }
    
    /**
     * Normalize input features to match training data
     */
    private function normalizeInput($input) {
        $normalized = [];
        
        // Interest (1-5) -> 0-1
        $normalized[] = ((float)$input['interest'] - 1) / 4;
        
        // Personality (1-5) -> 0-1
        $normalized[] = ((float)$input['personality'] - 1) / 4;
        
        // Grades (0-100) -> 0-1
        $normalized[] = (float)$input['mathematics'] / 100;
        $normalized[] = (float)$input['science'] / 100;
        $normalized[] = (float)$input['english'] / 100;
        $normalized[] = (float)$input['filipino'] / 100;
        $normalized[] = (float)$input['mapeh'] / 100;
        $normalized[] = (float)$input['tle'] / 100;
        $normalized[] = (float)$input['araling_panlipunan'] / 100;
        
        return $normalized;
    }
    
    /**
     * Make a single prediction
     */
    public function predict($input) {
        $this->loadModel();
        $normalized = $this->normalizeInput($input);
        return $this->classifier->predict([$normalized])[0];
    }
    
    /**
     * Get top N course recommendations with confidence scores
     * Uses a custom scoring approach based on K nearest neighbors
     */
    public function getTopRecommendations($input, $topN = 3) {
        $this->loadModel();
        $normalized = $this->normalizeInput($input);
        
        // Get the primary prediction
        $primaryPrediction = $this->classifier->predict([$normalized])[0];
        
        // Calculate course scores based on feature similarity
        $courseScores = $this->calculateCourseScores($normalized);
        
        // Sort by score descending
        arsort($courseScores);
        
        // Get top N courses
        $topCourses = array_slice($courseScores, 0, $topN, true);
        
        // Format results with course information
        $recommendations = [];
        $rank = 1;
        
        foreach ($topCourses as $courseName => $score) {
            $courseInfo = getCourseInfo($courseName);
            
            $recommendations[] = [
                'rank' => $rank,
                'course_name' => $courseName,
                'match_score' => round($score, 1),
                'code' => $courseInfo['code'] ?? '',
                'full_name' => $courseInfo['full_name'] ?? $courseName,
                'description' => $courseInfo['description'] ?? '',
                'duration' => $courseInfo['duration'] ?? '4 years',
                'universities' => $courseInfo['universities'] ?? [],
                'careers' => $courseInfo['careers'] ?? [],
                'skills' => $courseInfo['skills'] ?? [],
                'icon' => $courseInfo['icon'] ?? '📚',
                'is_primary' => ($courseName === $primaryPrediction)
            ];
            
            $rank++;
        }
        
        return $recommendations;
    }
    
    /**
     * Calculate similarity scores for all courses
     * Based on comparing input features to typical student profiles for each course
     */
    private function calculateCourseScores($normalizedInput) {
        // Define ideal feature profiles for each course
        // [interest, personality, math, science, english, filipino, mapeh, tle, araling_panlipunan]
        // Values are weights: how important is each feature for this course
        
        $courseProfiles = [
            'Computer Science' => [
                'weights' => [0.8, 0.3, 0.95, 0.7, 0.4, 0.3, 0.2, 0.8, 0.3],
                'ideal_interest' => [1, 3], // Problem Solving, Technology
                'ideal_personality' => [1, 4] // Analytical, Creative
            ],
            'Information Technology' => [
                'weights' => [0.7, 0.3, 0.7, 0.6, 0.5, 0.4, 0.2, 0.9, 0.4],
                'ideal_interest' => [3, 1], // Technology, Problem Solving
                'ideal_personality' => [1, 5] // Analytical, Independent
            ],
            'Engineering' => [
                'weights' => [0.8, 0.4, 0.95, 0.9, 0.5, 0.3, 0.2, 0.7, 0.5],
                'ideal_interest' => [1, 3], // Problem Solving, Technology
                'ideal_personality' => [1, 3] // Analytical, Adventurous
            ],
            'Computer Engineering' => [
                'weights' => [0.8, 0.3, 0.9, 0.85, 0.4, 0.3, 0.2, 0.85, 0.3],
                'ideal_interest' => [3, 1], // Technology, Problem Solving
                'ideal_personality' => [1, 4] // Analytical, Creative
            ],
            'Business Administration' => [
                'weights' => [0.7, 0.6, 0.6, 0.4, 0.7, 0.6, 0.3, 0.5, 0.7],
                'ideal_interest' => [4, 1], // Business, Problem Solving
                'ideal_personality' => [3, 5] // Adventurous, Independent
            ],
            'Marketing' => [
                'weights' => [0.6, 0.7, 0.5, 0.3, 0.8, 0.6, 0.4, 0.5, 0.6],
                'ideal_interest' => [4, 2], // Business, Art/Creativity
                'ideal_personality' => [4, 2] // Creative, Empathetic
            ],
            'Accounting' => [
                'weights' => [0.5, 0.4, 0.9, 0.4, 0.6, 0.5, 0.2, 0.5, 0.6],
                'ideal_interest' => [4, 1], // Business, Problem Solving
                'ideal_personality' => [1, 5] // Analytical, Independent
            ],
            'Fine Arts' => [
                'weights' => [0.3, 0.8, 0.3, 0.3, 0.6, 0.5, 0.9, 0.6, 0.4],
                'ideal_interest' => [2], // Art/Creativity
                'ideal_personality' => [4, 3] // Creative, Adventurous
            ],
            'Multimedia Arts / Design' => [
                'weights' => [0.5, 0.7, 0.4, 0.3, 0.7, 0.5, 0.8, 0.7, 0.4],
                'ideal_interest' => [2, 3], // Art, Technology
                'ideal_personality' => [4, 5] // Creative, Independent
            ],
            'Culinary Arts' => [
                'weights' => [0.4, 0.6, 0.4, 0.5, 0.5, 0.5, 0.6, 0.9, 0.4],
                'ideal_interest' => [5, 2], // Cooking, Creativity
                'ideal_personality' => [4, 3] // Creative, Adventurous
            ],
            'Hotel & Restaurant Management' => [
                'weights' => [0.5, 0.8, 0.4, 0.4, 0.7, 0.6, 0.5, 0.8, 0.5],
                'ideal_interest' => [5, 4], // Cooking, Business
                'ideal_personality' => [2, 3] // Empathetic, Adventurous
            ],
            'Nutrition' => [
                'weights' => [0.4, 0.6, 0.5, 0.8, 0.6, 0.5, 0.5, 0.7, 0.5],
                'ideal_interest' => [5, 1], // Cooking, Problem Solving
                'ideal_personality' => [2, 1] // Empathetic, Analytical
            ],
            'Nursing' => [
                'weights' => [0.4, 0.9, 0.6, 0.9, 0.6, 0.6, 0.5, 0.5, 0.5],
                'ideal_interest' => [1, 5], // Problem Solving
                'ideal_personality' => [2, 1] // Empathetic, Analytical
            ],
            'Education' => [
                'weights' => [0.5, 0.9, 0.6, 0.6, 0.8, 0.8, 0.6, 0.5, 0.8],
                'ideal_interest' => [1, 2], // Problem Solving, Art
                'ideal_personality' => [2, 4] // Empathetic, Creative
            ],
            'Communication Arts / Journalism' => [
                'weights' => [0.4, 0.7, 0.4, 0.4, 0.95, 0.8, 0.5, 0.4, 0.7],
                'ideal_interest' => [2, 4], // Art, Business
                'ideal_personality' => [4, 3] // Creative, Adventurous
            ],
            'Architecture' => [
                'weights' => [0.6, 0.7, 0.8, 0.6, 0.5, 0.5, 0.7, 0.7, 0.5],
                'ideal_interest' => [2, 1], // Art, Problem Solving
                'ideal_personality' => [4, 1] // Creative, Analytical
            ]
        ];
        
        $scores = [];
        $userInterest = round($normalizedInput[0] * 4) + 1; // Convert back to 1-5
        $userPersonality = round($normalizedInput[1] * 4) + 1;
        
        foreach ($courseProfiles as $courseName => $profile) {
            $score = 0;
            $weights = $profile['weights'];
            
            // Calculate weighted grade score
            $gradeScore = 0;
            for ($i = 2; $i < 9; $i++) {
                $gradeScore += $normalizedInput[$i] * $weights[$i];
            }
            $gradeScore = ($gradeScore / array_sum(array_slice($weights, 2))) * 100;
            
            // Calculate interest match
            $interestScore = 0;
            if (in_array($userInterest, $profile['ideal_interest'])) {
                $interestScore = $userInterest === $profile['ideal_interest'][0] ? 100 : 80;
            } else {
                $interestScore = 40;
            }
            
            // Calculate personality match
            $personalityScore = 0;
            if (in_array($userPersonality, $profile['ideal_personality'])) {
                $personalityScore = $userPersonality === $profile['ideal_personality'][0] ? 100 : 80;
            } else {
                $personalityScore = 40;
            }
            
            // Combined score: 50% grades, 30% interest, 20% personality
            $score = ($gradeScore * 0.50) + ($interestScore * 0.30) + ($personalityScore * 0.20);
            
            $scores[$courseName] = $score;
        }
        
        return $scores;
    }
    
    /**
     * Validate input data
     */
    public function validateInput($input) {
        $errors = [];
        
        // Check required fields
        $required = ['interest', 'personality', 'mathematics', 'science', 
                     'english', 'filipino', 'mapeh', 'tle', 'araling_panlipunan'];
        
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                $errors[] = "Missing required field: $field";
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validate ranges
        if ($input['interest'] < 1 || $input['interest'] > 5) {
            $errors[] = "Interest must be between 1 and 5";
        }
        
        if ($input['personality'] < 1 || $input['personality'] > 5) {
            $errors[] = "Personality must be between 1 and 5";
        }
        
        $gradeFields = ['mathematics', 'science', 'english', 'filipino', 
                        'mapeh', 'tle', 'araling_panlipunan'];
        
        foreach ($gradeFields as $field) {
            $value = (float)$input[$field];
            if ($value < 0 || $value > 100) {
                $errors[] = ucfirst($field) . " grade must be between 0 and 100";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Check if model is trained and ready
     */
    public function isModelReady() {
        return file_exists($this->modelFile);
    }
    
    /**
     * Get model information
     */
    public function getModelInfo() {
        if (!$this->isModelReady()) {
            return ['ready' => false, 'message' => 'Model not trained'];
        }
        
        return [
            'ready' => true,
            'file' => $this->modelFile,
            'size' => round(filesize($this->modelFile) / 1024, 2) . ' KB',
            'modified' => date('Y-m-d H:i:s', filemtime($this->modelFile))
        ];
    }
}

// API endpoint for AJAX predictions
if (isset($_POST['action']) && $_POST['action'] === 'predict') {
    header('Content-Type: application/json');
    
    try {
        $predictor = new CoursePredictor();
        
        $input = [
            'interest' => $_POST['interest'] ?? 0,
            'personality' => $_POST['personality'] ?? 0,
            'mathematics' => $_POST['mathematics'] ?? 0,
            'science' => $_POST['science'] ?? 0,
            'english' => $_POST['english'] ?? 0,
            'filipino' => $_POST['filipino'] ?? 0,
            'mapeh' => $_POST['mapeh'] ?? 0,
            'tle' => $_POST['tle'] ?? 0,
            'araling_panlipunan' => $_POST['araling_panlipunan'] ?? 0
        ];
        
        $validation = $predictor->validateInput($input);
        
        if (!$validation['valid']) {
            echo json_encode([
                'success' => false,
                'errors' => $validation['errors']
            ]);
            exit;
        }
        
        $recommendations = $predictor->getTopRecommendations($input, 3);
        
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

