<?php
/**
 * Admin - Medical Resources
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/MedicalResource.php';

requireAdmin();

$model = new MedicalResource();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && isset($_POST['id'])) {
        $model->delete((int)$_POST['id']);
        $message = 'Resource deleted.';
    } elseif ($action === 'verify' && isset($_POST['id'])) {
        $model->verify((int)$_POST['id'], $_SESSION['user_id']);
        $message = 'Resource verified.';
    } elseif ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $model->update((int)$_POST['id'], ['status' => $_POST['status']]);
        $message = 'Status updated.';
    }
}

$items = db()->fetchAll(
    "SELECT m.*, u.full_name as author_name FROM medical_resources m 
     JOIN users u ON m.author_id = u.id ORDER BY m.created_at DESC LIMIT 50"
);

$pageTitle = 'Medical Resources';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Medical Resources</h1>
        <p class="text-gray-600">Manage health and medical content</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-700"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resource</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Verified</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No resources found.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 line-clamp-1"><?php echo $item['title']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $item['specialty'] ?? 'General'; ?></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo $item['author_name']; ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-pink-100 text-pink-700"><?php echo ucfirst($item['content_type']); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($item['is_verified']): ?>
                                <i class="fas fa-check-circle text-green-500"></i>
                            <?php else: ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="verify">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="text-gray-400 hover:text-green-500" title="Verify">
                                        <i class="far fa-check-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-full border-0 
                                    <?php echo $item['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <option value="published" <?php echo $item['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $item['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="archived" <?php echo $item['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
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
