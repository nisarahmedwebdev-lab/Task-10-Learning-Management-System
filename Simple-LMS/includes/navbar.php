<?php
// includes/navbar.php

// Fix paths
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Determine base path for links
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
} else {
    $base_path = '';
}

$profile_image = $_SESSION['profile_image'] ?? 'default-user.png';
$user_image_url = getUserImage($profile_image);
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="<?php echo isLoggedIn() ? (isAdmin() ? $base_path . 'admin/dashboard.php' : $base_path . 'dashboard.php') : $base_path . 'index.php'; ?>">
                <!-- <i class="fas fa-graduation-cap"></i>
                <?php echo APP_NAME; ?> -->
            </a>
        </div>
        
        <!-- Hamburger Menu Button (3 lines) -->
        <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="navbar-menu" id="navbarMenu">
            <?php if (isLoggedIn()): ?>
                <?php if (isStudent()): ?>
                    <a href="<?php echo $base_path; ?>dashboard.php">
                        <i class="fas fa-chart-pie"></i> Dashboard
                    </a>
                    <a href="<?php echo $base_path; ?>courses.php">
                        <i class="fas fa-book"></i> Browse Courses
                    </a>
                <?php elseif (isAdmin()): ?>
                    <a href="<?php echo $base_path; ?>admin/dashboard.php">
                        <!-- <i class="fas fa-chart-pie"></i> Dashboard -->
                    </a>
                    <a href="<?php echo $base_path; ?>admin/courses.php">
                        <i class="fas fa-book"></i> Manage Courses
                    </a>
                    <a href="<?php echo $base_path; ?>admin/students.php">
                        <!-- <i class="fas fa-users"></i> Students -->
                    </a>
                <?php endif; ?>
                
                <div class="user-menu">
                    <img src="<?php echo $user_image_url; ?>" 
                         alt="Profile" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    <!-- <a href="<?php echo $base_path; ?>logout.php" class="btn btn-small btn-danger"> -->
                        <!-- <i class="fas fa-sign-out-alt"></i> Logout -->
                    </a>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>login.php">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="<?php echo $base_path; ?>register.php">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="main-content">

<style>
/* ==================== NAVBAR STYLES ==================== */

.navbar {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 0.8rem 0;
    position: sticky;
    top: 0;
    z-index: 999;
}

.navbar .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.navbar-brand a {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.4rem;
    font-weight: 700;
    color: #2c3e50;
    text-decoration: none;
}

.navbar-brand a i {
    color: #3498db;
    font-size: 1.6rem;
}

/* ===== Hamburger Toggle Button ===== */
.navbar-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    padding: 8px 6px;
    cursor: pointer;
    z-index: 1000;
    transition: all 0.3s ease;
}

.navbar-toggle span {
    display: block;
    width: 28px;
    height: 3px;
    background: #2c3e50;
    border-radius: 3px;
    transition: all 0.3s ease;
    transform-origin: center;
}

.navbar-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
}

.navbar-toggle.active span:nth-child(2) {
    opacity: 0;
    transform: scale(0);
}

.navbar-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
}

.navbar-toggle:hover span {
    background: #3498db;
}

/* ===== Navbar Menu ===== */
.navbar-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.navbar-menu a {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #555;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.navbar-menu a i {
    font-size: 0.9rem;
}

.navbar-menu a:hover {
    background: #f0f0f0;
    color: #3498db;
}

/* ===== User Menu ===== */
.user-menu {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #eee;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #3498db;
}

.user-name {
    font-weight: 500;
    color: #2c3e50;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.4rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-small {
    padding: 0.3rem 0.8rem;
    font-size: 0.8rem;
}

.btn-danger {
    background: #e74c3c;
    color: #fff;
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

/* ===== Mobile Responsive ===== */
@media (max-width: 768px) {
    .navbar-toggle {
        display: flex;
    }
    
    .navbar-menu {
        display: none;
        flex-direction: column;
        width: 100%;
        padding: 1rem 0;
        gap: 0.5rem;
        border-top: 1px solid #eee;
        margin-top: 0.8rem;
    }
    
    .navbar-menu.open {
        display: flex;
    }
    
    .navbar-menu a {
        width: 100%;
        padding: 0.8rem 1rem;
        border-radius: 6px;
        justify-content: center;
    }
    
    .navbar-menu a:hover {
        background: #f0f0f0;
    }
    
    .user-menu {
        width: 100%;
        justify-content: center;
        padding: 0.8rem 1rem;
        flex-wrap: wrap;
        border: none;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 0.5rem;
    }
    
    .user-avatar {
        width: 36px;
        height: 36px;
    }
    
    .user-name {
        font-size: 0.95rem;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
        padding: 0.6rem 1rem;
    }
    
    .navbar-brand a {
        font-size: 1.2rem;
    }
}

/* ===== Tablet (769px - 1024px) ===== */
@media (min-width: 769px) and (max-width: 1024px) {
    .navbar-menu {
        gap: 0.5rem;
    }
    
    .navbar-menu a {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .user-menu {
        padding: 0.4rem 0.8rem;
    }
    
    .user-name {
        font-size: 0.9rem;
    }
}

/* ===== Small Mobile (320px - 480px) ===== */
@media (max-width: 480px) {
    .navbar {
        padding: 0.5rem 0;
    }
    
    .navbar-brand a {
        font-size: 1rem;
    }
    
    .navbar-brand a i {
        font-size: 1.2rem;
    }
    
    .navbar-toggle {
        padding: 6px 4px;
    }
    
    .navbar-toggle span {
        width: 22px;
        height: 2.5px;
    }
    
    .navbar-menu a {
        font-size: 0.9rem;
        padding: 0.6rem 0.8rem;
    }
    
    .user-menu {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .user-avatar {
        width: 30px;
        height: 30px;
    }
    
    .user-name {
        font-size: 0.85rem;
    }
}

/* ===== Animation for menu items ===== */
.navbar-menu a {
    transition: all 0.3s ease;
}

.navbar-menu.open a {
    animation: slideDown 0.3s ease forwards;
    opacity: 0;
    transform: translateY(-10px);
}

.navbar-menu.open a:nth-child(1) { animation-delay: 0.05s; }
.navbar-menu.open a:nth-child(2) { animation-delay: 0.1s; }
.navbar-menu.open a:nth-child(3) { animation-delay: 0.15s; }
.navbar-menu.open a:nth-child(4) { animation-delay: 0.2s; }
.navbar-menu.open a:nth-child(5) { animation-delay: 0.25s; }

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== Dark overlay when menu is open ===== */
.navbar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 998;
}

.navbar-overlay.active {
    display: block;
}

/* ===== Print Styles ===== */
@media print {
    .navbar {
        position: static;
        box-shadow: none;
    }
    
    .navbar-toggle {
        display: none !important;
    }
    
    .navbar-menu {
        display: flex !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'navbar-overlay';
    overlay.id = 'navbarOverlay';
    document.body.appendChild(overlay);
    
    function toggleMenu() {
        toggle.classList.toggle('active');
        menu.classList.toggle('open');
        overlay.classList.toggle('active');
        document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
    }
    
    function closeMenu() {
        toggle.classList.remove('active');
        menu.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Toggle menu on button click
    if (toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });
    }
    
    // Close menu on overlay click
    overlay.addEventListener('click', function() {
        closeMenu();
    });
    
    // Close menu on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menu.classList.contains('open')) {
            closeMenu();
        }
    });
    
    // Close menu on window resize (if switching to desktop)
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768 && menu.classList.contains('open')) {
                closeMenu();
            }
        }, 250);
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        const isToggle = toggle.contains(e.target);
        const isMenu = menu.contains(e.target);
        
        if (!isToggle && !isMenu && menu.classList.contains('open')) {
            closeMenu();
        }
    });
});
</script>