<?php
/**
 * Admin - User Management
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

requireAdmin();

$userModel = new User();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $userModel->delete((int)$_POST['id']);
        $message = 'User deleted successfully.';
        $messageType = 'success';
    } elseif ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $userModel->update((int)$_POST['id'], ['status' => $_POST['status']]);
        $message = 'User status updated.';
        $messageType = 'success';
    } elseif ($action === 'update_role' && isset($_POST['id'], $_POST['role'])) {
        $userModel->update((int)$_POST['id'], ['role' => $_POST['role']]);
        $message = 'User role updated.';
        $messageType = 'success';
    }
}

// Filters
$filters = [
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = max(1, (int)($_GET['page'] ?? 1));
$users = $userModel->getAllFiltered($filters, $page);

$pageTitle = 'User Management';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
            <p class="text-gray-600">Manage all platform users</p>
        </div>
        <a href="user-add.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
            <i class="fas fa-plus"></i> Add User
        </a>
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
                <input type="text" name="search" value="<?php echo $filters['search']; ?>" placeholder="Name, email, username..."
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <option value="">All Roles</option>
                    <?php foreach (USER_ROLES as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $filters['role'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="banned" <?php echo $filters['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">Filter</button>
                <a href="users.php" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>
    
    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($users['items'])): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users['items'] as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="<?php echo getAvatar($user['avatar']); ?>" class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo $user['full_name']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $user['email']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <select name="role" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-full border-0 <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'; ?>">
                                    <?php foreach (USER_ROLES as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $user['role'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-full border-0 <?php 
                                    echo $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 
                                        ($user['status'] === 'banned' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'); ?>">
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="banned" <?php echo $user['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo formatDate($user['created_at']); ?></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="p-2 text-gray-500 hover:text-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
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
        
        <!-- Pagination -->
        <?php if ($users['total_pages'] > 1): ?>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">Showing <?php echo count($users['items']); ?> of <?php echo $users['total']; ?> users</p>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $users['total_pages']; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>" 
                       class="px-3 py-1 rounded <?php echo $i === $page ? 'bg-primary text-white' : 'hover:bg-gray-200'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
