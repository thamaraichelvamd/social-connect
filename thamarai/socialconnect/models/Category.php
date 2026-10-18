<?php
/**
 * Category Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Category extends Model {
    protected $table = 'categories';
    protected $fillable = [
        'name', 'slug', 'icon', 'description', 'color', 'sort_order', 'is_active'
    ];
    
    /**
     * Get all active categories
     */
    public function getActive(): array {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY sort_order ASC"
        );
    }
    
    /**
     * Find by slug
     */
    public function findBySlug(string $slug): ?array {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Get category with post count
     */
    public function getWithPostCount(): array {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(p.id) as posts_count 
             FROM {$this->table} c 
             LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
             WHERE c.is_active = TRUE 
             GROUP BY c.id 
             ORDER BY c.sort_order ASC"
        );
    }
    
    /**
     * Create category with unique slug
     */
    public function createCategory(array $data): int {
        $data['slug'] = uniqueSlug($data['name'], $this->table);
        return $this->create($data);
    }
    
    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): int {
        if (isset($data['name'])) {
            $data['slug'] = uniqueSlug($data['name'], $this->table, $id);
        }
        return $this->update($id, $data);
    }
    
    /**
     * Toggle active status
     */
    public function toggleActive(int $id): bool {
        $category = $this->find($id);
        if ($category) {
            $this->update($id, ['is_active' => !$category['is_active']]);
            return true;
        }
        return false;
    }
    
    /**
     * Reorder categories
     */
    public function reorder(array $order): void {
        foreach ($order as $index => $id) {
            $this->update($id, ['sort_order' => $index]);
        }
    }
}
