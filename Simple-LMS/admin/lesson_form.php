<?php
// admin/lesson_form.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

checkAdmin();

$db = Database::getInstance();
$course_id = (int)($_GET['course_id'] ?? 0);
$action = $_GET['action'] ?? 'add';
$lesson_id = (int)($_GET['id'] ?? 0);
$errors = [];
$lesson = [];

if ($course_id <= 0) {
    setFlashMessage('error', 'Invalid course ID.');
    redirect('courses.php');
}

// Get course title for display
$stmt = $db->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    setFlashMessage('error', 'Course not found.');
    redirect('courses.php');
}

if ($action === 'edit' && $lesson_id > 0) {
    $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ? AND course_id = ?");
    $stmt->bind_param("ii", $lesson_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lesson = $result->fetch_assoc();
    $stmt->close();
    
    if (!$lesson) {
        setFlashMessage('error', 'Lesson not found.');
        redirect('lessons.php?course_id=' . $course_id);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $order_number = (int)($_POST['order_number'] ?? 0);
    
    // Validation
    if (empty($title)) {
        $errors['title'] = 'Title is required.';
    }
    
    if (empty($content)) {
        $errors['content'] = 'Content is required.';
    }
    
    if ($order_number <= 0) {
        $errors['order_number'] = 'Order number must be a positive number.';
    }
    
    if (empty($errors)) {
        if ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO lessons (course_id, title, content, video_url, order_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $course_id, $title, $content, $video_url, $order_number);
        } else {
            $stmt = $db->prepare("UPDATE lessons SET title = ?, content = ?, video_url = ?, order_number = ? WHERE id = ? AND course_id = ?");
            $stmt->bind_param("sssiii", $title, $content, $video_url, $order_number, $lesson_id, $course_id);
        }
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Lesson ' . ($action === 'add' ? 'added' : 'updated') . ' successfully.');
            redirect('lessons.php?course_id=' . $course_id);
        } else {
            $errors['general'] = 'Failed to save lesson. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = ($action === 'add' ? 'Add' : 'Edit') . ' Lesson';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="form-container">
        <h1><?php echo $page_title; ?></h1>
        <p class="form-subtitle">Course: <?php echo htmlspecialchars($course['title']); ?></p>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Lesson Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($lesson['title'] ?? $_POST['title'] ?? ''); ?>" required>
                <?php if (!empty($errors['title'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['title']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($lesson['content'] ?? $_POST['content'] ?? ''); ?></textarea>
                <?php if (!empty($errors['content'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['content']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="video_url">Video URL (Optional)</label>
                <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($lesson['video_url'] ?? $_POST['video_url'] ?? ''); ?>">
                <small>YouTube or Vimeo URL</small>
            </div>
            
            <div class="form-group">
                <label for="order_number">Order Number</label>
                <input type="number" id="order_number" name="order_number" value="<?php echo htmlspecialchars($lesson['order_number'] ?? $_POST['order_number'] ?? 1); ?>" min="1" required>
                <?php if (!empty($errors['order_number'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['order_number']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Lesson</button>
                <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>