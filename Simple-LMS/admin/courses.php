<?php
// admin/courses.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

$db = Database::getInstance();

// Get all courses with stats
$query = "
    SELECT 
        c.id,
        c.title,
        c.description,
        c.image,
        c.created_at,
        COUNT(DISTINCT e.id) as enrollment_count,
        COUNT(DISTINCT l.id) as lesson_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN lessons l ON c.id = l.course_id
    GROUP BY c.id, c.title, c.description, c.image, c.created_at
    ORDER BY c.created_at DESC
";

$courses = $db->query($query);

$page_title = 'Manage Courses';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-book"></i> Manage Courses</h1>
                <p>View, add, edit, and delete courses</p>
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
        
        <div class="admin-table-toolbar">
            <div class="admin-search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search courses..." id="searchCourse">
            </div>
            <div class="admin-table-info">
                Total: <strong><?php echo $courses->num_rows; ?></strong> courses
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table" id="courseTable">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Lessons</th>
                        <th>Students</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($courses->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="admin-empty-state">
                                    <i class="fas fa-book-open" style="font-size: 48px;"></i>
                                    <p>No courses found. Create your first course!</p>
                                    <a href="course_form.php?action=add" class="btn btn-primary">
                                        <i class="fas fa-plus-circle"></i> Add Course
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo getCourseImage($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="thumbnail">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                </td>
                                <td>
                                    <span class="admin-badge admin-badge-info">
                                        <i class="fas fa-video"></i> <?php echo $course['lesson_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-badge admin-badge-success">
                                        <i class="fas fa-users"></i> <?php echo $course['enrollment_count']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                <td>
                                    <div class="admin-action-buttons">
                                        <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-small btn-info" title="Manage Lessons">
                                            <i class="fas fa-video"></i>
                                        </a>
                                        <a href="course_form.php?action=edit&id=<?php echo $course['id']; ?>" class="btn btn-small btn-warning" title="Edit Course">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')" class="btn btn-small btn-danger" title="Delete Course">
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
    const searchInput = document.getElementById('searchCourse');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#courseTable tbody tr');
            
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

// Delete Course with SweetAlert
function deleteCourse(courseId, courseTitle) {
    Swal.fire({
        title: 'Delete Course?',
        html: `Are you sure you want to delete <strong>"${courseTitle}"</strong>?<br><br>This will also delete all lessons and enrollments.`,
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
                window.location.href = 'delete_course.php?id=' + courseId;
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>