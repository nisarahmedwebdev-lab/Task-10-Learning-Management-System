<?php
// courses.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

checkStudent();

$db = Database::getInstance();
$user_id = getUserId();

// Get all courses that the student is not enrolled in
$query = "
    SELECT c.*, u.full_name as instructor_name,
           (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons
    FROM courses c
    JOIN users u ON c.created_by = u.id
    WHERE c.id NOT IN (
        SELECT course_id FROM enrollments WHERE user_id = ?
    )
    ORDER BY c.created_at DESC
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$available_courses = $stmt->get_result();
$stmt->close();

$page_title = 'Browse Courses';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="student-dashboard">
    <div class="container">
        <div class="dashboard-layout">
            <!-- Sidebar -->
            <div class="sidebar-wrapper">
                <?php include 'includes/student-sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="main-content-wrapper">
                <div class="section-header">
                    <div>
                        <h1><i class="fas fa-search"></i> Available Courses</h1>
                        <p style="color: #7f8c8d; margin-top: 5px;">Discover new courses to enhance your skills</p>
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> My Dashboard
                    </a>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($available_courses->num_rows === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle" style="color: #2ecc71;"></i>
                        <h3>All Caught Up!</h3>
                        <p>You're enrolled in all available courses. Check back later for new courses.</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Go to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <div class="course-grid">
                        <?php while ($course = $available_courses->fetch_assoc()): ?>
                            <div class="course-card">
                                <div class="course-image">
                                    <img src="<?php echo getCourseImage($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <div class="course-status status-not-started">
                                        <i class="fas fa-star"></i> Available
                                    </div>
                                </div>
                                <div class="course-content">
                                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?></p>
                                    <div class="course-meta">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                                        <span><i class="fas fa-video"></i> <?php echo $course['total_lessons']; ?> lessons</span>
                                    </div>
                                    <form method="POST" action="enroll.php" style="margin-top: 15px;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                            <i class="fas fa-plus-circle"></i> Enroll Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.course-meta {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #7f8c8d;
    margin-top: 10px;
}

.course-meta i {
    margin-right: 4px;
}
</style>

<?php include 'includes/footer.php'; ?>