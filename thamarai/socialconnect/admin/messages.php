<?php
/**
 * Admin - Messages Overview
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$messages = db()->fetchAll(
    "SELECT m.*, s.full_name as sender_name, s.email as sender_email,
            r.full_name as receiver_name, r.email as receiver_email
     FROM messages m
     JOIN users s ON m.sender_id = s.id
     JOIN users r ON m.receiver_id = r.id
     ORDER BY m.created_at DESC LIMIT 50"
);

$pageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
        <p class="text-gray-600">Overview of platform messaging</p>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Content</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($messages)): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No messages found.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo $msg['sender_name']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $msg['sender_email']; ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo $msg['receiver_name']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $msg['receiver_email']; ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-700 line-clamp-2"><?php echo truncate($msg['content'], 100); ?></p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($msg['is_read']): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Read</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Unread</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo timeAgo($msg['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
