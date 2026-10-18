<?php
/**
 * Admin - Post Management
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';

requireAdmin();

$postModel = new Post();
$categoryModel = new Category();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $postModel->delete((int)$_POST['id']);
        $message = 'Post deleted successfully.';
        $messageType = 'success';
    } elseif ($action === 'update_status' && isset($_POST['id'], $_POST['status'])) {
        $postModel->update((int)$_POST['id'], ['status' => $_POST['status']]);
        $message = 'Post status updated.';
        $messageType = 'success';
    } elseif ($action === 'toggle_featured' && isset($_POST['id'])) {
        $post = $postModel->find((int)$_POST['id']);
        $postModel->update((int)$_POST['id'], ['is_featured' => !$post['is_featured']]);
        $message = 'Featured status toggled.';
        $messageType = 'success';
    }
}

// Filters
$filters = [
    'category_id' => $_GET['category'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = max(1, (int)($_GET['page'] ?? 1));
$posts = $postModel->getAllFiltered($filters, $page);
$categories = $categoryModel->all('name', 'ASC');

$pageTitle = 'Post Management';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Posts</h1>
            <p class="text-gray-600">Manage and moderate all posts</p>
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
                <input type="text" name="search" value="<?php echo $filters['search']; ?>" placeholder="Search posts..."
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filters['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <option value="">All Status</option>
                    <option value="published" <?php echo $filters['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $filters['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="hidden" <?php echo $filters['status'] === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">Filter</button>
                <a href="posts.php" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>
    
    <!-- Posts Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Post</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stats</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($posts['items'])): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No posts found.</td></tr>
                <?php else: ?>
                    <?php foreach ($posts['items'] as $post): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <?php if ($post['is_featured']): ?>
                                    <i class="fas fa-star text-yellow-500"></i>
                                <?php endif; ?>
                                <div>
                                    <p class="font-medium text-gray-900 line-clamp-1"><?php echo $post['title']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo timeAgo($post['created_at']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium text-white rounded-full" style="background: <?php echo $post['category_color']; ?>">
                                <?php echo $post['category_name']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo $post['full_name']; ?></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
                                <span title="Likes"><i class="far fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                <span title="Comments"><i class="far fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                                <span title="Views"><i class="far fa-eye"></i> <?php echo $post['views_count']; ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-full border-0 <?php 
                                    echo $post['status'] === 'published' ? 'bg-green-100 text-green-700' : 
                                        ($post['status'] === 'hidden' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'); ?>">
                                    <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="hidden" <?php echo $post['status'] === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_featured">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="p-2 text-gray-500 hover:text-yellow-500" title="Toggle Featured">
                                        <i class="<?php echo $post['is_featured'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    </button>
                                </form>
                                <a href="<?php echo BASE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="p-2 text-gray-500 hover:text-primary" title="View">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this post?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
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
        <?php if ($posts['total_pages'] > 1): ?>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">Showing <?php echo count($posts['items']); ?> of <?php echo $posts['total']; ?> posts</p>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= min(5, $posts['total_pages']); $i++): ?>
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
