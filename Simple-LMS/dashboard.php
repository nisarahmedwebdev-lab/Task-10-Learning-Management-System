<?php
// dashboard.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

checkStudent();

$db = Database::getInstance();
$user_id = getUserId();

// Get user data
$stmt = $db->prepare("SELECT full_name, email, profile_image, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get enrolled courses with progress
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

// Get statistics
$total_courses = $enrolled_courses->num_rows;
$completed_courses = 0;
$in_progress_courses = 0;
$total_progress = 0;

while ($course = $enrolled_courses->fetch_assoc()) {
    if ($course['progress_percentage'] == 100) {
        $completed_courses++;
    } elseif ($course['progress_percentage'] > 0) {
        $in_progress_courses++;
    }
    $total_progress += $course['progress_percentage'];
}
// Reset pointer
$enrolled_courses->data_seek(0);

$avg_progress = $total_courses > 0 ? round($total_progress / $total_courses) : 0;

$page_title = 'Dashboard';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="student-dashboard">
    <div class="container">
        <div class="dashboard-layout">
            <!-- Sidebar -->
            <div class="sidebar-wrapper">
                <?php include 'includes/student-sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="main-content-wrapper">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <div class="user-greeting">
                            <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 👋</h1>
                            <p>Continue your learning journey and track your progress.</p>
                        </div>
                        <div class="user-profile-card">
                            <img src="<?php echo getUserImage($user['profile_image']); ?>" alt="Profile" class="profile-avatar">
                            <div class="user-details">
                                <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p><i class="fas fa-calendar"></i> Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $total_courses; ?></h3>
                            <p>Enrolled Courses</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $completed_courses; ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $in_progress_courses; ?></h3>
                            <p>In Progress</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $avg_progress; ?>%</h3>
                            <p>Average Progress</p>
                        </div>
                    </div>
                </div>

                <!-- My Courses Section -->
                <div class="my-courses-section">
                    <div class="section-header">
                        <h2><i class="fas fa-graduation-cap"></i> My Courses</h2>
                        <a href="courses.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Browse More Courses
                        </a>
                    </div>

                    <?php if ($enrolled_courses->num_rows === 0): ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <h3>No Courses Enrolled Yet</h3>
                            <p>Start your learning journey by enrolling in a course.</p>
                            <a href="courses.php" class="btn btn-primary">
                                <i class="fas fa-search"></i> Browse Courses
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="course-grid">
                            <?php while ($course = $enrolled_courses->fetch_assoc()): 
                                $progress = (int)$course['progress_percentage'];
                                $status_class = $progress == 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started');
                                $status_text = $progress == 100 ? 'Completed' : ($progress > 0 ? 'In Progress' : 'Not Started');
                            ?>
                                <div class="course-card">
                                    <div class="course-image">
                                        <img src="<?php echo getCourseImage($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                        <div class="course-status status-<?php echo $status_class; ?>">
                                            <i class="fas fa-<?php echo $status_class === 'completed' ? 'check-circle' : ($status_class === 'in-progress' ? 'spinner fa-spin' : 'clock'); ?>"></i>
                                            <?php echo $status_text; ?>
                                        </div>
                                    </div>
                                    <div class="course-content">
                                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?></p>
                                        
                                        <div class="course-progress">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                            <div class="progress-info">
                                                <span class="progress-text"><?php echo $progress; ?>% Complete</span>
                                                <span class="lessons-count">
                                                    <i class="fas fa-video"></i> <?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> lessons
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="course-actions">
                                            <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-arrow-right"></i> Continue
                                            </a>
                                            <?php if ($progress == 100): ?>
                                                <span class="completed-badge">
                                                    <i class="fas fa-trophy"></i> Completed
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity-section">
                    <div class="section-header">
                        <h2><i class="fas fa-clock"></i> Recent Activity</h2>
                    </div>
                    <div class="activity-list">
                        <?php
                        // Get recent lesson completions
                        $query = "
                            SELECT 
                                lp.completed_at,
                                l.title as lesson_title,
                                c.title as course_title,
                                c.id as course_id
                            FROM lesson_progress lp
                            JOIN lessons l ON lp.lesson_id = l.id
                            JOIN courses c ON l.course_id = c.id
                            WHERE lp.user_id = ? AND lp.completed = 1
                            ORDER BY lp.completed_at DESC
                            LIMIT 5
                        ";
                        $stmt = $db->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $recent_activities = $stmt->get_result();
                        $stmt->close();
                        ?>
                        
                        <?php if ($recent_activities->num_rows > 0): ?>
                            <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p>
                                            Completed <strong><?php echo htmlspecialchars($activity['lesson_title']); ?></strong>
                                            in <a href="course_detail.php?id=<?php echo $activity['course_id']; ?>">
                                                <?php echo htmlspecialchars($activity['course_title']); ?>
                                            </a>
                                        </p>
                                        <span class="activity-time">
                                            <i class="fas fa-clock"></i> <?php echo timeAgo($activity['completed_at']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="activity-empty">
                                <i class="fas fa-clock"></i>
                                <p>No recent activity. Start learning today!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>