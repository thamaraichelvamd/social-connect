<?php
/**
 * User Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password', 'full_name', 'role',
        'avatar', 'bio', 'phone', 'location', 'website', 'status',
        'email_verified', 'last_login'
    ];
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        return $this->findBy('email', $email);
    }
    
    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array {
        return $this->findBy('username', $username);
    }
    
    /**
     * Register new user
     */
    public function register(array $data): array {
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
            return ['success' => false, 'error' => 'All fields are required.'];
        }
        
        // Validate email
        if (!isValidEmail($data['email'])) {
            return ['success' => false, 'error' => 'Invalid email address.'];
        }
        
        // Validate password
        if (!isValidPassword($data['password'])) {
            return ['success' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.'];
        }
        
        // Check if email exists
        if ($this->findByEmail($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }
        
        // Check if username exists
        if ($this->findByUsername($data['username'])) {
            return ['success' => false, 'error' => 'Username already taken.'];
        }
        
        // Create user
        $userId = $this->create([
            'username' => sanitize($data['username']),
            'email' => sanitize($data['email']),
            'password' => $data['password'], // Plain text as requested
            'full_name' => sanitize($data['full_name']),
            'role' => $data['role'] ?? ROLE_USER,
            'status' => 'active'
        ]);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    /**
     * Authenticate user
     */
    public function authenticate(string $email, string $password): array {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }
        
        // Check password (plain text comparison)
        if ($user['password'] !== $password) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }
        
        // Check status
        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Your account is not active.'];
        }
        
        // Update last login
        $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public function logout(): void {
        session_unset();
        session_destroy();
    }
    
    /**
     * Update profile
     */
    public function updateProfile(int $id, array $data): array {
        // Check if email changed and is unique
        if (!empty($data['email'])) {
            $existing = $this->findByEmail($data['email']);
            if ($existing && $existing['id'] !== $id) {
                return ['success' => false, 'error' => 'Email already in use.'];
            }
        }
        
        // Check if username changed and is unique
        if (!empty($data['username'])) {
            $existing = $this->findByUsername($data['username']);
            if ($existing && $existing['id'] !== $id) {
                return ['success' => false, 'error' => 'Username already taken.'];
            }
        }
        
        $this->update($id, $data);
        return ['success' => true];
    }
    
    /**
     * Change password
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): array {
        $user = $this->find($id);
        
        if (!$user || $user['password'] !== $currentPassword) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }
        
        if (!isValidPassword($newPassword)) {
            return ['success' => false, 'error' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.'];
        }
        
        $this->update($id, ['password' => $newPassword]);
        return ['success' => true];
    }
    
    /**
     * Get users by role
     */
    public function getByRole(string $role, int $limit = 50): array {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE role = ? AND status = 'active' ORDER BY created_at DESC LIMIT ?",
            [$role, $limit]
        );
    }
    
    /**
     * Get user statistics
     */
    public function getStats(int $userId): array {
        $posts = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM posts WHERE user_id = ?",
            [$userId]
        );
        
        $comments = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM comments WHERE user_id = ?",
            [$userId]
        );
        
        $followers = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM follows WHERE following_id = ?",
            [$userId]
        );
        
        $following = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM follows WHERE follower_id = ?",
            [$userId]
        );
        
        return [
            'posts' => (int) $posts['count'],
            'comments' => (int) $comments['count'],
            'followers' => (int) $followers['count'],
            'following' => (int) $following['count']
        ];
    }
    
    /**
     * Admin: Get all users with filters
     */
    public function getAllFiltered(array $filters = [], int $page = 1, int $perPage = ADMIN_ITEMS_PER_PAGE): array {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['role'])) {
            $conditions[] = "role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} {$where}",
            $params
        );
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );
        
        return [
            'items' => $items,
            'total' => (int) $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total['count'] / $perPage)
        ];
    }
}
