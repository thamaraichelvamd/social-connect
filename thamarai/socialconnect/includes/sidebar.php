<?php
/**
 * Sidebar Include
 * SocialConnect Platform
 */

$categories = getCategories();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Sidebar -->
<aside class="sidebar fixed lg:sticky top-0 lg:top-[73px] left-0 w-72 h-screen lg:h-[calc(100vh-73px)] bg-white border-r border-gray-200 p-6 overflow-y-auto z-50 -translate-x-full lg:translate-x-0 transition-transform">
    
    <!-- Main Navigation -->
    <div class="sidebar__section mb-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Menu</h3>
        <nav class="sidebar__nav space-y-1">
            <a href="<?php echo BASE_URL; ?>/index.php" class="sidebar__link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition <?php echo $currentPage === 'index' ? 'bg-primary/10 text-primary' : ''; ?>">
                <i class="fas fa-home w-5"></i>
                <span>Home Feed</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/trending.php" class="sidebar__link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition <?php echo $currentPage === 'trending' ? 'bg-primary/10 text-primary' : ''; ?>">
                <i class="fas fa-fire w-5"></i>
                <span>Trending</span>
            </a>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>/saved.php" class="sidebar__link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition <?php echo $currentPage === 'saved' ? 'bg-primary/10 text-primary' : ''; ?>">
                    <i class="fas fa-bookmark w-5"></i>
                    <span>Saved Posts</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
    
    <!-- Categories -->
    <div class="sidebar__section mb-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Categories</h3>
        <nav class="sidebar__nav space-y-1">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo BASE_URL; ?>/pages/<?php echo $category['slug']; ?>.php" 
                   class="sidebar__category flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition">
                    <span class="w-2 h-2 rounded-full" style="background-color: <?php echo $category['color']; ?>"></span>
                    <i class="<?php echo $category['icon']; ?> w-5 text-gray-400"></i>
                    <span><?php echo $category['name']; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    
    <!-- Quick Links -->
    <div class="sidebar__section mb-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Quick Links</h3>
        <nav class="sidebar__nav space-y-1">
            <a href="<?php echo BASE_URL; ?>/pages/jobs.php" class="sidebar__link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition">
                <i class="fas fa-briefcase w-5 text-primary"></i>
                <span>Job Portal</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/medical.php" class="sidebar__link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition">
                <i class="fas fa-heartbeat w-5 text-pink-500"></i>
                <span>Medical Resources</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/education.php" class="sidebar__link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition">
                <i class="fas fa-graduation-cap w-5 text-blue-500"></i>
                <span>Learning Hub</span>
            </a>
        </nav>
    </div>
    
    <!-- Footer Links -->
    <div class="sidebar__footer pt-6 border-t border-gray-200">
        <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-500">
            <a href="<?php echo BASE_URL; ?>/about.php" class="hover:text-primary">About</a>
            <a href="<?php echo BASE_URL; ?>/privacy.php" class="hover:text-primary">Privacy</a>
            <a href="<?php echo BASE_URL; ?>/terms.php" class="hover:text-primary">Terms</a>
            <a href="<?php echo BASE_URL; ?>/contact.php" class="hover:text-primary">Contact</a>
        </div>
        <p class="text-xs text-gray-400 mt-3">&copy; <?php echo date('Y'); ?> SocialConnect. All rights reserved.</p>
    </div>
</aside>
