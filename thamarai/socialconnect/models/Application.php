<?php
/**
 * Application Model (Job Applications)
 * SocialConnect Platform
 */

require_once __DIR__ . '/Model.php';

class Application extends Model {
    protected $table = 'job_applications';
    protected $fillable = [
        'job_id', 'user_id', 'resume', 'cover_letter', 'expected_salary',
        'availability_date', 'status', 'notes', 'reviewed_at'
    ];
    
    public function getWithDetails(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT a.*, j.title as job_title, j.company, j.slug as job_slug,
                    u.username, u.full_name, u.email, u.avatar, u.phone
             FROM {$this->table} a
             JOIN jobs j ON a.job_id = j.id
             JOIN users u ON a.user_id = u.id
             WHERE a.id = ?", [$id]
        );
    }
    
    public function getByJob(int $jobId, int $page = 1, int $perPage = 20): array {
        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table} WHERE job_id = ?", [$jobId]);
        $offset = ($page - 1) * $perPage;
        
        $items = $this->db->fetchAll(
            "SELECT a.*, u.username, u.full_name, u.email, u.avatar
             FROM {$this->table} a JOIN users u ON a.user_id = u.id
             WHERE a.job_id = ? ORDER BY a.created_at DESC LIMIT ? OFFSET ?",
            [$jobId, $perPage, $offset]
        );
        
        return ['items' => $items, 'total' => (int) $total['count'], 'page' => $page,
                'per_page' => $perPage, 'total_pages' => ceil($total['count'] / $perPage)];
    }
    
    public function getByUser(int $userId, int $page = 1, int $perPage = 10): array {
        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?", [$userId]);
        $offset = ($page - 1) * $perPage;
        
        $items = $this->db->fetchAll(
            "SELECT a.*, j.title as job_title, j.company, j.slug as job_slug, j.location
             FROM {$this->table} a JOIN jobs j ON a.job_id = j.id
             WHERE a.user_id = ? ORDER BY a.created_at DESC LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );
        
        return ['items' => $items, 'total' => (int) $total['count'], 'page' => $page,
                'per_page' => $perPage, 'total_pages' => ceil($total['count'] / $perPage)];
    }
    
    public function apply(int $jobId, int $userId, array $data): array {
        // Check if already applied
        $exists = $this->db->fetchOne(
            "SELECT id FROM {$this->table} WHERE job_id = ? AND user_id = ?",
            [$jobId, $userId]
        );
        if ($exists) return ['success' => false, 'error' => 'You have already applied to this job.'];
        
        // Check if job is open
        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND status = 'open'", [$jobId]);
        if (!$job) return ['success' => false, 'error' => 'This job is no longer accepting applications.'];
        
        $applicationId = $this->create([
            'job_id' => $jobId,
            'user_id' => $userId,
            'resume' => $data['resume'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? '',
            'expected_salary' => $data['expected_salary'] ?? null,
            'availability_date' => $data['availability_date'] ?? null,
            'status' => 'pending'
        ]);
        
        // Update application count
        $this->db->query("UPDATE jobs SET applications_count = applications_count + 1 WHERE id = ?", [$jobId]);
        
        // Notify employer
        createNotification($job['employer_id'], 'application', 'New Job Application',
            "Someone applied to \"{$job['title']}\"", "admin/applications.php?job_id={$jobId}");
        
        return ['success' => true, 'application_id' => $applicationId];
    }
    
    public function updateStatus(int $id, string $status, ?string $notes = null): bool {
        $data = ['status' => $status, 'reviewed_at' => date('Y-m-d H:i:s')];
        if ($notes !== null) $data['notes'] = $notes;
        
        $this->update($id, $data);
        
        // Notify applicant
        $app = $this->getWithDetails($id);
        if ($app) {
            $messages = [
                'reviewed' => 'Your application has been reviewed',
                'shortlisted' => 'Congratulations! You have been shortlisted',
                'interview' => 'You have been invited for an interview',
                'rejected' => 'Your application status has been updated',
                'hired' => 'Congratulations! You have been hired'
            ];
            createNotification($app['user_id'], 'application_update', $messages[$status] ?? 'Application Updated',
                "Status update for \"{$app['job_title']}\"", "profile.php?tab=applications");
        }
        
        return true;
    }
    
    public function getStatusCounts(int $jobId): array {
        $results = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM {$this->table} WHERE job_id = ? GROUP BY status",
            [$jobId]
        );
        $counts = ['pending' => 0, 'reviewed' => 0, 'shortlisted' => 0, 'interview' => 0, 'rejected' => 0, 'hired' => 0];
        foreach ($results as $r) $counts[$r['status']] = (int) $r['count'];
        return $counts;
    }
}
