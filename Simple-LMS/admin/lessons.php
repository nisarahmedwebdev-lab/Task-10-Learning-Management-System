<?php
// admin/lessons.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

checkAdmin();

$db = Database::getInstance();
$course_id = (int)($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    setFlashMessage('error', 'Invalid course ID.');
    redirect('courses.php');
}

// Get course details
$stmt = $db->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    setFlashMessage('error', 'Course not found.');
    redirect('courses.php');
}

// Get lessons
$stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_number ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$lessons = $stmt->get_result();
$stmt->close();

$page_title = 'Manage Lessons - ' . htmlspecialchars($course['title']);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Manage Lessons</h1>
        <div class="header-actions">
            <a href="lesson_form.php?course_id=<?php echo $course_id; ?>&action=add" class="btn btn-primary">Add Lesson</a>
            <a href="courses.php" class="btn btn-secondary">Back to Courses</a>
        </div>
    </div>
    
    <div class="course-info-banner">
        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
    </div>
    
    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Title</th>
                    <th>Content Preview</th>
                    <th>Video</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lessons->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">No lessons found for this course.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($lesson = $lessons->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $lesson['order_number']; ?></td>
                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($lesson['content'], 0, 50)) . '...'; ?></td>
                            <td><?php echo $lesson['video_url'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <a href="lesson_form.php?course_id=<?php echo $course_id; ?>&action=edit&id=<?php echo $lesson['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <button onclick="confirmDelete(<?php echo $lesson['id']; ?>, <?php echo $course_id; ?>)" class="btn btn-small btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(lessonId, courseId) {
    if (confirm('Are you sure you want to delete this lesson? This action cannot be undone.')) {
        window.location.href = 'delete_lesson.php?id=' + lessonId + '&course_id=' + courseId;
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>