<?php
/**
 * Login Page
 * SocialConnect Platform
 */

require_once 'includes/functions.php';
require_once 'models/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL);
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $userModel = new User();
        $result = $userModel->authenticate($email, $password);
        
        if ($result['success']) {
            $redirectUrl = $_SESSION['redirect_after_login'] ?? BASE_URL;
            unset($_SESSION['redirect_after_login']);
            
            // Redirect based on role
            if ($result['user']['role'] === ROLE_ADMIN) {
                redirect(ADMIN_URL);
            }
            
            redirect($redirectUrl);
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | SocialConnect</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#6366f1', secondary: '#8b5cf6' } } }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary via-secondary to-pink-500 flex items-center justify-center p-4 font-sans">
    
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center gap-3 text-3xl font-bold text-white">
                <i class="fas fa-globe"></i>
                <span>SocialConnect</span>
            </a>
            <p class="text-white/70 mt-2">Welcome back! Please login to continue.</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <?php echo csrfField(); ?>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" required
                               class="w-full px-4 py-3 pl-11 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition"
                               placeholder="Enter your email">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 pl-11 pr-11 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition"
                               placeholder="Enter your password">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-primary hover:underline">Forgot password?</a>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-secondary text-white font-semibold rounded-xl hover:opacity-90 transition transform hover:scale-[1.02] active:scale-[0.98]">
                    Login
                </button>
            </form>
            
            <!-- Divider -->
            <div class="flex items-center gap-4 my-6">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-sm text-gray-400">or continue with</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            
            <!-- Social Login -->
            <div class="grid grid-cols-3 gap-3">
                <button class="flex items-center justify-center py-3 border-2 border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition">
                    <i class="fab fa-google text-red-500"></i>
                </button>
                <button class="flex items-center justify-center py-3 border-2 border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition">
                    <i class="fab fa-facebook-f text-blue-600"></i>
                </button>
                <button class="flex items-center justify-center py-3 border-2 border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition">
                    <i class="fab fa-twitter text-sky-500"></i>
                </button>
            </div>
            
            <!-- Register Link -->
            <p class="text-center mt-6 text-gray-600">
                Don't have an account? 
                <a href="register.php" class="text-primary font-semibold hover:underline">Sign up</a>
            </p>
        </div>
        
        <!-- Back to Home -->
        <p class="text-center mt-6">
            <a href="<?php echo BASE_URL; ?>" class="text-white/70 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Home
            </a>
        </p>
    </div>
    
    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
