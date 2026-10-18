<?php
/**
 * Message Model
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Message extends Model {
    protected $table = 'messages';
    protected $fillable = [
        'sender_id', 'receiver_id', 'subject', 'content', 'is_read',
        'read_at', 'is_deleted_sender', 'is_deleted_receiver'
    ];
    
    public function getConversations(int $userId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT m.*, 
                    CASE WHEN m.sender_id = ? THEN r.username ELSE s.username END as other_username,
                    CASE WHEN m.sender_id = ? THEN r.full_name ELSE s.full_name END as other_name,
                    CASE WHEN m.sender_id = ? THEN r.avatar ELSE s.avatar END as other_avatar,
                    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as other_id
             FROM {$this->table} m
             JOIN users s ON m.sender_id = s.id
             JOIN users r ON m.receiver_id = r.id
             WHERE (m.sender_id = ? AND m.is_deleted_sender = FALSE)
                OR (m.receiver_id = ? AND m.is_deleted_receiver = FALSE)
             ORDER BY m.created_at DESC LIMIT ?",
            [$userId, $userId, $userId, $userId, $userId, $userId, $limit]
        );
    }
    
    public function getThread(int $userId, int $otherId, int $limit = 50): array {
        $messages = $this->db->fetchAll(
            "SELECT m.*, s.username as sender_username, s.avatar as sender_avatar
             FROM {$this->table} m
             JOIN users s ON m.sender_id = s.id
             WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
             AND ((m.sender_id = ? AND m.is_deleted_sender = FALSE) OR (m.receiver_id = ? AND m.is_deleted_receiver = FALSE))
             ORDER BY m.created_at ASC LIMIT ?",
            [$userId, $otherId, $otherId, $userId, $userId, $userId, $limit]
        );
        
        // Mark as read
        $this->db->query(
            "UPDATE {$this->table} SET is_read = TRUE, read_at = NOW() 
             WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE",
            [$otherId, $userId]
        );
        
        return $messages;
    }
    
    public function send(int $senderId, int $receiverId, string $content, ?string $subject = null): int {
        $messageId = $this->create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'content' => sanitize($content)
        ]);
        
        // Create notification
        $sender = $this->db->fetchOne("SELECT username FROM users WHERE id = ?", [$senderId]);
        createNotification($receiverId, 'message', 'New Message',
            "You received a message from {$sender['username']}", "messages.php?user={$senderId}");
        
        return $messageId;
    }
    
    public function markAsRead(int $id): void {
        $this->update($id, ['is_read' => true, 'read_at' => date('Y-m-d H:i:s')]);
    }
    
    public function deleteForUser(int $id, int $userId): void {
        $msg = $this->find($id);
        if (!$msg) return;
        
        if ($msg['sender_id'] == $userId) {
            $this->update($id, ['is_deleted_sender' => true]);
        } elseif ($msg['receiver_id'] == $userId) {
            $this->update($id, ['is_deleted_receiver' => true]);
        }
    }
    
    public function getUnreadCount(int $userId): int {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} 
             WHERE receiver_id = ? AND is_read = FALSE AND is_deleted_receiver = FALSE",
            [$userId]
        );
        return (int) ($result['count'] ?? 0);
    }
}
