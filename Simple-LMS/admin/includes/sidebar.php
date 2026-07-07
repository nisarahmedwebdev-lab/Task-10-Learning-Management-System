<?php
// admin/includes/sidebar.php

// Determine base path
$base_path = '../';
$profile_image = $_SESSION['profile_image'] ?? 'default-user.png';

// Get first course ID for lessons link (if any courses exist)
$first_course_id = 0;
try {
    $db = Database::getInstance();
    $result = $db->query("SELECT id FROM courses ORDER BY id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $first_course_id = $row['id'];
    }
} catch (Exception $e) {
    // If no courses exist, keep 0
}
?>
<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-graduation-cap"></i>
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </div>
        <div class="sidebar-actions">
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="sidebar-close-btn" id="sidebarCloseBtn" title="Close Sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" data-tooltip="Dashboard">
                    <i class="fas fa-chart-pie"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' || basename($_SERVER['PHP_SELF']) == 'course_form.php' ? 'active' : ''; ?>">
                <a href="courses.php" data-tooltip="Courses">
                    <i class="fas fa-book"></i>
                    <span class="nav-text">Courses</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'lessons.php' || basename($_SERVER['PHP_SELF']) == 'lesson_form.php' ? 'active' : ''; ?>">
                <a href="<?php echo $first_course_id > 0 ? 'lessons.php?course_id=' . $first_course_id : 'courses.php'; ?>" data-tooltip="Lessons">
                    <i class="fas fa-video"></i>
                    <span class="nav-text">Lessons</span>
                </a>
            </li>
           <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
    <a href="students.php" data-tooltip="Students">
        <i class="fas fa-users"></i>
        <span class="nav-text">Students</span>
    </a>
</li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'enrollments.php' ? 'active' : ''; ?>">
    <a href="enrollments.php" data-tooltip="Enrollments">
        <i class="fas fa-credit-card"></i>
        <span class="nav-text">Enrollments</span>
    </a>
</li>
            <li>
                <a href="#" data-tooltip="Settings">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../logout.php" class="sidebar-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>
</div>

<!-- Mobile Toggle Button (3 lines) -->
<button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Desktop Toggle Button (appears when sidebar is collapsed) -->
<button class="desktop-toggle" id="desktopToggle" aria-label="Open Menu">
    <i class="fas fa-bars"></i>
</button>

<style>
/* ==================== SIDEBAR STYLES ==================== */

/* Mobile Toggle Button - 3 Lines */
.mobile-toggle {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 999;
    background: #2c3e50;
    border: none;
    padding: 12px 10px;
    border-radius: 8px;
    cursor: pointer;
    flex-direction: column;
    gap: 5px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.mobile-toggle span {
    display: block;
    width: 25px;
    height: 3px;
    background: #fff;
    border-radius: 3px;
    transition: all 0.3s ease;
    transform-origin: center;
}

.mobile-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 6px);
}

.mobile-toggle.active span:nth-child(2) {
    opacity: 0;
    transform: scale(0);
}

.mobile-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -6px);
}

.mobile-toggle:hover {
    background: #34495e;
}

/* Desktop Toggle Button */
.desktop-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 999;
    background: #2c3e50;
    color: #fff;
    border: none;
    padding: 12px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.desktop-toggle:hover {
    background: #34495e;
    transform: scale(1.05);
}

.desktop-toggle.visible {
    display: block !important;
}

/* Sidebar */
.admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #2c3e50;
    color: #fff;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
    overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 2px 0 20px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

/* Sidebar Collapsed State */
.admin-sidebar.collapsed {
    width: 70px;
}

.admin-sidebar.collapsed .brand-text,
.admin-sidebar.collapsed .sidebar-user-info,
.admin-sidebar.collapsed .nav-text,
.admin-sidebar.collapsed .user-role,
.admin-sidebar.collapsed .sidebar-footer .nav-text {
    display: none;
}

.admin-sidebar.collapsed .sidebar-user {
    justify-content: center;
    padding: 15px 10px;
}

.admin-sidebar.collapsed .sidebar-user-avatar {
    width: 40px;
    height: 40px;
}

.admin-sidebar.collapsed .sidebar-nav ul li a {
    justify-content: center;
    padding: 12px;
}

.admin-sidebar.collapsed .sidebar-nav ul li a i {
    font-size: 20px;
    margin: 0;
}

.admin-sidebar.collapsed .sidebar-footer a {
    justify-content: center;
    padding: 12px;
}

.admin-sidebar.collapsed .sidebar-footer a i {
    font-size: 20px;
}

.admin-sidebar.collapsed .sidebar-brand {
    justify-content: center;
}

.admin-sidebar.collapsed .sidebar-brand i {
    font-size: 24px;
}

.admin-sidebar.collapsed .sidebar-actions .sidebar-toggle-btn i {
    transform: rotate(180deg);
}

/* Sidebar open on mobile */
.admin-sidebar.active {
    left: 0 !important;
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    display: none;
}

.sidebar-overlay.active {
    display: block !important;
}

/* Sidebar Header */
.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 70px;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.sidebar-brand i {
    font-size: 28px;
    color: #3498db;
}

.sidebar-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.sidebar-toggle-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
}

.sidebar-toggle-btn:hover {
    background: rgba(255,255,255,0.2);
}

.sidebar-close-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    display: none;
}

.sidebar-close-btn:hover {
    background: rgba(231,76,60,0.3);
}

/* Sidebar Navigation */
.sidebar-nav {
    flex: 1;
    padding: 15px 0;
    overflow-y: auto;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav ul li {
    margin: 2px 0;
}

.sidebar-nav ul li a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 20px;
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    white-space: nowrap;
    position: relative;
}

.sidebar-nav ul li a:hover {
    background: rgba(255,255,255,0.05);
    color: #fff;
    border-left-color: #3498db;
}

.sidebar-nav ul li.active a {
    background: rgba(52,152,219,0.1);
    color: #fff;
    border-left-color: #3498db;
}

.sidebar-nav ul li a i {
    width: 20px;
    font-size: 18px;
    text-align: center;
    flex-shrink: 0;
}

.sidebar-nav ul li a .nav-text {
    font-size: 14px;
    font-weight: 500;
    transition: opacity 0.3s ease;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 15px 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    margin-top: auto;
}

.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 20px;
    color: #e74c3c;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sidebar-logout:hover {
    background: rgba(231,76,60,0.1);
    color: #ff6b6b;
}

.sidebar-logout i {
    width: 20px;
    font-size: 18px;
    text-align: center;
    flex-shrink: 0;
}

.sidebar-logout .nav-text {
    font-size: 14px;
    font-weight: 500;
}

/* Main Content */
.main-content {
    margin-left: 280px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding-top: 20px;
    min-height: 100vh;
}

.main-content.shifted {
    margin-left: 70px;
}

/* Scrollbar */
.admin-sidebar::-webkit-scrollbar {
    width: 5px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: #34495e;
    border-radius: 10px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: #4a6a8a;
}

/* Tooltip for collapsed sidebar */
.admin-sidebar.collapsed .sidebar-nav ul li a:hover::after {
    content: attr(data-tooltip);
    position: fixed;
    left: 80px;
    background: #2c3e50;
    color: #fff;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    white-space: nowrap;
    z-index: 1001;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.admin-sidebar.collapsed .sidebar-nav ul li a:hover::before {
    content: '';
    position: fixed;
    left: 72px;
    top: 50%;
    transform: translateY(-50%);
    border: 6px solid transparent;
    border-right-color: #2c3e50;
    z-index: 1001;
}

/* ==================== RESPONSIVE STYLES ==================== */

/* Mobile & Tablet (up to 1024px) */
@media (max-width: 1024px) {
    .admin-sidebar {
        left: -280px;
        width: 280px;
    }
    
    .admin-sidebar.active {
        left: 0 !important;
    }
    
    .admin-sidebar.collapsed {
        width: 280px;
        left: -280px;
    }
    
    .admin-sidebar.collapsed.active {
        left: 0 !important;
    }
    
    .admin-sidebar.collapsed .brand-text,
    .admin-sidebar.collapsed .sidebar-user-info,
    .admin-sidebar.collapsed .nav-text,
    .admin-sidebar.collapsed .user-role,
    .admin-sidebar.collapsed .sidebar-footer .nav-text {
        display: block !important;
    }
    
    .admin-sidebar.collapsed .sidebar-user {
        padding: 20px;
        justify-content: flex-start;
    }
    
    .admin-sidebar.collapsed .sidebar-user-avatar {
        width: 50px;
        height: 50px;
    }
    
    .admin-sidebar.collapsed .sidebar-nav ul li a {
        justify-content: flex-start;
        padding: 12px 20px;
    }
    
    .admin-sidebar.collapsed .sidebar-nav ul li a i {
        font-size: 18px;
        margin: 0;
    }
    
    .admin-sidebar.collapsed .sidebar-footer a {
        justify-content: flex-start;
        padding: 12px 20px;
    }
    
    .admin-sidebar.collapsed .sidebar-footer a i {
        font-size: 18px;
    }
    
    .admin-sidebar.collapsed .sidebar-brand {
        justify-content: flex-start;
    }
    
    .admin-sidebar.collapsed .sidebar-brand i {
        font-size: 28px;
    }
    
    .admin-sidebar.collapsed .sidebar-actions .sidebar-toggle-btn i {
        transform: rotate(0deg);
    }
    
    .mobile-toggle {
        display: flex !important;
    }
    
    .desktop-toggle {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
    
    .main-content.shifted {
        margin-left: 0 !important;
    }
    
    .sidebar-close-btn {
        display: block !important;
    }
    
    .sidebar-toggle-btn {
        display: none !important;
    }
}

/* Large Desktop (above 1024px) */
@media (min-width: 1025px) {
    .admin-sidebar {
        left: 0 !important;
    }
    
    .mobile-toggle {
        display: none !important;
    }
    
    .sidebar-close-btn {
        display: none !important;
    }
    
    .sidebar-toggle-btn {
        display: block !important;
    }
    
    .desktop-toggle {
        display: none;
    }
    
    .desktop-toggle.visible {
        display: block !important;
    }
}

/* Small Mobile (320px - 480px) */
@media (max-width: 480px) {
    .admin-sidebar {
        width: 280px;
        left: -100%;
    }
    
    .admin-sidebar.active {
        left: 0 !important;
    }
    
    .admin-sidebar.collapsed {
        width: 280px;
        left: -100%;
    }
    
    .admin-sidebar.collapsed.active {
        left: 0 !important;
    }
    
    .mobile-toggle {
        top: 12px;
        left: 12px;
        padding: 10px 8px;
    }
    
    .mobile-toggle span {
        width: 22px;
        height: 2.5px;
    }
}

/* Tablet (481px - 1024px) */
@media (min-width: 481px) and (max-width: 1024px) {
    .admin-sidebar {
        width: 280px;
        left: -280px;
    }
    
    .admin-sidebar.active {
        left: 0 !important;
    }
}

/* Print Styles */
@media print {
    .admin-sidebar,
    .mobile-toggle,
    .desktop-toggle,
    .sidebar-overlay {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mobileToggle = document.getElementById('mobileToggle');
    const desktopToggle = document.getElementById('desktopToggle');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const mainContent = document.querySelector('.main-content');
    
    // Check if sidebar was collapsed on desktop
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && window.innerWidth >= 1025) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('shifted');
        if (desktopToggle) {
            desktopToggle.classList.add('visible');
        }
    }
    
    // Toggle sidebar collapse (desktop only)
    function toggleCollapse() {
        const isDesktop = window.innerWidth >= 1025;
        if (!isDesktop) return;
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('shifted');
        
        // Show/hide desktop toggle button
        if (desktopToggle) {
            if (sidebar.classList.contains('collapsed')) {
                desktopToggle.classList.add('visible');
            } else {
                desktopToggle.classList.remove('visible');
            }
        }
        
        // Save state
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
    
    // Open sidebar (mobile)
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        mobileToggle.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Close sidebar (mobile)
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        mobileToggle.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Toggle sidebar (mobile)
    function toggleSidebar() {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }
    
    // ====== EVENT LISTENERS ======
    
    // Mobile toggle (3 lines)
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Desktop toggle (when collapsed)
    if (desktopToggle) {
        desktopToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleCollapse();
        });
    }
    
    // Sidebar toggle button (inside sidebar)
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleCollapse();
        });
    }
    
    // Close button (inside sidebar)
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSidebar();
        });
    }
    
    // Overlay click
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            closeSidebar();
        });
    }
    
    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (sidebar.classList.contains('active')) {
                closeSidebar();
            }
        }
    });
    
    // ====== WINDOW RESIZE HANDLER ======
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const isDesktop = window.innerWidth >= 1025;
            
            if (isDesktop) {
                // Desktop mode - close mobile overlay
                closeSidebar();
                
                // Restore collapsed state
                const savedState = localStorage.getItem('sidebarCollapsed') === 'true';
                if (savedState) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('shifted');
                    if (desktopToggle) {
                        desktopToggle.classList.add('visible');
                    }
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('shifted');
                    if (desktopToggle) {
                        desktopToggle.classList.remove('visible');
                    }
                }
            } else {
                // Mobile mode - remove collapsed state
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('shifted');
                if (desktopToggle) {
                    desktopToggle.classList.remove('visible');
                }
            }
        }, 250);
    });
});
</script>