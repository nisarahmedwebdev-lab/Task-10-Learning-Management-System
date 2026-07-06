<?php
// includes/navbar.php

// Fix paths
require_once __DIR__ . '/../config/session.php';

// Determine base path for links
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
} else {
    $base_path = '';
}

$profile_image = $_SESSION['profile_image'] ?? 'default-user.png';
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="<?php echo isLoggedIn() ? (isAdmin() ? $base_path . 'admin/dashboard.php' : $base_path . 'dashboard.php') : $base_path . 'index.php'; ?>">
                <?php echo APP_NAME; ?>
            </a>
        </div>
        
        <div class="navbar-menu">
            <?php if (isLoggedIn()): ?>
                <?php if (isStudent()): ?>
                    <a href="<?php echo $base_path; ?>dashboard.php">Dashboard</a>
                    <a href="<?php echo $base_path; ?>courses.php">Browse Courses</a>
                <?php elseif (isAdmin()): ?>
                    <a href="<?php echo $base_path; ?>admin/dashboard.php">Dashboard</a>
                    <a href="<?php echo $base_path; ?>admin/courses.php">Manage Courses</a>
                <?php endif; ?>
                
                <div class="user-menu">
                    <img src="<?php echo UPLOAD_URL . 'users/' . $profile_image; ?>" 
                         alt="Profile" class="user-avatar">
                    <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    <a href="<?php echo $base_path; ?>logout.php" class="btn btn-small btn-danger">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>login.php">Login</a>
                <a href="<?php echo $base_path; ?>register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="main-content">