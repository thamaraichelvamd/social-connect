-- ============================================
-- SocialConnect Database Schema
-- Version: 1.0
-- Created: 2026-01-18
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS socialconnect
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE socialconnect;

-- ============================================
-- TABLE 1: users
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'employer', 'medical', 'educator', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    bio TEXT,
    phone VARCHAR(20),
    location VARCHAR(100),
    website VARCHAR(255),
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 2: categories
-- ============================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-folder',
    description TEXT,
    color VARCHAR(7) DEFAULT '#6366f1',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 3: posts
-- ============================================
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    media VARCHAR(255),
    media_type ENUM('image', 'video', 'document', 'none') DEFAULT 'none',
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_pinned BOOLEAN DEFAULT FALSE,
    status ENUM('published', 'draft', 'hidden', 'pending') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 4: comments
-- ============================================
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    likes_count INT DEFAULT 0,
    status ENUM('approved', 'pending', 'spam', 'hidden') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE SET NULL,
    INDEX idx_post (post_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 5: jobs
-- ============================================
CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    company VARCHAR(100) NOT NULL,
    company_logo VARCHAR(255),
    location VARCHAR(100) NOT NULL,
    job_type ENUM('full-time', 'part-time', 'contract', 'remote', 'internship') DEFAULT 'full-time',
    experience_level ENUM('entry', 'mid', 'senior', 'executive') DEFAULT 'entry',
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    salary_currency VARCHAR(3) DEFAULT 'USD',
    description TEXT NOT NULL,
    requirements TEXT,
    benefits TEXT,
    skills_required JSON,
    applications_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('open', 'closed', 'filled', 'draft') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATE,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_employer (employer_id),
    INDEX idx_type (job_type),
    INDEX idx_status (status),
    INDEX idx_location (location),
    INDEX idx_created (created_at),
    FULLTEXT idx_search (title, description, requirements)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 6: job_applications
-- ============================================
CREATE TABLE job_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    resume VARCHAR(255),
    cover_letter TEXT,
    expected_salary DECIMAL(10,2),
    availability_date DATE,
    status ENUM('pending', 'reviewed', 'shortlisted', 'interview', 'rejected', 'hired') DEFAULT 'pending',
    notes TEXT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, user_id),
    INDEX idx_job (job_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 7: medical_resources
-- ============================================
CREATE TABLE medical_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content_type ENUM('article', 'video', 'guide', 'faq', 'infographic') DEFAULT 'article',
    specialty VARCHAR(100),
    content TEXT NOT NULL,
    summary TEXT,
    thumbnail VARCHAR(255),
    attachments JSON,
    tags JSON,
    views_count INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    status ENUM('published', 'draft', 'under_review', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_author (author_id),
    INDEX idx_specialty (specialty),
    INDEX idx_type (content_type),
    INDEX idx_verified (is_verified),
    FULLTEXT idx_search (title, content, summary)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 8: educational_content
-- ============================================
CREATE TABLE educational_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    educator_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    subject VARCHAR(100) NOT NULL,
    level ENUM('beginner', 'intermediate', 'advanced', 'all') DEFAULT 'all',
    content_type ENUM('course', 'tutorial', 'resource', 'quiz', 'webinar') DEFAULT 'tutorial',
    content TEXT NOT NULL,
    summary TEXT,
    thumbnail VARCHAR(255),
    video_url VARCHAR(500),
    attachments JSON,
    duration_minutes INT DEFAULT 0,
    tags JSON,
    views_count INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    enrollments_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_free BOOLEAN DEFAULT TRUE,
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (educator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_educator (educator_id),
    INDEX idx_subject (subject),
    INDEX idx_level (level),
    INDEX idx_type (content_type),
    INDEX idx_featured (is_featured),
    FULLTEXT idx_search (title, content, summary)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 9: messages
-- ============================================
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    is_deleted_sender BOOLEAN DEFAULT FALSE,
    is_deleted_receiver BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- TABLE 10: notifications
-- ============================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-bell',
    link VARCHAR(255),
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- ADDITIONAL TABLES: Likes & Follows
-- ============================================
CREATE TABLE likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    likeable_type ENUM('post', 'comment', 'medical', 'education') NOT NULL,
    likeable_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, likeable_type, likeable_id),
    INDEX idx_likeable (likeable_type, likeable_id)
) ENGINE=InnoDB;

CREATE TABLE follows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    INDEX idx_follower (follower_id),
    INDEX idx_following (following_id)
) ENGINE=InnoDB;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default Admin User (Password: admin123)
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@socialconnect.com', 'admin123', 'System Administrator', 'admin', 'active'),
('john_employer', 'john@techcorp.com', 'password123', 'John Smith', 'employer', 'active'),
('dr_sarah', 'sarah@medical.com', 'password123', 'Dr. Sarah Johnson', 'medical', 'active'),
('prof_mike', 'mike@university.edu', 'password123', 'Prof. Michael Brown', 'educator', 'active'),
('jane_user', 'jane@email.com', 'password123', 'Jane Doe', 'user', 'active');

-- Default Categories
INSERT INTO categories (name, slug, icon, description, color, sort_order) VALUES
('Entertainment', 'entertainment', 'fas fa-film', 'Movies, music, games, and fun content', '#ef4444', 1),
('Education', 'education', 'fas fa-graduation-cap', 'Learning resources and academic content', '#3b82f6', 2),
('Politics', 'politics', 'fas fa-landmark', 'Political news and discussions', '#8b5cf6', 3),
('Cinema', 'cinema', 'fas fa-video', 'Film reviews, trailers, and cinema news', '#f59e0b', 4),
('Sports', 'sports', 'fas fa-futbol', 'Sports news, scores, and discussions', '#10b981', 5),
('Medical', 'medical', 'fas fa-heartbeat', 'Health resources and medical information', '#ec4899', 6),
('Employment', 'employment', 'fas fa-briefcase', 'Job listings and career opportunities', '#6366f1', 7);

-- Sample Posts
INSERT INTO posts (user_id, category_id, title, slug, content, excerpt, status) VALUES
(5, 1, 'Top 10 Movies of 2026', 'top-10-movies-2026', 'Here are the best movies released this year...', 'A curated list of must-watch films', 'published'),
(4, 2, 'Introduction to Machine Learning', 'intro-machine-learning', 'Machine learning is a subset of artificial intelligence...', 'Learn the basics of ML', 'published'),
(5, 5, 'World Cup 2026 Preview', 'world-cup-2026-preview', 'The FIFA World Cup 2026 is approaching...', 'Everything you need to know about the upcoming World Cup', 'published');

-- Sample Jobs
INSERT INTO jobs (employer_id, title, slug, company, location, job_type, salary_min, salary_max, description, requirements, status) VALUES
(2, 'Senior Software Engineer', 'senior-software-engineer-techcorp', 'TechCorp Inc', 'San Francisco, CA', 'full-time', 120000, 180000, 'We are looking for an experienced software engineer...', 'Bachelor''s degree in CS, 5+ years experience', 'open'),
(2, 'Marketing Manager', 'marketing-manager-techcorp', 'TechCorp Inc', 'Remote', 'remote', 80000, 120000, 'Lead our marketing team and drive growth...', '3+ years marketing experience', 'open');

-- Sample Medical Resource
INSERT INTO medical_resources (author_id, title, slug, content_type, specialty, content, summary, is_verified) VALUES
(3, 'Understanding Heart Health', 'understanding-heart-health', 'article', 'Cardiology', 'Heart disease is the leading cause of death worldwide...', 'A comprehensive guide to maintaining a healthy heart', TRUE);

-- Sample Educational Content
INSERT INTO educational_content (educator_id, title, slug, subject, level, content_type, content, summary, duration_minutes) VALUES
(4, 'Python for Beginners', 'python-beginners', 'Computer Science', 'beginner', 'course', 'Welcome to Python programming! In this course...', 'Learn Python from scratch', 120);

-- ============================================
-- END OF SCHEMA
-- ============================================
