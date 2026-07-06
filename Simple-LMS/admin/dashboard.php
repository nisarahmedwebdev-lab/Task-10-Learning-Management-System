<?php
// admin/dashboard.php

// Fix paths - go up one level
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

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

// Recent activity - latest enrollments
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
?>

<div class="container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>
    
    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-content">
                <h3><?php echo $stats['students']; ?></h3>
                <p>Students</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3><?php echo $stats['courses']; ?></h3>
                <p>Courses</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo $stats['enrollments']; ?></h3>
                <p>Enrollments</p>
            </div>
        </div>
    </div>
    
    <div class="admin-actions">
        <h2>Quick Actions</h2>
        <div class="action-grid">
            <a href="courses.php" class="action-card">
                <span class="action-icon">📖</span>
                <h3>Manage Courses</h3>
                <p>Add, edit, or delete courses</p>
            </a>
            <a href="course_form.php" class="action-card">
                <span class="action-icon">➕</span>
                <h3>Add New Course</h3>
                <p>Create a new course</p>
            </a>
        </div>
    </div>
    
    <?php if ($recent_enrollments->num_rows > 0): ?>
        <div class="recent-activity">
            <h2>Recent Enrollments</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Enrolled At</th>
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
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>