        </div><!-- End Main Content -->
    </div><!-- End Main Wrapper -->
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8 mt-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <a href="<?php echo BASE_URL; ?>" class="flex items-center gap-2 text-xl font-bold mb-4">
                        <i class="fas fa-globe bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent"></i>
                        <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">SocialConnect</span>
                    </a>
                    <p class="text-gray-600 text-sm">Your all-in-one platform for social networking, job hunting, learning, and staying informed across entertainment, politics, sports, and more.</p>
                </div>
                
                <!-- Categories -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Categories</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo BASE_URL; ?>/pages/entertainment.php" class="text-gray-600 hover:text-primary transition">Entertainment</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/education.php" class="text-gray-600 hover:text-primary transition">Education</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/sports.php" class="text-gray-600 hover:text-primary transition">Sports</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/cinema.php" class="text-gray-600 hover:text-primary transition">Cinema</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Services</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo BASE_URL; ?>/pages/jobs.php" class="text-gray-600 hover:text-primary transition">Job Portal</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/medical.php" class="text-gray-600 hover:text-primary transition">Medical Resources</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/education.php" class="text-gray-600 hover:text-primary transition">Learning Hub</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/messages.php" class="text-gray-600 hover:text-primary transition">Messaging</a></li>
                    </ul>
                </div>
                
                <!-- Connect -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Connect</h4>
                    <div class="flex gap-4 mb-4">
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-primary hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-primary hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-primary hover:text-white transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-primary hover:text-white transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-envelope mr-2"></i> support@socialconnect.com
                    </p>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-200 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">&copy; <?php echo date('Y'); ?> SocialConnect. All rights reserved.</p>
                <div class="flex gap-6 text-sm">
                    <a href="<?php echo BASE_URL; ?>/privacy.php" class="text-gray-500 hover:text-primary transition">Privacy Policy</a>
                    <a href="<?php echo BASE_URL; ?>/terms.php" class="text-gray-500 hover:text-primary transition">Terms of Service</a>
                    <a href="<?php echo BASE_URL; ?>/cookies.php" class="text-gray-500 hover:text-primary transition">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
    
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
