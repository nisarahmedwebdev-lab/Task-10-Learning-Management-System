<?php
// admin/lessons.php

// ============================================
// FIXED: Correct paths using __DIR__
// ============================================
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

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
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-video"></i> Manage Lessons</h1>
                <p>Course: <strong><?php echo htmlspecialchars($course['title']); ?></strong></p>
            </div>
            <div class="header-actions">
                <a href="lesson_form.php?course_id=<?php echo $course_id; ?>&action=add" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add Lesson
                </a>
                <a href="courses.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>
        
        <?php if ($flash = getFlashMessage()): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-table-toolbar">
            <div class="admin-search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search lessons..." id="searchLesson">
            </div>
            <div class="admin-table-info">
                Total: <strong><?php echo $lessons->num_rows; ?></strong> lessons
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table" id="lessonTable">
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
                            <td colspan="5" class="text-center">
                                <div class="admin-empty-state">
                                    <i class="fas fa-video-slash" style="font-size: 48px;"></i>
                                    <p>No lessons found for this course.</p>
                                    <a href="lesson_form.php?course_id=<?php echo $course_id; ?>&action=add" class="btn btn-primary">
                                        <i class="fas fa-plus-circle"></i> Add First Lesson
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($lesson = $lessons->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="admin-badge admin-badge-primary"><?php echo $lesson['order_number']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars(substr(strip_tags($lesson['content']), 0, 50)) . '...'; ?></td>
                                <td>
                                    <?php if ($lesson['video_url']): ?>
                                        <span class="admin-badge admin-badge-success">
                                            <i class="fas fa-check"></i> Yes
                                        </span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-danger">
                                            <i class="fas fa-times"></i> No
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="admin-action-buttons">
                                        <a href="lesson_form.php?course_id=<?php echo $course_id; ?>&action=edit&id=<?php echo $lesson['id']; ?>" class="btn btn-small btn-warning" title="Edit Lesson">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteLesson(<?php echo $lesson['id']; ?>, <?php echo $course_id; ?>, '<?php echo htmlspecialchars($lesson['title']); ?>')" class="btn btn-small btn-danger" title="Delete Lesson">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchLesson');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#lessonTable tbody tr');
            
            rows.forEach(row => {
                const title = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
                if (title.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

// Delete Lesson with SweetAlert
function deleteLesson(lessonId, courseId, lessonTitle) {
    Swal.fire({
        title: 'Delete Lesson?',
        html: `Are you sure you want to delete <strong>"${lessonTitle}"</strong>?<br><br>This will also delete all student progress for this lesson.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                window.location.href = 'delete_lesson.php?id=' + lessonId + '&course_id=' + courseId;
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>