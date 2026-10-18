<?php
/**
 * EducationalContent Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class EducationalContent extends Model {
    protected $table = 'educational_content';
    protected $fillable = [
        'educator_id', 'title', 'slug', 'subject', 'level', 'content_type',
        'content', 'summary', 'thumbnail', 'video_url', 'attachments',
        'duration_minutes', 'tags', 'views_count', 'likes_count', 'enrollments_count',
        'is_featured', 'is_free', 'price', 'status'
    ];
    
    public function getWithEducator(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT e.*, u.username, u.full_name, u.avatar, u.role as educator_role
             FROM {$this->table} e JOIN users u ON e.educator_id = u.id WHERE e.id = ?", [$id]
        );
    }
    
    public function findBySlug(string $slug): ?array {
        return $this->db->fetchOne(
            "SELECT e.*, u.username, u.full_name, u.avatar FROM {$this->table} e
             JOIN users u ON e.educator_id = u.id WHERE e.slug = ?", [$slug]
        );
    }
    
    public function getPublished(int $page = 1, int $perPage = ITEMS_PER_PAGE, array $filters = []): array {
        $conditions = ["e.status = 'published'"];
        $params = [];
        
        if (!empty($filters['subject'])) { $conditions[] = "e.subject = ?"; $params[] = $filters['subject']; }
        if (!empty($filters['level'])) { $conditions[] = "e.level = ?"; $params[] = $filters['level']; }
        if (!empty($filters['content_type'])) { $conditions[] = "e.content_type = ?"; $params[] = $filters['content_type']; }
        if (!empty($filters['free'])) { $conditions[] = "e.is_free = TRUE"; }
        if (!empty($filters['search'])) {
            $conditions[] = "(e.title LIKE ? OR e.content LIKE ?)";
            $params = array_merge($params, ["%{$filters['search']}%", "%{$filters['search']}%"]);
        }
        
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table} e {$where}", $params);
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage; $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT e.*, u.username, u.full_name, u.avatar FROM {$this->table} e
             JOIN users u ON e.educator_id = u.id {$where}
             ORDER BY e.is_featured DESC, e.created_at DESC LIMIT ? OFFSET ?", $params
        );
        
        return ['items' => $items, 'total' => (int) $total['count'], 'page' => $page,
                'per_page' => $perPage, 'total_pages' => ceil($total['count'] / $perPage)];
    }
    
    public function getByEducator(int $educatorId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE educator_id = ? ORDER BY created_at DESC LIMIT ?",
            [$educatorId, $limit]
        );
    }
    
    public function getFeatured(int $limit = 6): array {
        return $this->db->fetchAll(
            "SELECT e.*, u.username, u.full_name, u.avatar FROM {$this->table} e
             JOIN users u ON e.educator_id = u.id
             WHERE e.status = 'published' AND e.is_featured = TRUE
             ORDER BY e.created_at DESC LIMIT ?", [$limit]
        );
    }
    
    public function getSubjects(): array {
        return $this->db->fetchAll(
            "SELECT DISTINCT subject FROM {$this->table} WHERE subject IS NOT NULL ORDER BY subject"
        );
    }
    
    public function createContent(array $data): int {
        $data['slug'] = uniqueSlug($data['title'], $this->table);
        if (isset($data['tags']) && is_array($data['tags'])) $data['tags'] = json_encode($data['tags']);
        if (isset($data['attachments']) && is_array($data['attachments'])) $data['attachments'] = json_encode($data['attachments']);
        return $this->create($data);
    }
    
    public function updateContent(int $id, array $data): int {
        if (isset($data['title'])) $data['slug'] = uniqueSlug($data['title'], $this->table, $id);
        if (isset($data['tags']) && is_array($data['tags'])) $data['tags'] = json_encode($data['tags']);
        if (isset($data['attachments']) && is_array($data['attachments'])) $data['attachments'] = json_encode($data['attachments']);
        return $this->update($id, $data);
    }
    
    public function incrementViews(int $id): void {
        $this->db->query("UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?", [$id]);
    }
    
    public function incrementEnrollments(int $id): void {
        $this->db->query("UPDATE {$this->table} SET enrollments_count = enrollments_count + 1 WHERE id = ?", [$id]);
    }
}
