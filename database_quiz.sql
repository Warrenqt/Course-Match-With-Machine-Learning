-- Quiz and Leaderboard Database Schema
-- Run this SQL to add quiz-related tables

-- Quiz Results Table
CREATE TABLE IF NOT EXISTS quiz_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    score INT NOT NULL DEFAULT 0,
    total_questions INT NOT NULL DEFAULT 20,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    time_taken_seconds INT DEFAULT NULL,
    answers_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_course_name (course_name),
    INDEX idx_percentage (percentage DESC),
    INDEX idx_created_at (created_at)
);

-- User's Best Score per Course (for leaderboard)
CREATE TABLE IF NOT EXISTS quiz_leaderboard (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    best_score INT NOT NULL DEFAULT 0,
    best_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    attempts INT DEFAULT 1,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course (user_id, course_name),
    INDEX idx_course_score (course_name, best_percentage DESC),
    INDEX idx_user_id (user_id)
);

-- View for Leaderboard Rankings (optional - can use query instead)
-- This creates a view that shows rankings per course
-- CREATE VIEW leaderboard_rankings AS
-- SELECT 
--     ql.id,
--     ql.user_id,
--     u.name as user_name,
--     ql.course_name,
--     ql.best_score,
--     ql.best_percentage,
--     ql.attempts,
--     ql.last_attempt_at,
--     RANK() OVER (PARTITION BY ql.course_name ORDER BY ql.best_percentage DESC, ql.last_attempt_at ASC) as rank_position
-- FROM quiz_leaderboard ql
-- JOIN users u ON ql.user_id = u.id
-- ORDER BY ql.course_name, ql.best_percentage DESC;

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_quiz_results_user_course ON quiz_results(user_id, course_name);
CREATE INDEX IF NOT EXISTS idx_leaderboard_ranking ON quiz_leaderboard(course_name, best_percentage DESC);

