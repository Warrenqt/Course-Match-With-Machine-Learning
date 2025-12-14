-- Course Recommendation System Database Schema
-- Run this SQL to create the required database tables

-- Create database (uncomment if needed)
-- CREATE DATABASE course_recommendation;
-- USE course_recommendation;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Rate limiting table for login attempts
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_created_at (created_at)
);

-- Email verification tokens
CREATE TABLE email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
);

-- Password reset tokens
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
);

-- User sessions (optional, for additional security)
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
);

-- User quiz/assessment responses (for course recommendations)
CREATE TABLE user_assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject_interest_score JSON, -- Store subject preferences as JSON
    grade_performance JSON, -- Store grade data as JSON
    career_interests TEXT,
    assessment_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Course recommendations table
CREATE TABLE course_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    course_description TEXT,
    university_name VARCHAR(255),
    match_score DECIMAL(5,2), -- 0.00 to 100.00
    reasoning TEXT,
    recommended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_match_score (match_score)
);

-- Subjects master table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL UNIQUE,
    subject_category ENUM('stem', 'humanities', 'arts', 'business', 'health') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample subjects data
INSERT INTO subjects (subject_name, subject_category, description) VALUES
('Mathematics', 'stem', 'Study of numbers, shapes, and patterns'),
('Physics', 'stem', 'Study of matter, energy, and the laws of nature'),
('Chemistry', 'stem', 'Study of substances and their transformations'),
('Biology', 'stem', 'Study of living organisms and life processes'),
('Computer Science', 'stem', 'Study of computation and computer systems'),
('English', 'humanities', 'Study of language, literature, and communication'),
('History', 'humanities', 'Study of past events and human civilization'),
('Psychology', 'humanities', 'Study of human mind and behavior'),
('Philosophy', 'humanities', 'Study of fundamental questions about existence'),
('Economics', 'business', 'Study of production, distribution, and consumption'),
('Business Administration', 'business', 'Study of business management and operations'),
('Accounting', 'business', 'Study of financial reporting and analysis'),
('Visual Arts', 'arts', 'Study of visual expression and creativity'),
('Music', 'arts', 'Study of musical theory and performance'),
('Physical Education', 'health', 'Study of physical fitness and sports'),
('Nursing', 'health', 'Study of healthcare and patient care'),
('Medicine', 'health', 'Study of medical science and healthcare');

-- Sample courses data (you can expand this)
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(20) UNIQUE,
    description TEXT,
    university_name VARCHAR(255),
    subject_id INT,
    duration_years INT,
    tuition_fee DECIMAL(10,2),
    requirements TEXT,
    career_prospects TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    INDEX idx_subject_id (subject_id),
    INDEX idx_course_name (course_name)
);

-- Insert sample courses
INSERT INTO courses (course_name, course_code, description, university_name, subject_id, duration_years) VALUES
('Bachelor of Science in Computer Science', 'BSCS', 'Comprehensive study of computer science principles, programming, algorithms, and software development', 'University of the Philippines', 5, 4),
('Bachelor of Science in Mathematics', 'BSMATH', 'Advanced study of mathematical theories, proofs, and applications', 'University of the Philippines', 1, 4),
('Bachelor of Science in Biology', 'BSBIO', 'Study of life sciences, ecosystems, and biological processes', 'University of the Philippines', 4, 4),
('Bachelor of Arts in Psychology', 'BAPSY', 'Study of human behavior, mental processes, and psychological research', 'University of the Philippines', 8, 4),
('Bachelor of Science in Business Administration', 'BSBA', 'Study of business principles, management, marketing, and entrepreneurship', 'University of the Philippines', 11, 4);

-- Create indexes for better performance
CREATE INDEX idx_users_email_status ON users(email, status);
CREATE INDEX idx_rate_limits_cleanup ON rate_limits(identifier, created_at);
CREATE INDEX idx_assessments_user ON user_assessments(user_id, assessment_completed);
CREATE INDEX idx_recommendations_user_score ON course_recommendations(user_id, match_score DESC);

