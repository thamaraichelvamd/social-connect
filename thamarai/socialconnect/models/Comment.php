<?php
/**
 * Comment Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Comment extends Model {
    protected $table = 'comments';
    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'content', 'likes_count', 'status'
    ];
    
    /**
     * Get comments for a post
     */
    public function getByPost(int $postId, int $page = 1, int $perPage = 20): array {
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE post_id = ? AND parent_id IS NULL AND status = 'approved'",
            [$postId]
        );
        
        $offset = ($page - 1) * $perPage;
        
        $items = $this->db->fetchAll(
            "SELECT c.*, u.username, u.full_name, u.avatar, u.role as author_role
             FROM {$this->table} c
             JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ? AND c.parent_id IS NULL AND c.status = 'approved'
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?",
            [$postId, $perPage, $offset]
        );
        
        // Get replies for each comment
        foreach ($items as &$comment) {
            $comment['replies'] = $this->getReplies($comment['id']);
        }
        
        return [
            'items' => $items,
            'total' => (int) $total['count'],
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total['count'] / $perPage)
        ];
    }
    
    /**
     * Get replies for a comment
     */
    public function getReplies(int $commentId): array {
        return $this->db->fetchAll(
            "SELECT c.*, u.username, u.full_name, u.avatar, u.role as author_role
             FROM {$this->table} c
             JOIN users u ON c.user_id = u.id
             WHERE c.parent_id = ? AND c.status = 'approved'
             ORDER BY c.created_at ASC",
            [$commentId]
        );
    }
    
    /**
     * Add comment
     */
    public function addComment(int $postId, int $userId, string $content, ?int $parentId = null): int {
        $commentId = $this->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => sanitize($content),
            'status' => 'approved'
        ]);
        
        // Update post comment count
        $this->db->query(
            "UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?",
            [$postId]
        );
        
        // Create notification for post author
        $post = $this->db->fetchOne("SELECT user_id, title FROM posts WHERE id = ?", [$postId]);
        if ($post && $post['user_id'] !== $userId) {
            createNotification(
                $post['user_id'],
                'comment',
                'New comment on your post',
                'Someone commented on "' . truncate($post['title'], 50) . '"',
                "post.php?id={$postId}#comment-{$commentId}"
            );
        }
        
        return $commentId;
    }
    
    /**
     * Delete comment and update count
     */
    public function deleteComment(int $id): bool {
        $comment = $this->find($id);
        if (!$comment) return false;
        
        // Count this comment and its replies
        $replyCount = $this->count("parent_id = ?", [$id]);
        $totalCount = 1 + $replyCount;
        
        // Delete replies first
        $this->db->delete("DELETE FROM {$this->table} WHERE parent_id = ?", [$id]);
        
        // Delete the comment
        $this->delete($id);
        
        // Update post comment count
        $this->db->query(
            "UPDATE posts SET comments_count = comments_count - ? WHERE id = ?",
            [$totalCount, $comment['post_id']]
        );
        
        return true;
    }
    
    /**
     * Toggle like
     */
    public function toggleLike(int $commentId, int $userId): array {
        $exists = $this->db->fetchOne(
            "SELECT id FROM likes WHERE user_id = ? AND likeable_type = 'comment' AND likeable_id = ?",
            [$userId, $commentId]
        );
        
        if ($exists) {
            $this->db->delete("DELETE FROM likes WHERE id = ?", [$exists['id']]);
            $this->db->query(
                "UPDATE {$this->table} SET likes_count = likes_count - 1 WHERE id = ?",
                [$commentId]
            );
            return ['liked' => false];
        } else {
            $this->db->insert(
                "INSERT INTO likes (user_id, likeable_type, likeable_id) VALUES (?, 'comment', ?)",
                [$userId, $commentId]
            );
            $this->db->query(
                "UPDATE {$this->table} SET likes_count = likes_count + 1 WHERE id = ?",
                [$commentId]
            );
            return ['liked' => true];
        }
    }
    
    /**
     * Get user's comments
     */
    public function getByUser(int $userId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT c.*, p.title as post_title, p.slug as post_slug
             FROM {$this->table} c
             JOIN posts p ON c.post_id = p.id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }
    
    /**
     * Admin: Get all comments with filters
     */
    public function getAllFiltered(array $filters = [], int $page = 1, int $perPage = ADMIN_ITEMS_PER_PAGE): array {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $conditions[] = "c.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['post_id'])) {
            $conditions[] = "c.post_id = ?";
            $params[] = $filters['post_id'];
        }
        
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} c {$where}",
            $params
        );
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT c.*, u.username, u.full_name, p.title as post_title
             FROM {$this->table} c
             JOIN users u ON c.user_id = u.id
             JOIN posts p ON c.post_id = p.id
             {$where}
             ORDER BY c.created_at DESC
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
