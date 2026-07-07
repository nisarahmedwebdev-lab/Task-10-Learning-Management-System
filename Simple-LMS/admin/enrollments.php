<?php
// admin/enrollments.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

$db = Database::getInstance();

// Get filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_filter = isset($_GET['course']) ? (int)$_GET['course'] : 0;
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build query
$query = "
    SELECT 
        e.id,
        e.enrolled_at,
        u.id as user_id,
        u.full_name as student_name,
        u.email as student_email,
        u.profile_image,
        c.id as course_id,
        c.title as course_title,
        c.image as course_image,
        COUNT(DISTINCT l.id) as total_lessons,
        COUNT(DISTINCT lp.id) as completed_lessons,
        ROUND(IFNULL(COUNT(DISTINCT lp.id) / NULLIF(COUNT(DISTINCT l.id), 0) * 100, 0)) as progress
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = u.id
    WHERE u.role = 'student'
";

// Apply filters
if (!empty($search)) {
    $search_param = '%' . $search . '%';
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR c.title LIKE ?)";
}

if ($course_filter > 0) {
    $query .= " AND c.id = ?";
}

$query .= " GROUP BY e.id, u.id, u.full_name, u.email, u.profile_image, c.id, c.title, c.image, e.enrolled_at";

// Apply status filter
if ($status_filter === 'completed') {
    $query .= " HAVING progress = 100";
} elseif ($status_filter === 'in-progress') {
    $query .= " HAVING progress > 0 AND progress < 100";
} elseif ($status_filter === 'not-started') {
    $query .= " HAVING progress = 0";
}

$query .= " ORDER BY e.enrolled_at DESC";

// Prepare statement with dynamic bindings
$stmt = $db->prepare($query);

if (!empty($search) && $course_filter > 0) {
    $stmt->bind_param("sssi", $search_param, $search_param, $search_param, $course_filter);
} elseif (!empty($search)) {
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
} elseif ($course_filter > 0) {
    $stmt->bind_param("i", $course_filter);
}

$stmt->execute();
$enrollments = $stmt->get_result();
$stmt->close();

// Get courses for filter dropdown
$courses = $db->query("SELECT id, title FROM courses ORDER BY title ASC");

// Get statistics
$stats = [];

// Total enrollments
$result = $db->query("SELECT COUNT(*) as count FROM enrollments");
$stats['total'] = $result->fetch_assoc()['count'];

// Completed enrollments
$result = $db->query("
    SELECT COUNT(*) as count 
    FROM enrollments e
    WHERE (
        SELECT COUNT(*) 
        FROM lessons l 
        WHERE l.course_id = e.course_id
    ) = (
        SELECT COUNT(*) 
        FROM lesson_progress lp 
        JOIN lessons l ON lp.lesson_id = l.id 
        WHERE lp.user_id = e.user_id 
        AND l.course_id = e.course_id
        AND lp.completed = 1
    )
");
$stats['completed'] = $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;

// In-progress enrollments
$result = $db->query("
    SELECT COUNT(*) as count 
    FROM enrollments e
    WHERE (
        SELECT COUNT(*) 
        FROM lesson_progress lp 
        JOIN lessons l ON lp.lesson_id = l.id 
        WHERE lp.user_id = e.user_id 
        AND l.course_id = e.course_id
        AND lp.completed = 1
    ) > 0
    AND (
        SELECT COUNT(*) 
        FROM lesson_progress lp 
        JOIN lessons l ON lp.lesson_id = l.id 
        WHERE lp.user_id = e.user_id 
        AND l.course_id = e.course_id
        AND lp.completed = 1
    ) < (
        SELECT COUNT(*) 
        FROM lessons l 
        WHERE l.course_id = e.course_id
    )
");
$stats['in_progress'] = $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;

// Not started enrollments
$result = $db->query("
    SELECT COUNT(*) as count 
    FROM enrollments e
    WHERE (
        SELECT COUNT(*) 
        FROM lesson_progress lp 
        JOIN lessons l ON lp.lesson_id = l.id 
        WHERE lp.user_id = e.user_id 
        AND l.course_id = e.course_id
        AND lp.completed = 1
    ) = 0
");
$stats['not_started'] = $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;

$page_title = 'Manage Enrollments';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-credit-card"></i> Manage Enrollments</h1>
                <p>View and manage all student course enrollments</p>
            </div>
            <div class="header-actions">
                <a href="courses.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Enroll Student
                </a>
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
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #2ecc71;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['completed']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #f39c12;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['in_progress']; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: #e74c3c;">
                    <i class="fas fa-hourglass-start"></i>
                </div>
                <div class="admin-stat-content">
                    <h3><?php echo $stats['not_started']; ?></h3>
                    <p>Not Started</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="admin-filters-section">
            <form method="GET" action="" class="admin-filters-form">
                <div class="admin-filter-group">
                    <div class="admin-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search student or course..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="admin-filter-select">
                        <i class="fas fa-book"></i>
                        <select name="course">
                            <option value="0">All Courses</option>
                            <?php while ($course = $courses->fetch_assoc()): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo $course_filter == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="admin-filter-select">
                        <i class="fas fa-filter"></i>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="in-progress" <?php echo $status_filter === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="not-started" <?php echo $status_filter === 'not-started' ? 'selected' : ''; ?>>Not Started</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    
                    <a href="enrollments.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Enrollments Table -->
        <div class="table-responsive">
            <table class="table" id="enrollmentTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Enrolled At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($enrollments->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="admin-empty-state">
                                    <i class="fas fa-credit-card" style="font-size: 48px;"></i>
                                    <p>No enrollments found.</p>
                                    <?php if (!empty($search) || $course_filter > 0 || !empty($status_filter)): ?>
                                        <p>Try adjusting your filters.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($enrollment = $enrollments->fetch_assoc()): 
                            $progress = (int)$enrollment['progress'];
                            $status_class = $progress == 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');
                            $status_text = $progress == 100 ? 'Completed' : ($progress > 0 ? 'In Progress' : 'Not Started');
                        ?>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <img src="<?php echo getUserImage($enrollment['profile_image']); ?>" alt="<?php echo htmlspecialchars($enrollment['student_name']); ?>" class="student-avatar">
                                        <div>
                                            <div class="student-name"><?php echo htmlspecialchars($enrollment['student_name']); ?></div>
                                            <div class="student-email"><?php echo htmlspecialchars($enrollment['student_email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="course-info">
                                        <img src="<?php echo getCourseImage($enrollment['course_image']); ?>" alt="<?php echo htmlspecialchars($enrollment['course_title']); ?>" class="course-thumbnail">
                                        <span><?php echo htmlspecialchars($enrollment['course_title']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress-cell">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo $progress; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $status_class; ?>">
                                        <i class="fas fa-<?php echo $status_class === 'completed' ? 'check-circle' : ($status_class === 'in-progress' ? 'spinner fa-spin' : 'hourglass-start'); ?>"></i>
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                <td>
                                    <div class="admin-action-buttons">
                                        <button onclick="viewEnrollment(<?php echo $enrollment['id']; ?>)" class="btn btn-small btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="deleteEnrollment(<?php echo $enrollment['id']; ?>, '<?php echo htmlspecialchars($enrollment['student_name']); ?>', '<?php echo htmlspecialchars($enrollment['course_title']); ?>')" class="btn btn-small btn-danger" title="Unenroll">
                                            <i class="fas fa-user-minus"></i>
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

<!-- View Enrollment Modal -->
<div class="modal fade" id="enrollmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card"></i> Enrollment Details
                </h5>
                <button type="button" class="close" onclick="closeModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="enrollmentDetails">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading enrollment details...</p>
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
/* Student Info */
.student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eee;
}

.student-name {
    font-weight: 600;
    color: #2c3e50;
}

.student-email {
    font-size: 12px;
    color: #7f8c8d;
}

/* Course Info */
.course-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.course-thumbnail {
    width: 40px;
    height: 30px;
    object-fit: cover;
    border-radius: 4px;
}

/* Progress Cell */
.progress-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 120px;
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

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-in-progress {
    background: #fff3cd;
    color: #856404;
}

.status-not-started {
    background: #f8d7da;
    color: #721c24;
}

/* Filters Section */
.admin-filters-section {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin: 20px 0;
}

.admin-filter-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.admin-filter-select {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 8px 15px;
    min-width: 150px;
}

.admin-filter-select i {
    color: #999;
    margin-right: 10px;
}

.admin-filter-select select {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 14px;
    cursor: pointer;
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

/* Enrollment Detail Styles */
.enrollment-profile {
    padding: 10px 0;
}

.enrollment-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.enrollment-student,
.enrollment-course {
    display: flex;
    align-items: center;
    gap: 15px;
}

.enrollment-student-image,
.enrollment-course-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eee;
}

.enrollment-student h3,
.enrollment-course h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.enrollment-student p,
.enrollment-course p {
    margin: 0;
    color: #7f8c8d;
    font-size: 13px;
}

.enrollment-student p i,
.enrollment-course p i {
    margin-right: 5px;
    color: #3498db;
    width: 16px;
}

.enrollment-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin: 20px 0;
}

.enrollment-stat-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.enrollment-stat-item h4 {
    margin: 0 0 5px 0;
    font-size: 24px;
    color: #2c3e50;
}

.enrollment-stat-item p {
    margin: 0;
    color: #7f8c8d;
    font-size: 13px;
}

.enrollment-progress-detail {
    margin: 20px 0;
}

.enrollment-progress-detail h4 {
    margin-bottom: 10px;
}

.progress-bar-large {
    width: 100%;
    height: 10px;
    background: #ecf0f1;
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill-large {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.5s ease;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
    font-size: 14px;
    color: #7f8c8d;
}

.progress-percentage {
    font-weight: 600;
    color: #2c3e50;
}

.enrollment-lessons {
    margin-top: 20px;
}

.enrollment-lessons h4 {
    margin-bottom: 15px;
}

.lesson-progress-list {
    max-height: 300px;
    overflow-y: auto;
}

.lesson-progress-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px 12px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: background 0.3s;
}

.lesson-progress-item:hover {
    background: #e9ecef;
}

.lesson-progress-item.completed {
    background: #d4edda;
}

.lesson-number {
    background: #6c757d;
    color: #fff;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.lesson-title {
    flex: 1;
    font-weight: 500;
}

.lesson-status {
    font-size: 13px;
    font-weight: 500;
}

.lesson-status i {
    margin-right: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-filter-group {
        flex-direction: column;
        width: 100%;
    }
    
    .admin-search-box,
    .admin-filter-select {
        width: 100%;
        min-width: unset;
    }
    
    .admin-filter-group .btn {
        width: 100%;
    }
    
    .enrollment-header {
        grid-template-columns: 1fr;
    }
    
    .enrollment-stats {
        grid-template-columns: 1fr;
    }
    
    .student-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .course-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .modal-dialog {
        width: 95%;
        margin: 10px auto;
    }
}
</style>

<script>
// View Enrollment Details
function viewEnrollment(enrollmentId) {
    const modal = document.getElementById('enrollmentModal');
    const details = document.getElementById('enrollmentDetails');
    
    modal.classList.add('show');
    details.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading enrollment details...</p>
        </div>
    `;
    
    fetch('get_enrollment_details.php?id=' + enrollmentId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                details.innerHTML = renderEnrollmentDetails(data.enrollment);
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
                    <i class="fas fa-exclamation-circle"></i> Failed to load enrollment details.
                </div>
            `;
        });
}

// Render Enrollment Details
function renderEnrollmentDetails(enrollment) {
    let lessonsHtml = '';
    if (enrollment.lessons && enrollment.lessons.length > 0) {
        enrollment.lessons.forEach(lesson => {
            lessonsHtml += `
                <div class="lesson-progress-item ${lesson.completed ? 'completed' : ''}">
                    <span class="lesson-number">#${lesson.order_number}</span>
                    <span class="lesson-title">${lesson.title}</span>
                    <span class="lesson-status">
                        ${lesson.completed ? 
                            '<i class="fas fa-check-circle" style="color: #2ecc71;"></i> Completed' : 
                            '<i class="fas fa-clock" style="color: #f39c12;"></i> Pending'
                        }
                    </span>
                </div>
            `;
        });
    }
    
    return `
        <div class="enrollment-profile">
            <div class="enrollment-header">
                <div class="enrollment-student">
                    <img src="${enrollment.student_image}" alt="${enrollment.student_name}" class="enrollment-student-image">
                    <div>
                        <h3>${enrollment.student_name}</h3>
                        <p><i class="fas fa-envelope"></i> ${enrollment.student_email}</p>
                    </div>
                </div>
                <div class="enrollment-course">
                    <img src="${enrollment.course_image}" alt="${enrollment.course_title}" class="enrollment-course-image">
                    <div>
                        <h3>${enrollment.course_title}</h3>
                        <p><i class="fas fa-calendar"></i> Enrolled: ${enrollment.enrolled_date}</p>
                    </div>
                </div>
            </div>
            
            <div class="enrollment-stats">
                <div class="enrollment-stat-item">
                    <h4>${enrollment.total_lessons}</h4>
                    <p>Total Lessons</p>
                </div>
                <div class="enrollment-stat-item">
                    <h4>${enrollment.completed_lessons}</h4>
                    <p>Completed</p>
                </div>
                <div class="enrollment-stat-item">
                    <h4>${enrollment.progress}%</h4>
                    <p>Progress</p>
                </div>
            </div>
            
            <div class="enrollment-progress-detail">
                <h4><i class="fas fa-chart-line"></i> Progress Overview</h4>
                <div class="progress-bar-large">
                    <div class="progress-fill-large" style="width: ${enrollment.progress}%"></div>
                </div>
                <div class="progress-info">
                    <span>${enrollment.completed_lessons} of ${enrollment.total_lessons} lessons completed</span>
                    <span class="progress-percentage">${enrollment.progress}%</span>
                </div>
            </div>
            
            ${enrollment.lessons && enrollment.lessons.length > 0 ? `
                <div class="enrollment-lessons">
                    <h4><i class="fas fa-list"></i> Lesson Progress</h4>
                    <div class="lesson-progress-list">
                        ${lessonsHtml}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

// Close Modal
function closeModal() {
    document.getElementById('enrollmentModal').classList.remove('show');
}

// Close modal on outside click
document.getElementById('enrollmentModal').addEventListener('click', function(e) {
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

// Delete/Unenroll with SweetAlert
function deleteEnrollment(enrollmentId, studentName, courseTitle) {
    Swal.fire({
        title: 'Unenroll Student?',
        html: `Are you sure you want to unenroll <strong>"${studentName}"</strong> from <strong>"${courseTitle}"</strong>?<br><br>This will delete all progress data for this enrollment.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-user-minus"></i> Yes, unenroll!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                window.location.href = 'delete_enrollment.php?id=' + enrollmentId;
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>