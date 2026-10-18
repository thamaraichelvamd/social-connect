<?php
/**
 * Header Include
 * SocialConnect Platform
 */

require_once __DIR__ . '/functions.php';

$currentUser = currentUser();
$categories = getCategories();
$unreadNotifications = getUnreadNotificationsCount();
$unreadMessages = getUnreadMessagesCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SocialConnect - Your all-in-one social media platform for entertainment, education, politics, cinema, sports, medical resources, and employment.">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>SocialConnect</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/compiled/style.css">
    
    <style>
        /* Inline critical styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f9fafb; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Alert Container -->
    <div class="alert-container fixed top-4 right-4 z-50 w-80 space-y-2">
        <?php if ($flash = getFlash()): ?>
            <div class="alert alert--<?php echo $flash['type']; ?> flex items-center gap-3 p-4 rounded-lg shadow-lg" data-auto-dismiss>
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <span class="flex-1"><?php echo $flash['message']; ?></span>
                <button class="alert__close opacity-50 hover:opacity-100"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header -->
    <header class="header bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="container mx-auto px-4">
            <div class="header__inner flex items-center justify-between py-4 gap-4">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>" class="header__logo flex items-center gap-3 text-xl font-bold text-gray-900">
                    <i class="fas fa-globe text-2xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent"></i>
                    <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">SocialConnect</span>
                </a>
                
                <!-- Mobile Menu Toggle -->
                <button class="header__menu-toggle lg:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Search -->
                <div class="header__search hidden md:block flex-1 max-w-lg relative">
                    <input type="text" placeholder="Search posts, jobs, resources..." 
                           class="w-full py-3 pl-10 pr-4 bg-gray-100 border-2 border-transparent rounded-full text-sm focus:outline-none focus:bg-white focus:border-primary transition">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                
                <!-- Navigation -->
                <nav class="header__nav flex items-center gap-2 md:gap-4">
                    <?php if (isLoggedIn()): ?>
                        <!-- Create Post -->
                        <a href="<?php echo BASE_URL; ?>/create-post.php" class="hidden md:flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-full text-sm font-medium hover:opacity-90 transition">
                            <i class="fas fa-plus"></i>
                            <span>Create</span>
                        </a>
                        
                        <!-- Messages -->
                        <a href="<?php echo BASE_URL; ?>/messages.php" class="header__link relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
                            <i class="fas fa-envelope text-lg"></i>
                            <?php if ($unreadMessages > 0): ?>
                                <span class="badge absolute top-0 right-0 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-semibold rounded-full flex items-center justify-center"><?php echo $unreadMessages > 9 ? '9+' : $unreadMessages; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Notifications -->
                        <div class="header__link relative" data-toggle="notifications">
                            <button class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
                                <i class="fas fa-bell text-lg"></i>
                                <?php if ($unreadNotifications > 0): ?>
                                    <span class="badge absolute top-0 right-0 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-semibold rounded-full flex items-center justify-center"><?php echo $unreadNotifications > 9 ? '9+' : $unreadNotifications; ?></span>
                                <?php endif; ?>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="header__user relative flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                            <img src="<?php echo getAvatar($currentUser['avatar']); ?>" alt="<?php echo $currentUser['full_name']; ?>" class="w-9 h-9 rounded-full object-cover">
                            <span class="hidden md:block font-medium text-gray-700"><?php echo $currentUser['full_name']; ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:block"></i>
                            
                            <!-- Dropdown -->
                            <div class="header__dropdown absolute top-full right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 hidden">
                                <a href="<?php echo BASE_URL; ?>/profile.php" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition">
                                    <i class="fas fa-user w-5 text-gray-400"></i>
                                    <span>My Profile</span>
                                </a>
                                <?php if ($currentUser['role'] === 'employer'): ?>
                                    <a href="<?php echo BASE_URL; ?>/my-jobs.php" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition">
                                        <i class="fas fa-briefcase w-5 text-gray-400"></i>
                                        <span>My Job Listings</span>
                                    </a>
                                <?php endif; ?>
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo ADMIN_URL; ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 transition">
                                        <i class="fas fa-cog w-5 text-gray-400"></i>
                                        <span>Admin Panel</span>
                                    </a>
                                <?php endif; ?>
                                <hr class="my-2 border-gray-100">
                                <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-2 text-red-500 hover:bg-red-50 transition">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="px-4 py-2 text-gray-700 hover:text-primary font-medium transition">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-full font-medium hover:opacity-90 transition">Sign Up</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>
    
    <!-- Main Wrapper -->
    <div class="main-wrapper flex">
