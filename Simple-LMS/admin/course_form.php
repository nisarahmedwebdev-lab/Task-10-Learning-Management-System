<?php
// admin/course_form.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';

checkAdmin();

$db = Database::getInstance();
$action = $_GET['action'] ?? 'add';
$course_id = (int)($_GET['id'] ?? 0);
$errors = [];
$course = [];

if ($action === 'edit' && $course_id > 0) {
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
    
    if (!$course) {
        setFlashMessage('error', 'Course not found.');
        redirect('courses.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_updated = false;
    
    // Validation
    if (empty($title)) {
        $errors['title'] = 'Title is required.';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required.';
    }
    
    // Handle image upload
    $image = $course['image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], 'courses');
        if ($upload_result['success']) {
            // Delete old image if exists
            if ($image && $image !== 'default-course.jpg') {
                deleteFile($image, 'courses');
            }
            $image = $upload_result['filename'];
            $image_updated = true;
        } else {
            $errors['image'] = $upload_result['message'];
        }
    }
    
    if (empty($errors)) {
        if ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO courses (title, description, image, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $title, $description, $image, $_SESSION['user_id']);
        } else {
            $stmt = $db->prepare("UPDATE courses SET title = ?, description = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $description, $image, $course_id);
        }
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Course ' . ($action === 'add' ? 'added' : 'updated') . ' successfully.');
            redirect('courses.php');
        } else {
            $errors['general'] = 'Failed to save course. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = ($action === 'add' ? 'Add' : 'Edit') . ' Course';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
    <div class="form-container">
        <h1><?php echo $page_title; ?></h1>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Course Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($course['title'] ?? $_POST['title'] ?? ''); ?>" required>
                <?php if (!empty($errors['title'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['title']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($course['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                <?php if (!empty($errors['description'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['description']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image">Course Image</label>
                <?php if ($action === 'edit' && !empty($course['image'])): ?>
                    <div class="current-image">
                        <img src="<?php echo UPLOAD_URL . 'courses/' . $course['image']; ?>" alt="Current image">
                        <p>Current image</p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png">
                <small>Allowed: JPG, JPEG, PNG (Max 2MB)</small>
                <?php if (!empty($errors['image'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['image']); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Course</button>
                <a href="courses.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>