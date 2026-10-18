<?php
/**
 * Job Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Job extends Model {
    protected $table = 'jobs';
    protected $fillable = [
        'employer_id', 'title', 'slug', 'company', 'company_logo', 'location',
        'job_type', 'experience_level', 'salary_min', 'salary_max', 'salary_currency',
        'description', 'requirements', 'benefits', 'skills_required',
        'applications_count', 'views_count', 'is_featured', 'status', 'expires_at'
    ];
    
    public function getWithDetails(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT j.*, u.username, u.full_name, u.avatar FROM {$this->table} j
             JOIN users u ON j.employer_id = u.id WHERE j.id = ?", [$id]
        );
    }
    
    public function findBySlug(string $slug): ?array {
        return $this->db->fetchOne(
            "SELECT j.*, u.username, u.full_name, u.avatar FROM {$this->table} j
             JOIN users u ON j.employer_id = u.id WHERE j.slug = ?", [$slug]
        );
    }
    
    public function getOpen(int $page = 1, int $perPage = ITEMS_PER_PAGE, array $filters = []): array {
        $conditions = ["j.status = 'open'", "(j.expires_at IS NULL OR j.expires_at >= CURDATE())"];
        $params = [];
        
        if (!empty($filters['job_type'])) { $conditions[] = "j.job_type = ?"; $params[] = $filters['job_type']; }
        if (!empty($filters['location'])) { $conditions[] = "j.location LIKE ?"; $params[] = "%{$filters['location']}%"; }
        if (!empty($filters['search'])) {
            $conditions[] = "(j.title LIKE ? OR j.company LIKE ?)";
            $params = array_merge($params, ["%{$filters['search']}%", "%{$filters['search']}%"]);
        }
        
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table} j {$where}", $params);
        
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage; $params[] = $offset;
        
        $items = $this->db->fetchAll(
            "SELECT j.*, u.username, u.full_name FROM {$this->table} j
             JOIN users u ON j.employer_id = u.id {$where}
             ORDER BY j.is_featured DESC, j.created_at DESC LIMIT ? OFFSET ?", $params
        );
        
        return ['items' => $items, 'total' => (int) $total['count'], 'page' => $page, 
                'per_page' => $perPage, 'total_pages' => ceil($total['count'] / $perPage)];
    }
    
    public function getByEmployer(int $employerId, int $page = 1, int $perPage = ITEMS_PER_PAGE): array {
        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table} WHERE employer_id = ?", [$employerId]);
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll("SELECT * FROM {$this->table} WHERE employer_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$employerId, $perPage, $offset]);
        return ['items' => $items, 'total' => (int) $total['count'], 'page' => $page,
                'per_page' => $perPage, 'total_pages' => ceil($total['count'] / $perPage)];
    }
    
    public function getFeatured(int $limit = 5): array {
        return $this->db->fetchAll(
            "SELECT j.*, u.username, u.full_name FROM {$this->table} j JOIN users u ON j.employer_id = u.id
             WHERE j.status = 'open' AND j.is_featured = TRUE AND (j.expires_at IS NULL OR j.expires_at >= CURDATE())
             ORDER BY j.created_at DESC LIMIT ?", [$limit]
        );
    }
    
    public function createJob(array $data): int {
        $data['slug'] = uniqueSlug($data['title'] . '-' . $data['company'], $this->table);
        if (isset($data['skills_required']) && is_array($data['skills_required'])) {
            $data['skills_required'] = json_encode($data['skills_required']);
        }
        return $this->create($data);
    }
    
    public function updateJob(int $id, array $data): int {
        if (isset($data['title']) || isset($data['company'])) {
            $job = $this->find($id);
            $data['slug'] = uniqueSlug(($data['title'] ?? $job['title']) . '-' . ($data['company'] ?? $job['company']), $this->table, $id);
        }
        if (isset($data['skills_required']) && is_array($data['skills_required'])) {
            $data['skills_required'] = json_encode($data['skills_required']);
        }
        return $this->update($id, $data);
    }
    
    public function incrementViews(int $id): void {
        $this->db->query("UPDATE {$this->table} SET views_count = views_count + 1 WHERE id = ?", [$id]);
    }
    
    public function hasApplied(int $jobId, int $userId): bool {
        return (bool) $this->db->fetchOne("SELECT id FROM job_applications WHERE job_id = ? AND user_id = ?", [$jobId, $userId]);
    }
}
