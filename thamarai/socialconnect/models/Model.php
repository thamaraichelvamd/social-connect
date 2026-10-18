<?php
/**
 * Base Model Class
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $data = [];
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Find by ID
     */
    public function find(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Find by column
     */
    public function findBy(string $column, $value): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }
    
    /**
     * Get all records
     */
    public function all(string $orderBy = 'id', string $order = 'DESC'): array {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}"
        );
    }
    
    /**
     * Get paginated records
     */
    public function paginate(int $page = 1, int $perPage = ITEMS_PER_PAGE, string $orderBy = 'id', string $order = 'DESC'): array {
        $offset = ($page - 1) * $perPage;
        
        $total = $this->count();
        $items = $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Count all records
     */
    public function count(string $where = '', array $params = []): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }
    
    /**
     * Create new record
     */
    public function create(array $data): int {
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        return $this->db->insert(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }
    
    /**
     * Update record
     */
    public function update(int $id, array $data): int {
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        
        return $this->db->update(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?",
            $values
        );
    }
    
    /**
     * Delete record
     */
    public function delete(int $id): int {
        return $this->db->delete(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Check if exists
     */
    public function exists(int $id): bool {
        return $this->find($id) !== null;
    }
    
    /**
     * Search records
     */
    public function search(string $query, array $columns, int $limit = 20): array {
        $conditions = implode(' OR ', array_map(fn($col) => "{$col} LIKE ?", $columns));
        $params = array_fill(0, count($columns), "%{$query}%");
        $params[] = $limit;
        
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$conditions} LIMIT ?",
            $params
        );
    }
    
    /**
     * Custom query
     */
    public function query(string $sql, array $params = []): array {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Custom query - single row
     */
    public function queryOne(string $sql, array $params = []): ?array {
        return $this->db->fetchOne($sql, $params);
    }
}
