<?php
/**
 * Registration Page
 * SocialConnect Platform
 */

require_once 'includes/functions.php';
require_once 'models/User.php';

if (isLoggedIn()) { redirect(BASE_URL); }

$error = '';
$success = '';
$formData = ['username' => '', 'email' => '', 'full_name' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $formData = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'role' => in_array($_POST['role'] ?? '', ['user', 'employer', 'medical', 'educator']) ? $_POST['role'] : 'user'
        ];
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!isset($_POST['terms'])) {
            $error = 'You must agree to the Terms of Service.';
        } else {
            $formData['password'] = $password;
            $userModel = new User();
            $result = $userModel->register($formData);
            
            if ($result['success']) {
                setFlash('Registration successful! Please login.', MSG_SUCCESS);
                redirect('login.php');
            } else {
                $error = $result['error'];
            }
        }
    }
}

$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | SocialConnect</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#6366f1', secondary: '#8b5cf6' } } } }</script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary via-secondary to-pink-500 flex items-center justify-center p-4 font-sans">
    
    <div class="w-full max-w-lg">
        <!-- Logo -->
        <div class="text-center mb-6">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center gap-3 text-3xl font-bold text-white">
                <i class="fas fa-globe"></i><span>SocialConnect</span>
            </a>
            <p class="text-white/70 mt-2">Create your account and join our community</p>
        </div>
        
        <!-- Register Card -->
        <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i><span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-5">
                <?php echo csrfField(); ?>
                
                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">I want to join as</label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php 
                        $roles = [
                            'user' => ['icon' => 'fa-user', 'label' => 'Regular User', 'desc' => 'Browse & share content'],
                            'employer' => ['icon' => 'fa-building', 'label' => 'Employer', 'desc' => 'Post job listings'],
                            'medical' => ['icon' => 'fa-stethoscope', 'label' => 'Medical Pro', 'desc' => 'Share health resources'],
                            'educator' => ['icon' => 'fa-graduation-cap', 'label' => 'Educator', 'desc' => 'Create learning content']
                        ];
                        foreach ($roles as $value => $role): ?>
                        <label class="role-option relative cursor-pointer">
                            <input type="radio" name="role" value="<?php echo $value; ?>" <?php echo $formData['role'] === $value ? 'checked' : ''; ?> class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 transition">
                                <i class="fas <?php echo $role['icon']; ?> text-xl text-gray-400 peer-checked:text-primary mb-2"></i>
                                <h4 class="font-medium text-gray-900 text-sm"><?php echo $role['label']; ?></h4>
                                <p class="text-xs text-gray-500"><?php echo $role['desc']; ?></p>
                            </div>
                            <div class="absolute top-2 right-2 w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Name Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="full_name" value="<?php echo $formData['full_name']; ?>" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" value="<?php echo $formData['username']; ?>" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition" placeholder="johndoe">
                    </div>
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" value="<?php echo $formData['email']; ?>" required
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition" placeholder="john@example.com">
                </div>
                
                <!-- Password Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition" placeholder="Min 6 characters">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary transition" placeholder="Confirm password">
                    </div>
                </div>
                
                <!-- Terms -->
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="terms" class="w-4 h-4 mt-1 text-primary border-gray-300 rounded focus:ring-primary">
                    <span class="text-sm text-gray-600">I agree to the <a href="terms.php" class="text-primary hover:underline">Terms of Service</a> and <a href="privacy.php" class="text-primary hover:underline">Privacy Policy</a></span>
                </label>
                
                <!-- Submit -->
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-secondary text-white font-semibold rounded-xl hover:opacity-90 transition transform hover:scale-[1.02]">
                    Create Account
                </button>
            </form>
            
            <p class="text-center mt-6 text-gray-600">
                Already have an account? <a href="login.php" class="text-primary font-semibold hover:underline">Login</a>
            </p>
        </div>
        
        <p class="text-center mt-6">
            <a href="<?php echo BASE_URL; ?>" class="text-white/70 hover:text-white transition"><i class="fas fa-arrow-left mr-2"></i> Back to Home</a>
        </p>
    </div>
</body>
</html>
