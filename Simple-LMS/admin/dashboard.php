<?php
// admin/dashboard.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

$db = Database::getInstance();

// Get statistics
$stats = [];

// Total students
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
$stats['students'] = $result->fetch_assoc()['count'];

// Total courses
$result = $db->query("SELECT COUNT(*) as count FROM courses");
$stats['courses'] = $result->fetch_assoc()['count'];

// Total enrollments
$result = $db->query("SELECT COUNT(*) as count FROM enrollments");
$stats['enrollments'] = $result->fetch_assoc()['count'];

// Total lessons
$result = $db->query("SELECT COUNT(*) as count FROM lessons");
$stats['lessons'] = $result->fetch_assoc()['count'];

// Recent enrollments
$recent_enrollments = $db->query("
    SELECT e.enrolled_at, u.full_name, c.title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");

$page_title = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-chart-pie"></i> Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <div class="header-actions">
                <a href="course_form.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Course
                </a>
            </div>
        </div>
        
        <?php if ($flash = getFlashMessage()): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #3498db;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['students']; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #2ecc71;">
                    <i class="fas fa-book"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['courses']; ?></h3>
                    <p>Total Courses</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #f39c12;">
                    <i class="fas fa-video"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['lessons']; ?></h3>
                    <p>Total Lessons</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #e74c3c;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['enrollments']; ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>
        </div>
        
        <div class="admin-actions">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="admin-action-grid">
                <a href="courses.php" class="admin-action-card">
                    <div class="admin-action-icon" style="background: #3498db;">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Manage Courses</h3>
                    <p>Add, edit, or delete courses</p>
                </a>
                <a href="course_form.php?action=add" class="admin-action-card">
                    <div class="admin-action-icon" style="background: #2ecc71;">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3>Add New Course</h3>
                    <p>Create a new course</p>
                </a>
                <a href="students.php" class="admin-action-card">
                    <div class="admin-action-icon" style="background: #f39c12;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Manage Students</h3>
                    <p>View and manage students</p>
                </a>
                <a href="enrollments.php" class="admin-action-card">
                    <div class="admin-action-icon" style="background: #9b59b6;">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Enrollments</h3>
                    <p>Manage student enrollments</p>
                </a>
            </div>
        </div>
        
        <?php if ($recent_enrollments && $recent_enrollments->num_rows > 0): ?>
            <div class="admin-recent-activity">
                <h2><i class="fas fa-clock"></i> Recent Enrollments</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Student</th>
                                <th><i class="fas fa-book"></i> Course</th>
                                <th><i class="fas fa-calendar"></i> Enrolled At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_enrollments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['enrolled_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>