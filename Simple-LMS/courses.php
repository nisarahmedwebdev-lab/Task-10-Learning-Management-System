<?php
// courses.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

checkStudent();

$db = Database::getInstance();
$user_id = getUserId();

// Get all courses that the student is not enrolled in
$query = "
    SELECT c.*, u.full_name as instructor_name
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

<div class="container">
    <div class="page-header">
        <h1>Available Courses</h1>
        <a href="dashboard.php" class="btn btn-secondary">My Dashboard</a>
    </div>
    
    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($available_courses->num_rows === 0): ?>
        <div class="empty-state">
            <p>No courses available at the moment.</p>
            <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
        </div>
    <?php else: ?>
        <div class="course-grid">
            <?php while ($course = $available_courses->fetch_assoc()): ?>
                <div class="course-card">
                    <div class="course-image">
                        <?php if ($course['image']): ?>
                            <img src="<?php echo UPLOAD_URL . 'courses/' . $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php else: ?>
                            <img src="<?php echo UPLOAD_URL . 'courses/default-course.jpg'; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?></p>
                        <p class="instructor">By: <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                        <form method="POST" action="enroll.php">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <button type="submit" class="btn btn-success">Enroll Now</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>