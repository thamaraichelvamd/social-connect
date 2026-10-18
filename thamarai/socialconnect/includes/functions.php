<?php
/**
 * Helper Functions
 * SocialConnect Platform
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input
 */
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize array recursively
 */
function sanitizeArray(array $data): array {
    return array_map(function($item) {
        return is_array($item) ? sanitizeArray($item) : sanitize($item);
    }, $data);
}

/**
 * Generate URL-friendly slug
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Generate unique slug
 */
function uniqueSlug(string $text, string $table, int $excludeId = 0): string {
    $slug = slugify($text);
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM $table WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $exists = db()->fetchOne($sql, $params);
        
        if (!$exists) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter++;
    }
    
    return $slug;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    static $user = null;
    
    if ($user === null) {
        $user = db()->fetchOne(
            "SELECT * FROM users WHERE id = ? AND status = 'active'",
            [$_SESSION['user_id']]
        );
    }
    
    return $user;
}

/**
 * Check if current user has specific role
 */
function hasRole(string $role): bool {
    $user = currentUser();
    return $user && $user['role'] === $role;
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return hasRole(ROLE_ADMIN);
}

/**
 * Require user to be logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('Please login to continue.', MSG_WARNING);
        redirect('login.php');
    }
}

/**
 * Require admin access
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        setFlash('Access denied. Admin privileges required.', MSG_ERROR);
        redirect(BASE_URL);
    }
}

/**
 * Require specific role
 */
function requireRole(string $role): void {
    requireLogin();
    if (!hasRole($role) && !isAdmin()) {
        setFlash('You do not have permission to access this page.', MSG_ERROR);
        redirect(BASE_URL);
    }
}

/**
 * Set flash message
 */
function setFlash(string $message, string $type = MSG_INFO): void {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Redirect to URL
 */
function redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    echo "<script>window.location.href='$url';</script>";
    exit;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * CSRF token input field
 */
function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Format date for display
 */
function formatDate(string $date, string $format = 'M d, Y'): string {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime(string $datetime, string $format = 'M d, Y h:i A'): string {
    return date($format, strtotime($datetime));
}

/**
 * Time ago format
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 150, string $suffix = '...'): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Format number (1000 -> 1K)
 */
function formatNumber(int $num): string {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return (string) $num;
}

/**
 * Format currency
 */
function formatCurrency(float $amount, string $currency = 'USD'): string {
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹'];
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($amount, 0);
}

/**
 * Get avatar URL
 */
function getAvatar(?string $avatar): string {
    if ($avatar && file_exists(AVATARS_PATH . '/' . $avatar)) {
        return UPLOADS_URL . '/avatars/' . $avatar;
    }
    return ASSETS_URL . '/images/default-avatar.png';
}

/**
 * Upload file
 */
function uploadFile(array $file, string $destination, array $allowedTypes = []): array {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed. Error code: ' . $file['error']];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File is too large.'];
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Create directory if not exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath];
    }
    
    return ['success' => false, 'error' => 'Failed to save file.'];
}

/**
 * Delete file
 */
function deleteFile(string $filepath): bool {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get all categories
 */
function getCategories(): array {
    static $categories = null;
    
    if ($categories === null) {
        $categories = db()->fetchAll(
            "SELECT * FROM categories WHERE is_active = TRUE ORDER BY sort_order ASC"
        );
    }
    
    return $categories;
}

/**
 * Get category by slug
 */
function getCategoryBySlug(string $slug): ?array {
    return db()->fetchOne(
        "SELECT * FROM categories WHERE slug = ? AND is_active = TRUE",
        [$slug]
    );
}

/**
 * Get unread notifications count
 */
function getUnreadNotificationsCount(): int {
    if (!isLoggedIn()) return 0;
    
    $result = db()->fetchOne(
        "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE",
        [$_SESSION['user_id']]
    );
    
    return (int) ($result['count'] ?? 0);
}

/**
 * Get unread messages count
 */
function getUnreadMessagesCount(): int {
    if (!isLoggedIn()) return 0;
    
    $result = db()->fetchOne(
        "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = FALSE AND is_deleted_receiver = FALSE",
        [$_SESSION['user_id']]
    );
    
    return (int) ($result['count'] ?? 0);
}

/**
 * Create notification
 */
function createNotification(int $userId, string $type, string $title, string $message = '', string $link = ''): int {
    return db()->insert(
        "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)",
        [$userId, $type, $title, $message, $link]
    );
}

/**
 * JSON response
 */
function jsonResponse(bool $success, string $message = '', array $data = []): void {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isValidPassword(string $password): bool {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

/**
 * Debug function
 */
function dd($data): void {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    exit;
}
