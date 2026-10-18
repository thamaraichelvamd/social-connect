<?php
/**
 * Logout
 * SocialConnect Platform
 */

require_once 'includes/functions.php';
require_once 'models/User.php';

$userModel = new User();
$userModel->logout();

setFlash('You have been logged out successfully.', MSG_SUCCESS);
redirect(BASE_URL);
