<?php
/**
 * Admin Dashboard
 * SocialConnect Platform
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Application.php';

requireAdmin();

$userModel = new User();
$postModel = new Post();
$jobModel = new Job();
$applicationModel = new Application();

// Get statistics
$totalUsers = $userModel->count();
$totalPosts = $postModel->count();
$totalJobs = $jobModel->count();
$totalApplications = $applicationModel->count();

// Get user role distribution
$usersByRole = db()->fetchAll("SELECT role, COUNT(*) as count FROM users GROUP BY role");

// Get posts by category
$postsByCategory = db()->fetchAll(
    "SELECT c.name, c.color, COUNT(p.id) as count 
     FROM categories c LEFT JOIN posts p ON c.id = p.category_id 
     GROUP BY c.id ORDER BY count DESC"
);

// Get recent activity
$recentUsers = db()->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recentPosts = db()->fetchAll(
    "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5"
);

// Monthly stats for charts
$monthlyUsers = db()->fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
     FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
     GROUP BY month ORDER BY month"
);

$monthlyPosts = db()->fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
     FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
     GROUP BY month ORDER BY month"
);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?php echo currentUser()['full_name']; ?>!</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo number_format($totalUsers); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 12% from last month</p>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Posts</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo number_format($totalPosts); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-newspaper text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 8% from last month</p>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Job Listings</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo number_format($totalJobs); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-briefcase text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 15% from last month</p>
        </div>
        
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Applications</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo number_format($totalApplications); ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-orange-600 text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 23% from last month</p>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- User Growth Chart -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-900 mb-4">User Growth</h3>
            <canvas id="userGrowthChart" height="200"></canvas>
        </div>
        
        <!-- Posts by Category -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-900 mb-4">Posts by Category</h3>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
    </div>
    
    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900">Recent Users</h3>
                <a href="users.php" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($recentUsers as $user): ?>
                <div class="p-4 flex items-center gap-4">
                    <img src="<?php echo getAvatar($user['avatar']); ?>" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900"><?php echo $user['full_name']; ?></p>
                        <p class="text-sm text-gray-500"><?php echo $user['email']; ?></p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Recent Posts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900">Recent Posts</h3>
                <a href="posts.php" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($recentPosts as $post): ?>
                <div class="p-4">
                    <p class="font-medium text-gray-900 truncate"><?php echo $post['title']; ?></p>
                    <p class="text-sm text-gray-500">by <?php echo $post['username']; ?> • <?php echo timeAgo($post['created_at']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
const userCtx = document.getElementById('userGrowthChart').getContext('2d');
new Chart(userCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyUsers, 'month')); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode(array_column($monthlyUsers, 'count')); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Category Chart
const catCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(catCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($postsByCategory, 'name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($postsByCategory, 'count')); ?>,
            backgroundColor: <?php echo json_encode(array_column($postsByCategory, 'color')); ?>
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
