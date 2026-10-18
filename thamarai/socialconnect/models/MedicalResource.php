<?php
/**
 * MedicalResource Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class MedicalResource extends Model {
    protected $table = 'medical_resources';
    protected $fillable = [
        'author_id', 'title', 'slug', 'content_type', 'specialty', 'content',
        'summary', 'thumbnail', 'attachments', 'tags', 'views_count', 'likes_count',
        'is_verified', 'verified_by', 'verified_at', 'status'
    ];
    
    public function getWithAuthor(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT m.*, u.username, u.full_name, u.avatar, u.role as author_role
             FROM {$this->table} m JOIN users u ON m.author_id = u.id WHERE m.id = ?", [$id]
        );
    }
    
    public function findBySlug(string $slug): ?array {
        return $this->db->fetchOne(
            "SELECT m.*, u.username, u.full_name, u.avatar FROM {$this->table} m
             JOIN users u ON m.author_id = u.id WHERE m.slug = ?", [$slug]
        );
    }
    
    public function getPublished(int $page = 1, int $perPage = ITEMS_PER_PAGE, array $filters = []): array {
        $conditions = ["m.status = 'published'"];
        $params = [];
        
        if (!empty($filters['specialty'])) { $conditions[] = "m.specialty = ?"; $params[] = $filters['specialty']; }
        if (!empty($filters['content_type'])) { $conditions[] = "m.content_type = ?"; $params[] = $filters['content_type']; }
        if (!empty($filters['verified'])) { $conditions[] = "m.is_verified = TRUE"; }
        if (!empty($filters['search'])) {
            $conditions[] = "(m.title LIKE ? OR m.content LIKE ?)";
            $params = array_merge($params, ["%{$filters['search']}%", "%{$filters['search']}%"]);
        }
        
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table} m {$where}", $params);
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage; $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT m.*, u.username, u.full_name, u.avatar FROM {$this->table} m
             JOIN users u ON m.author_id = u.id {$where}
             ORDER BY m.is_verified DESC, m.created_at DESC LIMIT ? OFFSET ?", $params
        );
        
        return ['items' => $items, 'total' => (int) $total['count'], 'page' => $page,
                'per_page' => $perPage, 'total_pages' => ceil($total['count'] / $perPage)];
    }
    
    public function getByAuthor(int $authorId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE author_id = ? ORDER BY created_at DESC LIMIT ?",
            [$authorId, $limit]
        );
    }
    
    public function getSpecialties(): array {
        return $this->db->fetchAll(
            "SELECT DISTINCT specialty FROM {$this->table} WHERE specialty IS NOT NULL ORDER BY specialty"
        );
    }
    
    public function createResource(array $data): int {
        $data['slug'] = uniqueSlug($data['title'], $this->table);
        if (isset($data['tags']) && is_array($data['tags'])) $data['tags'] = json_encode($data['tags']);
        if (isset($data['attachments']) && is_array($data['attachments'])) $data['attachments'] = json_encode($data['attachments']);
        return $this->create($data);
    }
    
    public function updateResource(int $id, array $data): int {
        if (isset($data['title'])) $data['slug'] = uniqueSlug($data['title'], $this->table, $id);
        if (isset($data['tags']) && is_array($data['tags'])) $data['tags'] = json_encode($data['tags']);
        if (isset($data['attachments']) && is_array($data['attachments'])) $data['attachments'] = json_encode($data['attachments']);
        return $this->update($id, $data);
    }
    
    public function verify(int $id, int $verifiedBy): bool {
        return $this->update($id, [
            'is_verified' => true,
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s')
        ]) > 0;
    }
    
    public function incrementViews(int $id): void {
        $this->db->query("UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?", [$id]);
    }
}
