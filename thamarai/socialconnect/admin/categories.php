<?php
/**
 * Admin - Category Management
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Category.php';

requireAdmin();

$categoryModel = new Category();
$message = '';
$messageType = '';
$editCategory = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $categoryModel->createCategory([
            'name' => sanitize($_POST['name']),
            'icon' => sanitize($_POST['icon']),
            'description' => sanitize($_POST['description']),
            'color' => $_POST['color']
        ]);
        $message = 'Category created successfully.';
        $messageType = 'success';
    } elseif ($action === 'update' && isset($_POST['id'])) {
        $categoryModel->updateCategory((int)$_POST['id'], [
            'name' => sanitize($_POST['name']),
            'icon' => sanitize($_POST['icon']),
            'description' => sanitize($_POST['description']),
            'color' => $_POST['color']
        ]);
        $message = 'Category updated successfully.';
        $messageType = 'success';
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $categoryModel->delete((int)$_POST['id']);
        $message = 'Category deleted successfully.';
        $messageType = 'success';
    } elseif ($action === 'toggle' && isset($_POST['id'])) {
        $categoryModel->toggleActive((int)$_POST['id']);
        $message = 'Category status toggled.';
        $messageType = 'success';
    }
}

if (isset($_GET['edit'])) {
    $editCategory = $categoryModel->find((int)$_GET['edit']);
}

$categories = $categoryModel->getWithPostCount();

$pageTitle = 'Category Management';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <p class="text-gray-600">Manage content categories</p>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-4"><?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?></h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'create'; ?>">
                    <?php if ($editCategory): ?>
                        <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required value="<?php echo $editCategory['name'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Font Awesome)</label>
                        <input type="text" name="icon" placeholder="fas fa-film" value="<?php echo $editCategory['icon'] ?? 'fas fa-folder'; ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input type="color" name="color" value="<?php echo $editCategory['color'] ?? '#6366f1'; ?>"
                               class="w-full h-10 border border-gray-200 rounded-lg cursor-pointer">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary"><?php echo $editCategory['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            <?php echo $editCategory ? 'Update' : 'Create'; ?>
                        </button>
                        <?php if ($editCategory): ?>
                            <a href="categories.php" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Categories List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Posts</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white" style="background: <?php echo $cat['color']; ?>">
                                        <i class="<?php echo $cat['icon']; ?>"></i>
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo $cat['name']; ?></p>
                                        <p class="text-xs text-gray-500">/<?php echo $cat['slug']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700"><?php echo $cat['posts_count']; ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $cat['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="?edit=<?php echo $cat['id']; ?>" class="p-2 text-gray-500 hover:text-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" class="p-2 text-gray-500 hover:text-yellow-500" title="Toggle Status">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-500" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
