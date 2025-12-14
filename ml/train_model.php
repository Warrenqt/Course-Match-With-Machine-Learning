<?php
/**
 * KNN Model Training Script
 * Trains a K-Nearest Neighbors classifier using the student dataset
 * 
 * Usage: Run this script via CLI or browser to train the model
 * php train_model.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\Dataset\CsvDataset;
use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;

// Configuration
define('DATA_FILE', __DIR__ . '/data/college_course_dataset_standardized.csv');
define('MODEL_FILE', __DIR__ . '/models/course_classifier.model');
define('K_NEIGHBORS', 5); // Number of neighbors for KNN

/**
 * Main training function
 */
function trainModel() {
    echo "===========================================\n";
    echo "  CourseMatch ML Model Training\n";
    echo "===========================================\n\n";
    
    // Step 1: Load Dataset
    echo "[1/6] Loading dataset...\n";
    
    if (!file_exists(DATA_FILE)) {
        die("ERROR: Dataset file not found at: " . DATA_FILE . "\n");
    }
    
    $dataset = new CsvDataset(DATA_FILE, 9, true); // 9 features, has header
    $samples = $dataset->getSamples();
    $labels = $dataset->getTargets();
    
    echo "      Loaded " . count($samples) . " samples\n";
    echo "      Features: 9 (Interest, Personality, 7 Subject Grades)\n";
    echo "      Unique courses: " . count(array_unique($labels)) . "\n\n";
    
    // Step 2: Preprocess Data (Normalize grades to 0-1)
    echo "[2/6] Preprocessing data...\n";
    
    $normalizedSamples = [];
    foreach ($samples as $sample) {
        $normalized = [];
        // Interest (1-5) -> normalize to 0-1
        $normalized[] = ((float)$sample[0] - 1) / 4;
        // Personality (1-5) -> normalize to 0-1
        $normalized[] = ((float)$sample[1] - 1) / 4;
        // Grades (0-100) -> normalize to 0-1
        for ($i = 2; $i < 9; $i++) {
            $normalized[] = (float)$sample[$i] / 100;
        }
        $normalizedSamples[] = $normalized;
    }
    
    echo "      Normalized Interest & Personality (0-1)\n";
    echo "      Normalized Grades (0-1)\n\n";
    
    // Step 3: Split Data (80% train, 20% test)
    echo "[3/6] Splitting data (80% train, 20% test)...\n";
    
    // Create ArrayDataset for the split
    $arrayDataset = new ArrayDataset($normalizedSamples, $labels);
    $split = new StratifiedRandomSplit($arrayDataset, 0.2);
    
    $trainSamples = $split->getTrainSamples();
    $trainLabels = $split->getTrainLabels();
    $testSamples = $split->getTestSamples();
    $testLabels = $split->getTestLabels();
    
    echo "      Training samples: " . count($trainSamples) . "\n";
    echo "      Testing samples: " . count($testSamples) . "\n\n";
    
    // Step 4: Train KNN Classifier
    echo "[4/6] Training KNN classifier (k=" . K_NEIGHBORS . ")...\n";
    
    $classifier = new KNearestNeighbors(K_NEIGHBORS);
    $classifier->train($trainSamples, $trainLabels);
    
    echo "      Model trained successfully!\n\n";
    
    // Step 5: Evaluate Model
    echo "[5/6] Evaluating model accuracy...\n";
    
    $predictions = $classifier->predict($testSamples);
    $accuracy = Accuracy::score($testLabels, $predictions);
    
    echo "      Test Accuracy: " . round($accuracy * 100, 2) . "%\n\n";
    
    // Show per-class accuracy
    $classCorrect = [];
    $classTotal = [];
    
    for ($i = 0; $i < count($testLabels); $i++) {
        $actual = $testLabels[$i];
        $predicted = $predictions[$i];
        
        if (!isset($classTotal[$actual])) {
            $classTotal[$actual] = 0;
            $classCorrect[$actual] = 0;
        }
        
        $classTotal[$actual]++;
        if ($actual === $predicted) {
            $classCorrect[$actual]++;
        }
    }
    
    echo "      Per-Course Accuracy:\n";
    foreach ($classTotal as $course => $total) {
        $correct = $classCorrect[$course] ?? 0;
        $pct = $total > 0 ? round(($correct / $total) * 100, 1) : 0;
        echo "        - $course: $pct% ($correct/$total)\n";
    }
    echo "\n";
    
    // Step 6: Save Model
    echo "[6/6] Saving model to file...\n";
    
    // Create models directory if not exists
    $modelsDir = dirname(MODEL_FILE);
    if (!is_dir($modelsDir)) {
        mkdir($modelsDir, 0755, true);
    }
    
    // Serialize and save the model
    $modelData = serialize($classifier);
    file_put_contents(MODEL_FILE, $modelData);
    
    $fileSize = round(filesize(MODEL_FILE) / 1024, 2);
    echo "      Model saved to: " . MODEL_FILE . "\n";
    echo "      File size: " . $fileSize . " KB\n\n";
    
    echo "===========================================\n";
    echo "  Training Complete!\n";
    echo "  Accuracy: " . round($accuracy * 100, 2) . "%\n";
    echo "===========================================\n";
    
    return [
        'success' => true,
        'accuracy' => $accuracy,
        'samples' => count($samples),
        'model_file' => MODEL_FILE
    ];
}

/**
 * Retrain model with full dataset (no test split)
 * Use this for production after confirming accuracy is acceptable
 */
function trainModelFull() {
    echo "Training with FULL dataset (no test split)...\n\n";
    
    $dataset = new CsvDataset(DATA_FILE, 9, true);
    $samples = $dataset->getSamples();
    $labels = $dataset->getTargets();
    
    // Normalize
    $normalizedSamples = [];
    foreach ($samples as $sample) {
        $normalized = [];
        $normalized[] = ((float)$sample[0] - 1) / 4;
        $normalized[] = ((float)$sample[1] - 1) / 4;
        for ($i = 2; $i < 9; $i++) {
            $normalized[] = (float)$sample[$i] / 100;
        }
        $normalizedSamples[] = $normalized;
    }
    
    // Train on ALL data
    $classifier = new KNearestNeighbors(K_NEIGHBORS);
    $classifier->train($normalizedSamples, $labels);
    
    // Save
    $modelsDir = dirname(MODEL_FILE);
    if (!is_dir($modelsDir)) {
        mkdir($modelsDir, 0755, true);
    }
    
    file_put_contents(MODEL_FILE, serialize($classifier));
    
    echo "Full model trained and saved!\n";
    echo "Total samples used: " . count($samples) . "\n";
    
    return true;
}

// Run training if called directly
if (php_sapi_name() === 'cli' || isset($_GET['train'])) {
    // Check if PHP-ML is installed
    if (!class_exists('Phpml\Classification\KNearestNeighbors')) {
        die("ERROR: PHP-ML library not found. Run: composer require php-ai/php-ml\n");
    }
    
    // Set content type for browser
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: text/plain');
    }
    
    // Train the model
    $result = trainModel();
    
    // Optionally train full model
    if (isset($_GET['full']) || (isset($argv[1]) && $argv[1] === '--full')) {
        echo "\n\n";
        trainModelFull();
    }
}

