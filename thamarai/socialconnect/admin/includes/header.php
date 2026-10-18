<?php
/**
 * Admin Header
 * SocialConnect Platform
 */

require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$currentUser = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin | ' : 'Admin | '; ?>SocialConnect</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#6366f1', secondary: '#8b5cf6' } } }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex min-h-screen">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white fixed h-full">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-800">
                <a href="<?php echo ADMIN_URL; ?>" class="flex items-center gap-3 text-xl font-bold">
                    <i class="fas fa-globe text-primary"></i>
                    <span>SocialConnect</span>
                </a>
                <p class="text-xs text-gray-500 mt-1">Admin Panel</p>
            </div>
            
            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <a href="<?php echo ADMIN_URL; ?>/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'index' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <p class="text-xs text-gray-600 uppercase tracking-wider px-4 pt-4 pb-2">Content</p>
                
                <a href="<?php echo ADMIN_URL; ?>/users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'users' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span>Users</span>
                </a>
                <a href="<?php echo ADMIN_URL; ?>/posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'posts' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-newspaper w-5"></i>
                    <span>Posts</span>
                </a>
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'categories' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-folder w-5"></i>
                    <span>Categories</span>
                </a>
                
                <p class="text-xs text-gray-600 uppercase tracking-wider px-4 pt-4 pb-2">Services</p>
                
                <a href="<?php echo ADMIN_URL; ?>/jobs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'jobs' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-briefcase w-5"></i>
                    <span>Jobs</span>
                </a>
                <a href="<?php echo ADMIN_URL; ?>/applications.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'applications' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Applications</span>
                </a>
                <a href="<?php echo ADMIN_URL; ?>/medical.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'medical' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-heartbeat w-5"></i>
                    <span>Medical</span>
                </a>
                <a href="<?php echo ADMIN_URL; ?>/education.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'education' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-graduation-cap w-5"></i>
                    <span>Education</span>
                </a>
                
                <p class="text-xs text-gray-600 uppercase tracking-wider px-4 pt-4 pb-2">Communication</p>
                
                <a href="<?php echo ADMIN_URL; ?>/messages.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'messages' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-envelope w-5"></i>
                    <span>Messages</span>
                </a>
                <a href="<?php echo ADMIN_URL; ?>/notifications.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'notifications' ? 'bg-primary text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <i class="fas fa-bell w-5"></i>
                    <span>Notifications</span>
                </a>
            </nav>
            
            <!-- User Info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
                <div class="flex items-center gap-3">
                    <img src="<?php echo getAvatar($currentUser['avatar']); ?>" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate"><?php echo $currentUser['full_name']; ?></p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="text-gray-500 hover:text-white" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="<?php echo BASE_URL; ?>" target="_blank" class="text-gray-500 hover:text-primary" title="View Site">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <button class="relative text-gray-500 hover:text-primary">
                            <i class="fas fa-bell"></i>
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center">3</span>
                        </button>
                    </div>
                </div>
            </header>
