<?php
// admin/lessons_list.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

$db = Database::getInstance();

// Get all courses with lessons count
$courses_query = "
    SELECT c.id, c.title, c.image,
           COUNT(l.id) as lesson_count
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id
    GROUP BY c.id, c.title, c.image
    ORDER BY c.title ASC
";

$courses = $db->query($courses_query);

$page_title = 'All Lessons';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container">
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-video"></i> All Lessons</h1>
            <p>View lessons from all courses</p>
        </div>
        <div class="header-actions">
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
    
    <?php if ($courses->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fas fa-video-slash" style="font-size: 48px; color: #ddd;"></i>
            <p>No courses found. Create a course first to add lessons.</p>
            <a href="course_form.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Add Course
            </a>
        </div>
    <?php else: ?>
        <div class="course-lessons-grid">
            <?php while ($course = $courses->fetch_assoc()): ?>
                <div class="course-lesson-card">
                    <div class="course-lesson-header">
                        <div class="course-info">
                            <?php if ($course['image']): ?>
                                <img src="<?php echo getCourseImage($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-thumb">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        </div>
                        <span class="badge badge-info">
                            <i class="fas fa-video"></i> <?php echo $course['lesson_count']; ?> Lessons
                        </span>
                    </div>
                    <div class="course-lesson-body">
                        <?php
                        // Get lessons for this course
                        $stmt = $db->prepare("SELECT id, title, order_number, video_url FROM lessons WHERE course_id = ? ORDER BY order_number ASC");
                        $stmt->bind_param("i", $course['id']);
                        $stmt->execute();
                        $lessons = $stmt->get_result();
                        ?>
                        
                        <?php if ($lessons->num_rows === 0): ?>
                            <p class="no-lessons"><i class="fas fa-info-circle"></i> No lessons added yet.</p>
                            <a href="lesson_form.php?course_id=<?php echo $course['id']; ?>&action=add" class="btn btn-small btn-primary">
                                <i class="fas fa-plus-circle"></i> Add Lesson
                            </a>
                        <?php else: ?>
                            <ul class="lesson-list">
                                <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                    <li>
                                        <span class="lesson-order">#<?php echo $lesson['order_number']; ?></span>
                                        <span class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></span>
                                        <?php if ($lesson['video_url']): ?>
                                            <span class="badge badge-success"><i class="fas fa-play"></i> Video</span>
                                        <?php endif; ?>
                                        <div class="lesson-actions">
                                            <a href="lesson_form.php?course_id=<?php echo $course['id']; ?>&action=edit&id=<?php echo $lesson['id']; ?>" class="btn btn-small btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteLesson(<?php echo $lesson['id']; ?>, <?php echo $course['id']; ?>, '<?php echo htmlspecialchars($lesson['title']); ?>')" class="btn btn-small btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                            <div class="course-lesson-footer">
                                <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View All Lessons
                                </a>
                                <a href="lesson_form.php?course_id=<?php echo $course['id']; ?>&action=add" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Add Lesson
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php $stmt->close(); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.course-lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.course-lesson-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.course-lesson-card:hover {
    transform: translateY(-3px);
}

.course-lesson-header {
    padding: 20px 25px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.course-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.course-thumb {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
}

.course-lesson-header h3 {
    margin: 0;
    font-size: 18px;
}

.course-lesson-body {
    padding: 20px 25px;
}

.no-lessons {
    color: #999;
    text-align: center;
    padding: 15px 0;
}

.lesson-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.lesson-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.3s;
}

.lesson-list li:hover {
    background: #f8f9fa;
}

.lesson-list li:last-child {
    border-bottom: none;
}

.lesson-order {
    background: #e9ecef;
    color: #6c757d;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.lesson-title {
    flex: 1;
    font-weight: 500;
}

.lesson-actions {
    display: flex;
    gap: 5px;
}

.course-lesson-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

@media (max-width: 768px) {
    .course-lessons-grid {
        grid-template-columns: 1fr;
    }
    
    .course-lesson-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .lesson-list li {
        flex-wrap: wrap;
    }
    
    .lesson-actions {
        margin-left: auto;
    }
}
</style>

<script>
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

// Show success/error messages from PHP
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($flash = getFlashMessage()): ?>
        Swal.fire({
            icon: '<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>',
            title: '<?php echo $flash['type'] === 'success' ? 'Success!' : 'Error!'; ?>',
            text: '<?php echo htmlspecialchars($flash['message']); ?>',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: true
        });
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>