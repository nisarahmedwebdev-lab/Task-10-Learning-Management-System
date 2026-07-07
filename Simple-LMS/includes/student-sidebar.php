<?php
// includes/student-sidebar.php

// Ensure functions are loaded
if (!function_exists('getUserImage')) {
    require_once __DIR__ . '/functions.php';
}

$profile_image = $_SESSION['profile_image'] ?? 'default-user.png';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="student-sidebar">
    <div class="sidebar-user">
        <img src="<?php echo getUserImage($profile_image); ?>" alt="Profile">
        <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Student'); ?></h4>
        <span><i class="fas fa-user-graduate"></i> Student</span>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
                <a href="courses.php">
                    <i class="fas fa-book"></i>
                    <span>Browse Courses</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'course_detail.php' || $current_page == 'lesson.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-video"></i>
                    <span>My Learning</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-trophy"></i>
                    <span>Achievements</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="logout-item">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<style>
/* ==================== STUDENT SIDEBAR STYLES ==================== */

.student-sidebar {
    width: 100%;
    background: #fff;
    border-radius: 16px;
    padding: 25px 20px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

/* Sidebar User Profile */
.sidebar-user {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #ecf0f1;
    margin-bottom: 20px;
}

.sidebar-user img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #667eea;
    margin-bottom: 12px;
    transition: transform 0.3s ease;
}

.sidebar-user img:hover {
    transform: scale(1.05);
}

.sidebar-user h4 {
    margin: 0 0 4px 0;
    color: #2c3e50;
    font-size: 18px;
}

.sidebar-user span {
    color: #7f8c8d;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.sidebar-user span i {
    color: #667eea;
}

/* Sidebar Navigation */
.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav ul li {
    margin: 4px 0;
}

.sidebar-nav ul li a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    color: #7f8c8d;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-weight: 500;
    position: relative;
}

.sidebar-nav ul li a:hover {
    background: #f8f9fa;
    color: #2c3e50;
    transform: translateX(4px);
}

.sidebar-nav ul li.active a {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.sidebar-nav ul li.active a i {
    color: #fff;
}

.sidebar-nav ul li a i {
    width: 22px;
    font-size: 18px;
    text-align: center;
    color: #95a5a6;
    transition: color 0.3s ease;
}

.sidebar-nav ul li a:hover i {
    color: #667eea;
}

.sidebar-nav ul li.active a i {
    color: #fff;
}

.sidebar-nav ul li a span {
    font-size: 15px;
}

/* Logout Item */
.sidebar-nav ul li.logout-item {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
}

.sidebar-nav ul li.logout-item a {
    color: #e74c3c;
}

.sidebar-nav ul li.logout-item a i {
    color: #e74c3c;
}

.sidebar-nav ul li.logout-item a:hover {
    background: #fde8e8;
    color: #c0392b;
}

.sidebar-nav ul li.logout-item a:hover i {
    color: #c0392b;
}

/* Responsive */
@media (min-width: 769px) {
    .student-sidebar {
        position: sticky;
        top: 90px;
        width: 260px;
        flex-shrink: 0;
    }
}

@media (max-width: 768px) {
    .student-sidebar {
        width: 100%;
        margin-bottom: 20px;
        padding: 20px 15px;
    }
    
    .sidebar-user img {
        width: 60px;
        height: 60px;
    }
    
    .sidebar-user h4 {
        font-size: 16px;
    }
    
    .sidebar-nav ul li a {
        padding: 10px 14px;
        font-size: 14px;
    }
    
    .sidebar-nav ul li a i {
        font-size: 16px;
        width: 20px;
    }
}

@media (max-width: 480px) {
    .student-sidebar {
        padding: 15px 12px;
    }
    
    .sidebar-user img {
        width: 50px;
        height: 50px;
    }
    
    .sidebar-user h4 {
        font-size: 14px;
    }
    
    .sidebar-nav ul li a {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .sidebar-nav ul li a i {
        font-size: 14px;
        width: 18px;
    }
}
</style>