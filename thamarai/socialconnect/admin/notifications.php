<?php
/**
 * Admin - Notifications Management
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Notification.php';

requireAdmin();

$model = new Notification();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_old') {
        $deleted = $model->deleteOld(30);
        $message = "Deleted {$deleted} old notifications.";
    } elseif ($action === 'send_broadcast') {
        $title = sanitize($_POST['title']);
        $msg = sanitize($_POST['message']);
        $users = db()->fetchAll("SELECT id FROM users WHERE status = 'active'");
        foreach ($users as $user) {
            $model->createNotification($user['id'], 'broadcast', $title, $msg, '', 'fas fa-bullhorn');
        }
        $message = 'Broadcast sent to ' . count($users) . ' users.';
    }
}

$notifications = db()->fetchAll(
    "SELECT n.*, u.full_name, u.email FROM notifications n 
     JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC LIMIT 50"
);

$pageTitle = 'Notifications';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600">Manage system notifications</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('broadcastModal').classList.remove('hidden')" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                <i class="fas fa-bullhorn"></i> Send Broadcast
            </button>
            <form method="POST" onsubmit="return confirm('Delete notifications older than 30 days?');">
                <input type="hidden" name="action" value="delete_old">
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                    <i class="fas fa-trash"></i> Clean Old
                </button>
            </form>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-700"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notification</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($notifications)): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No notifications found.</td></tr>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo $n['full_name']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $n['email']; ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo $n['title']; ?></p>
                            <p class="text-xs text-gray-500 line-clamp-1"><?php echo $n['message']; ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700"><?php echo $n['type']; ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($n['is_read']): ?>
                                <span class="text-green-500"><i class="fas fa-check"></i></span>
                            <?php else: ?>
                                <span class="text-gray-400"><i class="far fa-circle"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo timeAgo($n['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Broadcast Modal -->
<div id="broadcastModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Send Broadcast Notification</h3>
        <form method="POST">
            <input type="hidden" name="action" value="send_broadcast">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="message" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg">Send to All</button>
                <button type="button" onclick="document.getElementById('broadcastModal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
