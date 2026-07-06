<?php
// course_detail.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

checkStudent();

$db = Database::getInstance();
$user_id = getUserId();
$course_id = (int)($_GET['id'] ?? 0);

if ($course_id <= 0) {
    setFlashMessage('error', 'Invalid course ID.');
    redirect('dashboard.php');
}

// Check if student is enrolled
$stmt = $db->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment = $stmt->get_result();
$stmt->close();

if ($enrollment->num_rows === 0) {
    setFlashMessage('error', 'You are not enrolled in this course.');
    redirect('courses.php');
}

// Get course details
$stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get lessons with progress
$query = "
    SELECT 
        l.*,
        lp.completed,
        lp.completed_at,
        COUNT(DISTINCT lp2.id) as total_completed
    FROM lessons l
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
    LEFT JOIN lesson_progress lp2 ON l.course_id = (
        SELECT course_id FROM lessons WHERE id = l.id
    ) AND lp2.user_id = ?
    WHERE l.course_id = ?
    GROUP BY l.id
    ORDER BY l.order_number ASC
";

$stmt = $db->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $course_id);
$stmt->execute();
$lessons = $stmt->get_result();
$stmt->close();

// Calculate course progress
$total_lessons = $lessons->num_rows;
$completed_lessons = 0;
while ($lesson = $lessons->fetch_assoc()) {
    if ($lesson['completed']) {
        $completed_lessons++;
    }
    $lessons_data[] = $lesson;
}
// Reset pointer
$lessons->data_seek(0);

$progress_percentage = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;

$page_title = htmlspecialchars($course['title']);
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="course-detail-header">
        <div class="course-info">
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p><?php echo htmlspecialchars($course['description']); ?></p>
            <div class="course-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                </div>
                <span><?php echo $progress_percentage; ?>% Complete</span>
                <span class="lessons-count"><?php echo $completed_lessons; ?>/<?php echo $total_lessons; ?> lessons</span>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="lessons-list">
        <h2>Course Content</h2>
        
        <?php if ($lessons->num_rows === 0): ?>
            <div class="empty-state">
                <p>No lessons available for this course yet.</p>
            </div>
        <?php else: ?>
            <?php while ($lesson = $lessons->fetch_assoc()): ?>
                <div class="lesson-item">
                    <div class="lesson-header">
                        <div class="lesson-info">
                            <span class="lesson-order">Lesson <?php echo $lesson['order_number']; ?></span>
                            <h3>
                                <a href="lesson.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>">
                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                </a>
                            </h3>
                        </div>
                        <div class="lesson-actions">
                            <form method="POST" action="update_progress.php">
                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <input type="hidden" name="completed" value="<?php echo $lesson['completed'] ? '0' : '1'; ?>">
                                <button type="submit" class="btn <?php echo $lesson['completed'] ? 'btn-success' : 'btn-secondary'; ?>">
                                    <?php echo $lesson['completed'] ? '✓ Completed' : 'Mark Complete'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($lesson['content'])): ?>
                        <div class="lesson-preview">
                            <?php echo htmlspecialchars(substr($lesson['content'], 0, 200)) . '...'; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>