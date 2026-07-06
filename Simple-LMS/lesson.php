<?php
// lesson.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

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
    SELECT l.*, c.title as course_title
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    WHERE l.id = ? AND l.course_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("ii", $lesson_id, $course_id);
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
                ← Back to Course
            </a>
            <div class="lesson-nav-links">
                <?php if ($prev_lesson): ?>
                    <a href="lesson.php?id=<?php echo $prev_lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        ← Previous: <?php echo htmlspecialchars($prev_lesson['title']); ?>
                    </a>
                <?php endif; ?>
                <?php if ($next_lesson): ?>
                    <a href="lesson.php?id=<?php echo $next_lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        Next: <?php echo htmlspecialchars($next_lesson['title']); ?> →
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="lesson-content">
            <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
            <p class="lesson-meta">Course: <?php echo htmlspecialchars($lesson['course_title']); ?></p>
            
            <?php if ($lesson['video_url']): ?>
                <div class="video-container">
                    <?php
                    $video_id = '';
                    if (strpos($lesson['video_url'], 'youtube.com') !== false || 
                        strpos($lesson['video_url'], 'youtu.be') !== false) {
                        // YouTube embed
                        parse_str(parse_url($lesson['video_url'], PHP_URL_QUERY), $params);
                        $video_id = $params['v'] ?? '';
                        if (empty($video_id) && strpos($lesson['video_url'], 'youtu.be') !== false) {
                            $video_id = substr(parse_url($lesson['video_url'], PHP_URL_PATH), 1);
                        }
                        if ($video_id) {
                            echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                        }
                    } elseif (strpos($lesson['video_url'], 'vimeo.com') !== false) {
                        // Vimeo embed
                        $video_id = substr(parse_url($lesson['video_url'], PHP_URL_PATH), 1);
                        echo '<iframe src="https://player.vimeo.com/video/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        echo '<p>Video URL: <a href="' . htmlspecialchars($lesson['video_url']) . '" target="_blank">' . htmlspecialchars($lesson['video_url']) . '</a></p>';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="lesson-text">
                <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
            </div>
            
            <div class="lesson-actions-bottom">
                <form method="POST" action="update_progress.php">
                    <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <input type="hidden" name="completed" value="<?php echo $lesson['completed'] ?? 0 ? '0' : '1'; ?>">
                    <button type="submit" class="btn <?php echo ($lesson['completed'] ?? 0) ? 'btn-success' : 'btn-primary'; ?>">
                        <?php echo ($lesson['completed'] ?? 0) ? '✓ Marked as Complete' : 'Mark as Complete'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>