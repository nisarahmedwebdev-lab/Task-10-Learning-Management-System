<?php
// lesson.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

checkStudent();

$db = Database::getInstance();
$user_id = getUserId();
$lesson_id = (int)($_GET['id'] ?? 0);
$course_id = (int)($_GET['course_id'] ?? 0);

if ($lesson_id <= 0 || $course_id <= 0) {
    setFlashMessage('error', 'Invalid lesson or course ID.');
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

// Get lesson details
$query = "
    SELECT l.*, c.title as course_title,
           lp.completed as is_completed
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
    WHERE l.id = ? AND l.course_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("iii", $user_id, $lesson_id, $course_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lesson) {
    setFlashMessage('error', 'Lesson not found.');
    redirect('course_detail.php?id=' . $course_id);
}

// Get navigation: previous and next lessons
$stmt = $db->prepare("
    SELECT id, title, order_number 
    FROM lessons 
    WHERE course_id = ? 
    ORDER BY order_number
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$all_lessons = $stmt->get_result();
$lesson_list = [];
$current_index = 0;

while ($row = $all_lessons->fetch_assoc()) {
    $lesson_list[] = $row;
    if ($row['id'] == $lesson_id) {
        $current_index = count($lesson_list) - 1;
    }
}
$stmt->close();

$prev_lesson = $current_index > 0 ? $lesson_list[$current_index - 1] : null;
$next_lesson = $current_index < count($lesson_list) - 1 ? $lesson_list[$current_index + 1] : null;

$page_title = htmlspecialchars($lesson['title']);
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="lesson-container">
        <div class="lesson-navigation">
            <a href="course_detail.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
            <div class="lesson-nav-links">
                <?php if ($prev_lesson): ?>
                    <a href="lesson.php?id=<?php echo $prev_lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                <?php if ($next_lesson): ?>
                    <a href="lesson.php?id=<?php echo $next_lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="lesson-content">
            <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
            <p class="lesson-meta">
                <i class="fas fa-book"></i> Course: <?php echo htmlspecialchars($lesson['course_title']); ?>
                <span class="lesson-order-badge">
                    <i class="fas fa-sort-numeric-down"></i> Lesson <?php echo $lesson['order_number']; ?>
                </span>
            </p>
            
            <?php if ($lesson['video_url']): ?>
                <div class="video-container">
                    <?php
                    $video_id = '';
                    if (strpos($lesson['video_url'], 'youtube.com') !== false || 
                        strpos($lesson['video_url'], 'youtu.be') !== false) {
                        parse_str(parse_url($lesson['video_url'], PHP_URL_QUERY), $params);
                        $video_id = $params['v'] ?? '';
                        if (empty($video_id) && strpos($lesson['video_url'], 'youtu.be') !== false) {
                            $video_id = substr(parse_url($lesson['video_url'], PHP_URL_PATH), 1);
                        }
                        if ($video_id) {
                            echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                        }
                    } elseif (strpos($lesson['video_url'], 'vimeo.com') !== false) {
                        $video_id = substr(parse_url($lesson['video_url'], PHP_URL_PATH), 1);
                        echo '<iframe src="https://player.vimeo.com/video/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        echo '<p><i class="fas fa-link"></i> <a href="' . htmlspecialchars($lesson['video_url']) . '" target="_blank">' . htmlspecialchars($lesson['video_url']) . '</a></p>';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- CRITICAL FIX: Display HTML content without htmlspecialchars -->
            <div class="lesson-text">
                <?php echo $lesson['content']; ?>
            </div>
            
            <div class="lesson-actions-bottom">
                <form method="POST" action="update_progress.php">
                    <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <input type="hidden" name="completed" value="<?php echo ($lesson['is_completed'] ?? 0) ? '0' : '1'; ?>">
                    <button type="submit" class="btn <?php echo ($lesson['is_completed'] ?? 0) ? 'btn-success' : 'btn-primary'; ?>">
                        <i class="fas <?php echo ($lesson['is_completed'] ?? 0) ? 'fa-check-circle' : 'fa-check'; ?>"></i>
                        <?php echo ($lesson['is_completed'] ?? 0) ? ' ✓ Completed' : ' Mark as Complete'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.lesson-order-badge {
    display: inline-block;
    background: #3498db;
    color: #fff;
    padding: 2px 12px;
    border-radius: 20px;
    font-size: 13px;
    margin-left: 15px;
}

.lesson-text {
    margin: 2rem 0;
    line-height: 1.8;
}

/* Lesson content styles - matches TinyMCE */
.lesson-text h1 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 25px 0 15px 0;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 10px;
}

.lesson-text h2 {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 20px 0 12px 0;
}

.lesson-text h3 {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin: 18px 0 10px 0;
}

.lesson-text h4 {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 15px 0 10px 0;
}

.lesson-text p {
    margin-bottom: 15px;
    line-height: 1.8;
    color: #333;
}

.lesson-text ul,
.lesson-text ol {
    margin: 10px 0 20px 30px;
    padding: 0;
}

.lesson-text li {
    margin-bottom: 8px;
    line-height: 1.6;
    color: #333;
}

.lesson-text ul li {
    list-style-type: disc;
}

.lesson-text ol li {
    list-style-type: decimal;
}

.lesson-text a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.lesson-text a:hover {
    text-decoration: underline;
    color: #2980b9;
}

.lesson-text blockquote {
    border-left: 4px solid #3498db;
    padding: 15px 25px;
    margin: 20px 0;
    background: #f8f9fa;
    font-style: italic;
    border-radius: 0 8px 8px 0;
    color: #555;
}

.lesson-text blockquote p {
    margin-bottom: 0;
}

.lesson-text code {
    background: #f8f9fa;
    padding: 3px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    color: #e74c3c;
    border: 1px solid #e9ecef;
}

.lesson-text pre {
    background: #2c3e50;
    color: #ecf0f1;
    padding: 20px 25px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 20px 0;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.6;
    border: 1px solid #34495e;
}

.lesson-text pre code {
    background: transparent;
    color: #ecf0f1;
    padding: 0;
    border: none;
    font-size: 14px;
}

.lesson-text img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 15px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.lesson-text table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 15px;
}

.lesson-text table th,
.lesson-text table td {
    border: 1px solid #ddd;
    padding: 12px 15px;
    text-align: left;
}

.lesson-text table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.lesson-text table tr:nth-child(even) {
    background: #f8f9fa;
}

.lesson-text table tr:hover {
    background: #e9ecef;
}

.lesson-text hr {
    border: none;
    height: 1px;
    background: linear-gradient(to right, #ecf0f1, #3498db, #ecf0f1);
    margin: 25px 0;
}

.lesson-text strong,
.lesson-text b {
    font-weight: 700;
    color: #2c3e50;
}

.lesson-text em,
.lesson-text i {
    font-style: italic;
}

.lesson-text u {
    text-decoration: underline;
}

.lesson-text strike {
    text-decoration: line-through;
}

@media (max-width: 768px) {
    .lesson-text h1 {
        font-size: 24px;
    }
    
    .lesson-text h2 {
        font-size: 20px;
    }
    
    .lesson-text h3 {
        font-size: 18px;
    }
    
    .lesson-text ul,
    .lesson-text ol {
        margin-left: 20px;
    }
    
    .lesson-text pre {
        padding: 15px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .lesson-text h1 {
        font-size: 20px;
    }
    
    .lesson-text h2 {
        font-size: 18px;
    }
    
    .lesson-text h3 {
        font-size: 16px;
    }
    
    .lesson-text blockquote {
        padding: 10px 15px;
    }
    
    .lesson-text pre {
        font-size: 12px;
        padding: 12px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>