-- Database Update for ML Integration
-- Run this if you already have the database set up
-- This adds necessary columns for the ML recommendation system

-- Add assessment_id and recommendation_data columns to course_recommendations
ALTER TABLE course_recommendations 
ADD COLUMN IF NOT EXISTS assessment_id INT AFTER user_id,
ADD COLUMN IF NOT EXISTS recommendation_data JSON AFTER match_score,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Add index on assessment_id if not exists
-- Note: MySQL may throw error if index exists, that's okay
CREATE INDEX IF NOT EXISTS idx_assessment_id ON course_recommendations(assessment_id);

-- For fresh installs, you can also run this alternative version:
-- DROP TABLE IF EXISTS course_recommendations;
-- CREATE TABLE course_recommendations (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     user_id INT NOT NULL,
--     assessment_id INT,
--     course_name VARCHAR(255) NOT NULL,
--     course_description TEXT,
--     university_name VARCHAR(255),
--     match_score DECIMAL(5,2),
--     recommendation_data JSON,
--     reasoning TEXT,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
--     INDEX idx_user_id (user_id),
--     INDEX idx_assessment_id (assessment_id),
--     INDEX idx_match_score (match_score)
-- );

