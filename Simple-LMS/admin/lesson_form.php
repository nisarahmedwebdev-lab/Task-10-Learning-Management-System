<?php
// admin/lesson_form.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

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
    $content = $_POST['content'] ?? '';
    $video_url = trim($_POST['video_url'] ?? '');
    $order_number = (int)($_POST['order_number'] ?? 0);
    
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
include __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-dashboard">
    <div class="container">
        <div class="admin-form-container">
            <h1><i class="fas fa-<?php echo $action === 'add' ? 'plus-circle' : 'edit'; ?>"></i> <?php echo $page_title; ?></h1>
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                <p class="admin-form-subtitle">Course: <strong><?php echo htmlspecialchars($course['title']); ?></strong></p>
                <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Lessons
                </a>
            </div>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="lessonForm">
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i> Lesson Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($lesson['title'] ?? $_POST['title'] ?? ''); ?>" placeholder="Enter lesson title" required>
                    <?php if (!empty($errors['title'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['title']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="content">
                        <i class="fas fa-align-left"></i> Content <span class="text-danger">*</span>
                    </label>
                    <textarea id="content" name="content" rows="15" data-tinymce><?php echo htmlspecialchars($lesson['content'] ?? $_POST['content'] ?? ''); ?></textarea>
                    <?php if (!empty($errors['content'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['content']); ?></span>
                    <?php endif; ?>
                    <small><i class="fas fa-info-circle"></i> Use the toolbar to format your content (bold, italic, lists, links, images, tables, etc.)</small>
                </div>
                
                <div class="form-group">
                    <label for="video_url">
                        <i class="fas fa-video"></i> Video URL <span class="text-muted">(Optional)</span>
                    </label>
                    <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($lesson['video_url'] ?? $_POST['video_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                    <small><i class="fas fa-info-circle"></i> YouTube or Vimeo URL</small>
                </div>
                
                <div class="form-group">
                    <label for="order_number">
                        <i class="fas fa-sort-numeric-down"></i> Order Number <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="order_number" name="order_number" value="<?php echo htmlspecialchars($lesson['order_number'] ?? $_POST['order_number'] ?? 1); ?>" min="1" required>
                    <?php if (!empty($errors['order_number'])): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['order_number']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Lesson
                    </button>
                    <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.text-danger {
    color: #e74c3c;
}

.text-muted {
    color: #95a5a6;
    font-weight: normal;
}

/* TinyMCE Custom Styles */
.tox-tinymce {
    border-radius: 8px !important;
    border: 1px solid #ddd !important;
}

.tox-toolbar__group {
    padding: 4px 0 !important;
}

.tox .tox-toolbar {
    background: #f8f9fa !important;
}

.tox .tox-tbtn {
    border-radius: 4px !important;
}

.tox .tox-tbtn:hover {
    background: #e9ecef !important;
}

.tox .tox-tbtn--enabled {
    background: #3498db !important;
    color: #fff !important;
}

.tox .tox-tbtn--enabled svg {
    fill: #fff !important;
}
</style>

<!-- Load TinyMCE Config -->
<script src="<?php echo $base_path; ?>assets/js/tinymce-config.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE for content textarea
    if (typeof initTinyMCE !== 'undefined') {
        initTinyMCE('#content', {
            height: 500
        });
    }
    
    // Form submission - get content from TinyMCE
    document.getElementById('lessonForm').addEventListener('submit', function(e) {
        if (typeof tinymce !== 'undefined') {
            const content = tinymce.get('content').getContent();
            document.getElementById('content').value = content;
        }
    });
});

// Handle page unload - clear localStorage
window.addEventListener('beforeunload', function() {
    localStorage.removeItem('lesson_content_0');
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>