<?php
/**
 * Notification Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Notification extends Model {
    protected $table = 'notifications';
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'icon', 'link', 'data', 'is_read', 'read_at'
    ];
    
    public function getByUser(int $userId, int $limit = 20, bool $unreadOnly = false): array {
        $where = "user_id = ?";
        if ($unreadOnly) $where .= " AND is_read = FALSE";
        
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
    
    public function getUnreadCount(int $userId): int {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        );
        return (int) ($result['count'] ?? 0);
    }
    
    public function markAsRead(int $id): void {
        $this->update($id, ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')]);
    }
    
    public function markAllAsRead(int $userId): void {
        $this->db->query(
            "UPDATE {$this->table} SET is_read = TRUE, read_at = NOW() WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        );
    }
    
    public function createNotification(int $userId, string $type, string $title, string $message = '', string $link = '', string $icon = 'fas fa-bell'): int {
        return $this->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'icon' => $icon
        ]);
    }
    
    public function deleteOld(int $days = 30): int {
        return $this->db->delete(
            "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
    }
    
    public function deleteByUser(int $userId): int {
        return $this->db->delete("DELETE FROM {$this->table} WHERE user_id = ?", [$userId]);
    }
}
