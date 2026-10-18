<?php
/**
 * Post Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Post extends Model {
    protected $table = 'posts';
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'content', 'excerpt',
        'media', 'media_type', 'likes_count', 'comments_count', 'views_count',
        'is_featured', 'is_pinned', 'status'
    ];
    
    /**
     * Get post with author and category
     */
    public function getWithDetails(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT p.*, u.username, u.full_name, u.avatar, u.role as author_role,
                    c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             WHERE p.id = ?",
            [$id]
        );
    }
    
    /**
     * Find by slug with details
     */
    public function findBySlug(string $slug): ?array {
        return $this->db->fetchOne(
            "SELECT p.*, u.username, u.full_name, u.avatar, u.role as author_role,
                    c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ?",
            [$slug]
        );
    }
    
    /**
     * Get published posts with pagination
     */
    public function getPublished(int $page = 1, int $perPage = ITEMS_PER_PAGE, ?int $categoryId = null): array {
        $params = [];
        $where = "p.status = 'published'";
        
        if ($categoryId) {
            $where .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} p WHERE {$where}",
            $params
        );
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT p.*, u.username, u.full_name, u.avatar,
                    c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             WHERE {$where}
             ORDER BY p.is_pinned DESC, p.created_at DESC
             LIMIT ? OFFSET ?",
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
    
    /**
     * Get posts by category slug
     */
    public function getByCategory(string $categorySlug, int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $category = $this->db->fetchOne(
            "SELECT id FROM categories WHERE slug = ?",
            [$categorySlug]
        );
        
        if (!$category) {
            return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'total_pages' => 0];
        }
        
        return $this->getPublished($page, $perPage, $category['id']);
    }
    
    /**
     * Get posts by user
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?",
            [$userId]
        );
        
        $offset = ($page - 1) * $perPage;
        
        $items = $this->db->fetchAll(
            "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN categories c ON p.category_id = c.id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );
        
        return [
            'items' => $items,
            'total' => (int) $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total['count'] / $perPage)
        ];
    }
    
    /**
     * Get featured posts
     */
    public function getFeatured(int $limit = 5): array {
        return $this->db->fetchAll(
            "SELECT p.*, u.username, u.full_name, u.avatar,
                    c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'published' AND p.is_featured = TRUE
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Get trending posts (most viewed in last 7 days)
     */
    public function getTrending(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT p.*, u.username, u.full_name, u.avatar,
                    c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'published' AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY p.views_count DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Create post
     */
    public function createPost(array $data): int {
        $data['slug'] = uniqueSlug($data['title'], $this->table);
        
        if (!empty($data['content']) && empty($data['excerpt'])) {
            $data['excerpt'] = truncate(strip_tags($data['content']), 200);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update post
     */
    public function updatePost(int $id, array $data): int {
        if (isset($data['title'])) {
            $data['slug'] = uniqueSlug($data['title'], $this->table, $id);
        }
        
        if (!empty($data['content']) && empty($data['excerpt'])) {
            $data['excerpt'] = truncate(strip_tags($data['content']), 200);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Increment view count
     */
    public function incrementViews(int $id): void {
        $this->db->query(
            "UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Toggle like
     */
    public function toggleLike(int $postId, int $userId): array {
        $exists = $this->db->fetchOne(
            "SELECT id FROM likes WHERE user_id = ? AND likeable_type = 'post' AND likeable_id = ?",
            [$userId, $postId]
        );
        
        if ($exists) {
            $this->db->delete(
                "DELETE FROM likes WHERE id = ?",
                [$exists['id']]
            );
            $this->db->query(
                "UPDATE {$this->table} SET likes_count = likes_count - 1 WHERE id = ?",
                [$postId]
            );
            return ['liked' => false];
        } else {
            $this->db->insert(
                "INSERT INTO likes (user_id, likeable_type, likeable_id) VALUES (?, 'post', ?)",
                [$userId, $postId]
            );
            $this->db->query(
                "UPDATE {$this->table} SET likes_count = likes_count + 1 WHERE id = ?",
                [$postId]
            );
            return ['liked' => true];
        }
    }
    
    /**
     * Check if user liked post
     */
    public function isLikedBy(int $postId, int $userId): bool {
        $exists = $this->db->fetchOne(
            "SELECT id FROM likes WHERE user_id = ? AND likeable_type = 'post' AND likeable_id = ?",
            [$userId, $postId]
        );
        return (bool) $exists;
    }
    
    /**
     * Search posts
     */
    public function searchPosts(string $query, int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $searchTerm = "%{$query}%";
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)",
            [$searchTerm, $searchTerm]
        );
        
        $offset = ($page - 1) * $perPage;
        
        $items = $this->db->fetchAll(
            "SELECT p.*, u.username, u.full_name, u.avatar,
                    c.name as category_name, c.slug as category_slug, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ?)
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$searchTerm, $searchTerm, $perPage, $offset]
        );
        
        return [
            'items' => $items,
            'total' => (int) $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total['count'] / $perPage)
        ];
    }
    
    /**
     * Admin: Get all posts with filters
     */
    public function getAllFiltered(array $filters = [], int $page = 1, int $perPage = ADMIN_ITEMS_PER_PAGE): array {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $conditions[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['user_id'])) {
            $conditions[] = "p.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} p {$where}",
            $params
        );
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT p.*, u.username, u.full_name,
                    c.name as category_name, c.color as category_color
             FROM {$this->table} p
             JOIN users u ON p.user_id = u.id
             JOIN categories c ON p.category_id = c.id
             {$where}
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
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
