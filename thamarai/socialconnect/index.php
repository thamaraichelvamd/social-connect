<?php
/**
 * Homepage
 * SocialConnect Platform
 */

$pageTitle = 'Home';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'models/Post.php';
require_once 'models/Category.php';

$postModel = new Post();
$categoryModel = new Category();

// Get posts
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$posts = $postModel->getPublished($page);
$featuredPosts = $postModel->getFeatured(3);
$trendingPosts = $postModel->getTrending(5);
$categories = $categoryModel->getWithPostCount();
?>

<!-- Main Content -->
<main class="main-content flex-1 p-6">
    <div class="max-w-4xl mx-auto">
        
        <!-- Welcome Banner -->
        <?php if (!isLoggedIn()): ?>
        <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-8 mb-8 text-white">
            <h1 class="text-3xl font-bold mb-3">Welcome to SocialConnect</h1>
            <p class="text-white/80 mb-6 max-w-xl">Your all-in-one platform for entertainment, education, jobs, and more. Join millions of users sharing and connecting.</p>
            <div class="flex gap-4">
                <a href="register.php" class="px-6 py-3 bg-white text-primary font-semibold rounded-full hover:bg-gray-100 transition">Get Started</a>
                <a href="login.php" class="px-6 py-3 border-2 border-white/50 text-white font-semibold rounded-full hover:bg-white/10 transition">Login</a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Featured Posts -->
        <?php if (!empty($featuredPosts)): ?>
        <section class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-star text-yellow-500"></i> Featured
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($featuredPosts as $post): ?>
                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="group block bg-white rounded-xl overflow-hidden shadow hover:shadow-lg transition">
                    <?php if ($post['media']): ?>
                        <div class="h-32 bg-gray-200">
                            <img src="uploads/posts/<?php echo $post['media']; ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition">
                        </div>
                    <?php else: ?>
                        <div class="h-32 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <i class="fas fa-newspaper text-4xl text-gray-300"></i>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <span class="text-xs font-semibold text-white px-2 py-1 rounded-full" style="background: <?php echo $post['category_color']; ?>"><?php echo $post['category_name']; ?></span>
                        <h3 class="font-semibold text-gray-900 mt-2 group-hover:text-primary transition line-clamp-2"><?php echo $post['title']; ?></h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Category Filters -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <a href="index.php" class="px-4 py-2 bg-primary text-white rounded-full text-sm font-medium whitespace-nowrap">All Posts</a>
            <?php foreach ($categories as $cat): ?>
                <a href="pages/<?php echo $cat['slug']; ?>.php" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-full text-sm font-medium hover:border-primary hover:text-primary transition whitespace-nowrap">
                    <?php echo $cat['name']; ?>
                    <span class="ml-1 text-gray-400">(<?php echo $cat['posts_count']; ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Posts Feed -->
        <div class="posts-feed space-y-6">
            <?php if (empty($posts['items'])): ?>
                <div class="bg-white rounded-xl p-12 text-center">
                    <i class="fas fa-newspaper text-6xl text-gray-200 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No posts yet</h3>
                    <p class="text-gray-500 mb-4">Be the first to share something with the community!</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="create-post.php" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-full font-medium hover:opacity-90 transition">
                            <i class="fas fa-plus"></i> Create Post
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($posts['items'] as $post): ?>
                <article class="post-card bg-white rounded-xl shadow hover:shadow-lg transition">
                    <!-- Header -->
                    <div class="p-4 flex items-center gap-3">
                        <img src="<?php echo getAvatar($post['avatar']); ?>" alt="<?php echo $post['full_name']; ?>" class="w-11 h-11 rounded-full object-cover">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 text-sm"><?php echo $post['full_name']; ?></h4>
                            <span class="text-xs text-gray-500"><?php echo timeAgo($post['created_at']); ?></span>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold text-white rounded-full" style="background: <?php echo $post['category_color']; ?>"><?php echo $post['category_name']; ?></span>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-4 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <a href="post.php?slug=<?php echo $post['slug']; ?>" class="hover:text-primary transition"><?php echo $post['title']; ?></a>
                        </h3>
                        <p class="text-gray-600 text-sm"><?php echo $post['excerpt']; ?></p>
                    </div>
                    
                    <!-- Media -->
                    <?php if ($post['media']): ?>
                    <div class="post-card__media">
                        <img src="uploads/posts/<?php echo $post['media']; ?>" alt="" class="w-full max-h-[400px] object-cover">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Actions -->
                    <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex gap-4">
                            <button class="post-card__action like-btn flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition <?php echo (isLoggedIn() && $postModel->isLikedBy($post['id'], $_SESSION['user_id'] ?? 0)) ? 'text-red-500' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                                <i class="<?php echo (isLoggedIn() && $postModel->isLikedBy($post['id'], $_SESSION['user_id'] ?? 0)) ? 'fas' : 'far'; ?> fa-heart"></i>
                                <span class="count text-sm font-medium"><?php echo formatNumber($post['likes_count']); ?></span>
                            </button>
                            <a href="post.php?slug=<?php echo $post['slug']; ?>#comments" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                                <i class="far fa-comment"></i>
                                <span class="text-sm font-medium"><?php echo formatNumber($post['comments_count']); ?></span>
                            </a>
                            <span class="flex items-center gap-2 px-3 py-2 text-gray-400">
                                <i class="far fa-eye"></i>
                                <span class="text-sm"><?php echo formatNumber($post['views_count']); ?></span>
                            </span>
                        </div>
                        <button class="share-btn px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition" data-url="<?php echo BASE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>">
                            <i class="far fa-share-square"></i>
                        </button>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($posts['total_pages'] > 1): ?>
        <nav class="pagination flex items-center justify-center gap-2 py-8">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination__item w-10 h-10 flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($posts['total_pages'], $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="pagination__item min-w-[40px] h-10 flex items-center justify-center rounded-lg font-medium transition <?php echo $i === $page ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $posts['total_pages']): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="pagination__item w-10 h-10 flex items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        
    </div>
</main>

<!-- Right Sidebar -->
<aside class="hidden xl:block w-80 p-6 space-y-6">
    <!-- Trending -->
    <div class="bg-white rounded-xl p-4 shadow">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-fire text-orange-500"></i> Trending Now
        </h3>
        <div class="space-y-4">
            <?php foreach (array_slice($trendingPosts, 0, 5) as $index => $trending): ?>
            <a href="post.php?slug=<?php echo $trending['slug']; ?>" class="flex gap-3 group">
                <span class="text-2xl font-bold text-gray-200"><?php echo $index + 1; ?></span>
                <div>
                    <h4 class="font-medium text-gray-900 text-sm group-hover:text-primary transition line-clamp-2"><?php echo $trending['title']; ?></h4>
                    <span class="text-xs text-gray-500"><?php echo $trending['views_count']; ?> views</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Categories Widget -->
    <div class="bg-white rounded-xl p-4 shadow">
        <h3 class="font-bold text-gray-900 mb-4">Browse Categories</h3>
        <div class="space-y-2">
            <?php foreach ($categories as $cat): ?>
            <a href="pages/<?php echo $cat['slug']; ?>.php" class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full" style="background: <?php echo $cat['color']; ?>"></span>
                    <span class="text-sm text-gray-700"><?php echo $cat['name']; ?></span>
                </div>
                <span class="text-xs text-gray-400"><?php echo $cat['posts_count']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</aside>

<?php require_once 'includes/footer.php'; ?>
