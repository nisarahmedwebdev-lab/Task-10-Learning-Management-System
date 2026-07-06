<?php
// dashboard.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

checkStudent();

$db = Database::getInstance();
$user_id = getUserId();

// Get user data
$stmt = $db->prepare("SELECT full_name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get enrolled courses with progress - FIXED QUERY
$query = "
    SELECT 
        c.id, 
        c.title, 
        c.description, 
        c.image,
        COUNT(DISTINCT l.id) as total_lessons,
        COUNT(DISTINCT lp.id) as completed_lessons,
        ROUND(IFNULL(COUNT(DISTINCT lp.id) / NULLIF(COUNT(DISTINCT l.id), 0) * 100, 0)) as progress_percentage,
        MAX(e.enrolled_at) as enrolled_at
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
    WHERE e.user_id = ?
    GROUP BY c.id, c.title, c.description, c.image
    ORDER BY MAX(e.enrolled_at) DESC
";

$stmt = $db->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();
$stmt->close();

$page_title = 'Dashboard';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="dashboard-header">
        <div class="user-profile">
            <?php if ($user['profile_image']): ?>
                <img src="<?php echo UPLOAD_URL . 'users/' . $user['profile_image']; ?>" alt="Profile Image" class="profile-image">
            <?php else: ?>
                <img src="<?php echo UPLOAD_URL . 'users/default-user.png'; ?>" alt="Default Profile" class="profile-image">
            <?php endif; ?>
            <div class="user-info">
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        <a href="courses.php" class="btn btn-primary">Browse Courses</a>
    </div>
    
    <div class="dashboard-content">
        <h2>My Courses</h2>
        
        <?php if ($enrolled_courses->num_rows === 0): ?>
            <div class="empty-state">
                <p>You haven't enrolled in any courses yet.</p>
                <a href="courses.php" class="btn btn-primary">Browse Available Courses</a>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
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
                            <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                            <div class="course-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                </div>
                                <span><?php echo $course['progress_percentage']; ?>% Complete</span>
                                <span class="lessons-count"><?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> lessons</span>
                            </div>
                            <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Continue</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>