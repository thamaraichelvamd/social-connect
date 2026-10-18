<?php
/**
 * Admin - Application Management
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/../models/Job.php';

requireAdmin();

$applicationModel = new Application();
$jobModel = new Job();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $applicationModel->updateStatus((int)$_POST['id'], $_POST['status'], $_POST['notes'] ?? null);
        $message = 'Application status updated.';
        $messageType = 'success';
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $applicationModel->delete((int)$_POST['id']);
        $message = 'Application deleted.';
        $messageType = 'success';
    }
}

$jobId = $_GET['job_id'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));

if ($jobId) {
    $job = $jobModel->find((int)$jobId);
    $applications = $applicationModel->getByJob((int)$jobId, $page);
} else {
    $applications = ['items' => db()->fetchAll(
        "SELECT a.*, j.title as job_title, j.company, u.full_name, u.email, u.avatar
         FROM job_applications a
         JOIN jobs j ON a.job_id = j.id
         JOIN users u ON a.user_id = u.id
         ORDER BY a.created_at DESC LIMIT 50"
    ), 'total' => 0];
}

$pageTitle = 'Applications';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Job Applications</h1>
            <p class="text-gray-600"><?php echo isset($job) ? 'For: ' . $job['title'] : 'All applications'; ?></p>
        </div>
        <?php if (isset($job)): ?>
            <a href="applications.php" class="text-primary hover:underline"><i class="fas fa-arrow-left"></i> View All</a>
        <?php endif; ?>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                    <?php if (!isset($job)): ?>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($applications['items'])): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No applications found.</td></tr>
                <?php else: ?>
                    <?php foreach ($applications['items'] as $app): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="<?php echo getAvatar($app['avatar'] ?? null); ?>" class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo $app['full_name']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $app['email']; ?></p>
                                </div>
                            </div>
                        </td>
                        <?php if (!isset($job)): ?>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900"><?php echo $app['job_title']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo $app['company']; ?></p>
                            </td>
                        <?php endif; ?>
                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo timeAgo($app['created_at']); ?></td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-full border-0 
                                    <?php echo match($app['status']) {
                                        'shortlisted' => 'bg-blue-100 text-blue-700',
                                        'interview' => 'bg-purple-100 text-purple-700',
                                        'hired' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    }; ?>">
                                    <option value="pending" <?php echo $app['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="reviewed" <?php echo $app['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                    <option value="shortlisted" <?php echo $app['status'] === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                    <option value="interview" <?php echo $app['status'] === 'interview' ? 'selected' : ''; ?>>Interview</option>
                                    <option value="rejected" <?php echo $app['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="hired" <?php echo $app['status'] === 'hired' ? 'selected' : ''; ?>>Hired</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php if ($app['resume']): ?>
                                <a href="<?php echo UPLOADS_URL; ?>/resumes/<?php echo $app['resume']; ?>" target="_blank" class="p-2 text-gray-500 hover:text-primary" title="View Resume">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            <?php endif; ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <button type="submit" class="p-2 text-gray-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
