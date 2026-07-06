<?php
// admin/courses.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

checkAdmin();

$db = Database::getInstance();

// Get all courses with stats
$query = "
    SELECT 
        c.*,
        COUNT(DISTINCT e.id) as enrollment_count,
        COUNT(DISTINCT l.id) as lesson_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN lessons l ON c.id = l.course_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
";

$courses = $db->query($query);

$page_title = 'Manage Courses';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Manage Courses</h1>
        <a href="course_form.php?action=add" class="btn btn-primary">Add Course</a>
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
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>Lessons</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($courses->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">No courses found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($course['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . 'courses/' . $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="thumbnail">
                                <?php else: ?>
                                    <img src="<?php echo UPLOAD_URL . 'courses/default-course.jpg'; ?>" alt="Default" class="thumbnail">
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo $course['lesson_count']; ?></td>
                            <td><?php echo $course['enrollment_count']; ?></td>
                            <td>
                                <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-small btn-info">Lessons</a>
                                <a href="course_form.php?action=edit&id=<?php echo $course['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <button onclick="confirmDelete(<?php echo $course['id']; ?>)" class="btn btn-small btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(courseId) {
    if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
        window.location.href = 'delete_course.php?id=' + courseId;
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>