<?php
// admin/students.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

$db = Database::getInstance();

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "
    SELECT 
        u.id,
        u.full_name,
        u.email,
        u.profile_image,
        u.created_at,
        COUNT(DISTINCT e.course_id) as enrolled_courses,
        COUNT(DISTINCT lp.lesson_id) as completed_lessons,
        (SELECT COUNT(*) FROM lessons l WHERE l.course_id IN (SELECT course_id FROM enrollments WHERE user_id = u.id)) as total_lessons
    FROM users u
    LEFT JOIN enrollments e ON u.id = e.user_id
    LEFT JOIN lesson_progress lp ON u.id = lp.user_id AND lp.completed = 1
    WHERE u.role = 'student'
";

if (!empty($search)) {
    $search = '%' . $search . '%';
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
}

$query .= " GROUP BY u.id, u.full_name, u.email, u.profile_image, u.created_at ORDER BY u.created_at DESC";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $stmt->bind_param("ss", $search, $search);
}

$stmt->execute();
$students = $stmt->get_result();
$stmt->close();

// Get statistics
$stats = [];

// Total students
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
$stats['total'] = $result->fetch_assoc()['count'];

// Active students (with at least one enrollment)
$result = $db->query("SELECT COUNT(DISTINCT user_id) as count FROM enrollments");
$stats['active'] = $result->fetch_assoc()['count'];

// Total enrollments
$result = $db->query("SELECT COUNT(*) as count FROM enrollments");
$stats['enrollments'] = $result->fetch_assoc()['count'];

$page_title = 'Manage Students';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-users"></i> Manage Students</h1>
                <p>View and manage all registered students</p>
            </div>
        </div>
        
        <?php if ($flash = getFlashMessage()): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #3498db;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #2ecc71;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['active']; ?></h3>
                    <p>Active Students</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #f39c12;">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['enrollments']; ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="admin-table-toolbar">
            <div class="admin-search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search students by name or email..." id="searchStudent" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="admin-table-info">
                Total: <strong><?php echo $students->num_rows; ?></strong> students
            </div>
        </div>
        
        <!-- Students Table -->
        <div class="table-responsive">
            <table class="table" id="studentTable">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Enrolled Courses</th>
                        <th>Progress</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students->num_rows === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="admin-empty-state">
                                    <i class="fas fa-user-slash" style="font-size: 48px;"></i>
                                    <p>No students found.</p>
                                    <?php if (!empty($search)): ?>
                                        <p>Try adjusting your search criteria.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($student = $students->fetch_assoc()): 
                            $progress = $student['total_lessons'] > 0 
                                ? round(($student['completed_lessons'] / $student['total_lessons']) * 100) 
                                : 0;
                        ?>
                            <tr>
                                <td>
                                    <img src="<?php echo getUserImage($student['profile_image']); ?>" alt="<?php echo htmlspecialchars($student['full_name']); ?>" class="student-avatar">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-info">
                                        <i class="fas fa-book"></i> <?php echo $student['enrolled_courses']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="progress-cell">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo $progress; ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <div class="admin-action-buttons">
                                        <button onclick="viewStudent(<?php echo $student['id']; ?>)" class="btn btn-small btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')" class="btn btn-small btn-danger" title="Delete Student">
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

<!-- View Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate"></i> Student Details
                </h5>
                <button type="button" class="close" onclick="closeModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="studentDetails">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading student details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Student Avatar */
.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eee;
}

/* Progress Cell */
.progress-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 100px;
}

.progress-cell .progress-bar {
    flex: 1;
    height: 6px;
    background: #ecf0f1;
    border-radius: 3px;
    overflow: hidden;
}

.progress-cell .progress-fill {
    height: 100%;
    background: #2ecc71;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 12px;
    font-weight: 600;
    color: #2c3e50;
    min-width: 40px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    overflow-y: auto;
}

.modal.show {
    display: block;
}

.modal-dialog {
    position: relative;
    width: 90%;
    max-width: 800px;
    margin: 30px auto;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-content {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    overflow: hidden;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h5 {
    margin: 0;
    font-size: 20px;
}

.modal-header .close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
    padding: 0 10px;
}

.modal-header .close:hover {
    color: #333;
}

.modal-body {
    padding: 25px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #eee;
    text-align: right;
}

/* Student Detail Styles */
.student-profile {
    display: flex;
    align-items: center;
    gap: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.student-profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #3498db;
}

.student-profile-info h3 {
    margin: 0 0 5px 0;
    font-size: 22px;
}

.student-profile-info p {
    margin: 5px 0;
    color: #666;
}

.student-profile-info p i {
    margin-right: 8px;
    color: #3498db;
    width: 20px;
}

.student-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin: 20px 0;
}

.student-stat-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.student-stat-item h4 {
    margin: 0 0 5px 0;
    font-size: 24px;
    color: #2c3e50;
}

.student-stat-item p {
    margin: 0;
    color: #7f8c8d;
    font-size: 13px;
}

.student-courses-list {
    margin-top: 20px;
}

.student-courses-list h4 {
    margin-bottom: 15px;
}

.student-course-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.student-course-item .course-name {
    font-weight: 500;
}

.student-course-item .course-progress {
    display: flex;
    align-items: center;
    gap: 15px;
}

.student-course-item .course-progress .progress-bar {
    width: 150px;
    height: 6px;
    background: #ecf0f1;
    border-radius: 3px;
    overflow: hidden;
}

.student-course-item .course-progress .progress-fill {
    height: 100%;
    background: #2ecc71;
}

.student-course-item .course-status {
    font-size: 12px;
    font-weight: 500;
    padding: 2px 12px;
    border-radius: 12px;
}

.student-course-item .course-status.completed {
    background: #d4edda;
    color: #155724;
}

.student-course-item .course-status.in-progress {
    background: #fff3cd;
    color: #856404;
}

.student-course-item .course-status.not-started {
    background: #f8d7da;
    color: #721c24;
}
</style>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStudent');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const searchValue = searchInput.value.trim();
                if (searchValue) {
                    window.location.href = 'students.php?search=' + encodeURIComponent(searchValue);
                } else {
                    window.location.href = 'students.php';
                }
            }, 500);
        });
    }
});

// View Student Details
function viewStudent(studentId) {
    const modal = document.getElementById('studentModal');
    const details = document.getElementById('studentDetails');
    
    modal.classList.add('show');
    details.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading student details...</p>
        </div>
    `;
    
    fetch('get_student_details.php?id=' + studentId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                details.innerHTML = renderStudentDetails(data.student);
            } else {
                details.innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            details.innerHTML = `
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> Failed to load student details.
                </div>
            `;
        });
}

// Render Student Details
function renderStudentDetails(student) {
    let coursesHtml = '';
    if (student.courses && student.courses.length > 0) {
        student.courses.forEach(course => {
            const statusClass = course.progress === 100 ? 'completed' : 
                              course.progress > 0 ? 'in-progress' : 'not-started';
            const statusText = course.progress === 100 ? 'Completed' : 
                              course.progress > 0 ? 'In Progress' : 'Not Started';
            
            coursesHtml += `
                <div class="student-course-item">
                    <span class="course-name">${course.title}</span>
                    <div class="course-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${course.progress}%"></div>
                        </div>
                        <span class="course-status ${statusClass}">${statusText} (${course.progress}%)</span>
                    </div>
                </div>
            `;
        });
    } else {
        coursesHtml = '<p class="text-center" style="color: #999;">No courses enrolled yet.</p>';
    }
    
    return `
        <div class="student-profile">
            <img src="${student.profile_image}" alt="${student.full_name}" class="student-profile-image">
            <div class="student-profile-info">
                <h3>${student.full_name}</h3>
                <p><i class="fas fa-envelope"></i> ${student.email}</p>
                <p><i class="fas fa-calendar"></i> Joined: ${student.joined_date}</p>
                <p><i class="fas fa-credit-card"></i> Enrollments: ${student.enrolled_courses}</p>
            </div>
        </div>
        
        <div class="student-stats-grid">
            <div class="student-stat-item">
                <h4>${student.total_lessons}</h4>
                <p>Total Lessons</p>
            </div>
            <div class="student-stat-item">
                <h4>${student.completed_lessons}</h4>
                <p>Completed Lessons</p>
            </div>
            <div class="student-stat-item">
                <h4>${student.overall_progress}%</h4>
                <p>Overall Progress</p>
            </div>
        </div>
        
        <div class="student-courses-list">
            <h4><i class="fas fa-book"></i> Enrolled Courses</h4>
            ${coursesHtml}
        </div>
    `;
}

// Close Modal
function closeModal() {
    document.getElementById('studentModal').classList.remove('show');
}

// Close modal on outside click
document.getElementById('studentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Delete Student with SweetAlert
function deleteStudent(studentId, studentName) {
    Swal.fire({
        title: 'Delete Student?',
        html: `Are you sure you want to delete <strong>"${studentName}"</strong>?<br><br>This will also delete all enrollments and progress.`,
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
                window.location.href = 'delete_student.php?id=' + studentId;
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>