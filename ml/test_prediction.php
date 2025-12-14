<?php
/**
 * Test Prediction Script
 * Verifies the ML predictor is working correctly
 */

require_once __DIR__ . '/predictor.php';

echo "===========================================\n";
echo "  CourseMatch Prediction Test\n";
echo "===========================================\n\n";

$predictor = new CoursePredictor();

// Check if model is ready
$modelInfo = $predictor->getModelInfo();
echo "Model Status:\n";
echo "  Ready: " . ($modelInfo['ready'] ? 'Yes' : 'No') . "\n";
if ($modelInfo['ready']) {
    echo "  Size: " . $modelInfo['size'] . "\n";
    echo "  Last Modified: " . $modelInfo['modified'] . "\n";
}
echo "\n";

// Test Case 1: Technology-focused student
echo "===========================================\n";
echo "Test Case 1: Technology-focused Student\n";
echo "===========================================\n";

$input1 = [
    'interest' => 3,  // Technology
    'personality' => 1, // Analytical
    'mathematics' => 92,
    'science' => 88,
    'english' => 85,
    'filipino' => 80,
    'mapeh' => 75,
    'tle' => 95,
    'araling_panlipunan' => 78
];

echo "Input:\n";
echo "  Interest: Technology (3)\n";
echo "  Personality: Analytical (1)\n";
echo "  Mathematics: 92, Science: 88, TLE: 95\n\n";

$recommendations1 = $predictor->getTopRecommendations($input1, 3);

echo "Recommendations:\n";
foreach ($recommendations1 as $rec) {
    echo "  #{$rec['rank']}: {$rec['course_name']} ({$rec['match_score']}%)\n";
    echo "       {$rec['icon']} {$rec['full_name']}\n";
}
echo "\n";

// Test Case 2: Creative student
echo "===========================================\n";
echo "Test Case 2: Creative/Art Student\n";
echo "===========================================\n";

$input2 = [
    'interest' => 2,  // Art/Creativity
    'personality' => 4, // Creative
    'mathematics' => 78,
    'science' => 75,
    'english' => 90,
    'filipino' => 88,
    'mapeh' => 95,
    'tle' => 82,
    'araling_panlipunan' => 85
];

echo "Input:\n";
echo "  Interest: Art/Creativity (2)\n";
echo "  Personality: Creative (4)\n";
echo "  English: 90, MAPEH: 95\n\n";

$recommendations2 = $predictor->getTopRecommendations($input2, 3);

echo "Recommendations:\n";
foreach ($recommendations2 as $rec) {
    echo "  #{$rec['rank']}: {$rec['course_name']} ({$rec['match_score']}%)\n";
    echo "       {$rec['icon']} {$rec['full_name']}\n";
}
echo "\n";

// Test Case 3: Business-minded student
echo "===========================================\n";
echo "Test Case 3: Business-minded Student\n";
echo "===========================================\n";

$input3 = [
    'interest' => 4,  // Business
    'personality' => 3, // Adventurous
    'mathematics' => 85,
    'science' => 78,
    'english' => 88,
    'filipino' => 85,
    'mapeh' => 80,
    'tle' => 82,
    'araling_panlipunan' => 90
];

echo "Input:\n";
echo "  Interest: Business (4)\n";
echo "  Personality: Adventurous (3)\n";
echo "  Araling Panlipunan: 90, English: 88\n\n";

$recommendations3 = $predictor->getTopRecommendations($input3, 3);

echo "Recommendations:\n";
foreach ($recommendations3 as $rec) {
    echo "  #{$rec['rank']}: {$rec['course_name']} ({$rec['match_score']}%)\n";
    echo "       {$rec['icon']} {$rec['full_name']}\n";
}
echo "\n";

// Test Case 4: Culinary student
echo "===========================================\n";
echo "Test Case 4: Culinary/Hospitality Student\n";
echo "===========================================\n";

$input4 = [
    'interest' => 5,  // Culinary
    'personality' => 2, // Empathetic
    'mathematics' => 75,
    'science' => 80,
    'english' => 85,
    'filipino' => 82,
    'mapeh' => 88,
    'tle' => 95,
    'araling_panlipunan' => 78
];

echo "Input:\n";
echo "  Interest: Culinary (5)\n";
echo "  Personality: Empathetic (2)\n";
echo "  TLE: 95, MAPEH: 88\n\n";

$recommendations4 = $predictor->getTopRecommendations($input4, 3);

echo "Recommendations:\n";
foreach ($recommendations4 as $rec) {
    echo "  #{$rec['rank']}: {$rec['course_name']} ({$rec['match_score']}%)\n";
    echo "       {$rec['icon']} {$rec['full_name']}\n";
}
echo "\n";

echo "===========================================\n";
echo "  All Tests Complete!\n";
echo "===========================================\n";

