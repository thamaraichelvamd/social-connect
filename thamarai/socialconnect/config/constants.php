<?php
/**
 * Application Constants
 * SocialConnect Platform
 */

// App identifier - must be defined before including other files
define('SOCIALCONNECT', true);

// Application Info
define('APP_NAME', 'SocialConnect');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'All-in-one Social Media Platform');

// URL Configuration (update these based on your setup)
define('BASE_URL', 'http://localhost/socialconnect');
define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODELS_PATH', ROOT_PATH . '/models');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Upload Directories
define('AVATARS_PATH', UPLOADS_PATH . '/avatars');
define('POSTS_PATH', UPLOADS_PATH . '/posts');
define('RESUMES_PATH', UPLOADS_PATH . '/resumes');
define('DOCUMENTS_PATH', UPLOADS_PATH . '/documents');

// User Roles
define('ROLE_USER', 'user');
define('ROLE_EMPLOYER', 'employer');
define('ROLE_MEDICAL', 'medical');
define('ROLE_EDUCATOR', 'educator');
define('ROLE_ADMIN', 'admin');

// User Roles Array (for validation)
define('USER_ROLES', [
    ROLE_USER => 'Regular User',
    ROLE_EMPLOYER => 'Employer',
    ROLE_MEDICAL => 'Medical Professional',
    ROLE_EDUCATOR => 'Educator',
    ROLE_ADMIN => 'Administrator'
]);

// Content Status
define('STATUS_PUBLISHED', 'published');
define('STATUS_DRAFT', 'draft');
define('STATUS_HIDDEN', 'hidden');
define('STATUS_PENDING', 'pending');

// Job Types
define('JOB_TYPES', [
    'full-time' => 'Full Time',
    'part-time' => 'Part Time',
    'contract' => 'Contract',
    'remote' => 'Remote',
    'internship' => 'Internship'
]);

// Experience Levels
define('EXPERIENCE_LEVELS', [
    'entry' => 'Entry Level',
    'mid' => 'Mid Level',
    'senior' => 'Senior Level',
    'executive' => 'Executive'
]);

// Content Types
define('EDUCATION_TYPES', [
    'course' => 'Course',
    'tutorial' => 'Tutorial',
    'resource' => 'Resource',
    'quiz' => 'Quiz',
    'webinar' => 'Webinar'
]);

define('MEDICAL_TYPES', [
    'article' => 'Article',
    'video' => 'Video',
    'guide' => 'Guide',
    'faq' => 'FAQ',
    'infographic' => 'Infographic'
]);

// Difficulty Levels
define('DIFFICULTY_LEVELS', [
    'beginner' => 'Beginner',
    'intermediate' => 'Intermediate',
    'advanced' => 'Advanced',
    'all' => 'All Levels'
]);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// File Upload Limits
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_RESUME_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed File Types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg']);

// Session Configuration
define('SESSION_LIFETIME', 7200); // 2 hours
define('REMEMBER_ME_LIFETIME', 30 * 24 * 60 * 60); // 30 days

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 6);

// Category Colors
define('CATEGORY_COLORS', [
    'entertainment' => '#ef4444',
    'education' => '#3b82f6',
    'politics' => '#8b5cf6',
    'cinema' => '#f59e0b',
    'sports' => '#10b981',
    'medical' => '#ec4899',
    'employment' => '#6366f1'
]);

// Application Status Messages
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// Time Zone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
