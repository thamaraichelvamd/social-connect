<?php
/**
 * Admin - Job Management
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Job.php';

requireAdmin();

$jobModel = new Job();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $jobModel->delete((int)$_POST['id']);
        $message = 'Job deleted successfully.';
        $messageType = 'success';
    } elseif ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $jobModel->update((int)$_POST['id'], ['status' => $_POST['status']]);
        $message = 'Job status updated.';
        $messageType = 'success';
    } elseif ($action === 'toggle_featured' && isset($_POST['id'])) {
        $job = $jobModel->find((int)$_POST['id']);
        $jobModel->update((int)$_POST['id'], ['is_featured' => !$job['is_featured']]);
        $message = 'Featured status toggled.';
        $messageType = 'success';
    }
}

$filters = [
    'status' => $_GET['status'] ?? '',
    'job_type' => $_GET['type'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = max(1, (int)($_GET['page'] ?? 1));
$jobs = $jobModel->getAllFiltered($filters, $page);

$pageTitle = 'Job Management';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jobs</h1>
            <p class="text-gray-600">Manage all job listings</p>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl p-4 mb-6 shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo $filters['search']; ?>" placeholder="Job title, company..."
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <option value="">All Types</option>
                    <?php foreach (JOB_TYPES as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $filters['job_type'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <option value="">All Status</option>
                    <option value="open" <?php echo $filters['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    <option value="filled" <?php echo $filters['status'] === 'filled' ? 'selected' : ''; ?>>Filled</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">Filter</button>
                <a href="jobs.php" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>
    
    <!-- Jobs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Applications</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($jobs['items'])): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No jobs found.</td></tr>
                <?php else: ?>
                    <?php foreach ($jobs['items'] as $job): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <?php if ($job['is_featured']): ?>
                                    <i class="fas fa-star text-yellow-500"></i>
                                <?php endif; ?>
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo $job['title']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $job['company']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                <?php echo JOB_TYPES[$job['job_type']] ?? $job['job_type']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo $job['location']; ?></td>
                        <td class="px-4 py-3 text-center">
                            <a href="applications.php?job_id=<?php echo $job['id']; ?>" class="text-primary hover:underline">
                                <?php echo $job['applications_count']; ?> <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-full border-0 <?php 
                                    echo $job['status'] === 'open' ? 'bg-green-100 text-green-700' : 
                                        ($job['status'] === 'filled' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'); ?>">
                                    <option value="open" <?php echo $job['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="closed" <?php echo $job['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    <option value="filled" <?php echo $job['status'] === 'filled' ? 'selected' : ''; ?>>Filled</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_featured">
                                    <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" class="p-2 text-gray-500 hover:text-yellow-500" title="Toggle Featured">
                                        <i class="<?php echo $job['is_featured'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    </button>
                                </form>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this job?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                                    <button type="submit" class="p-2 text-gray-500 hover:text-red-500" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
