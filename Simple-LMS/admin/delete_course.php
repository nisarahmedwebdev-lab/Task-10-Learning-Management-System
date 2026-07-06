<?php
// admin/delete_course.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';

checkAdmin();

$course_id = (int)($_GET['id'] ?? 0);

if ($course_id <= 0) {
    setFlashMessage('error', 'Invalid course ID.');
    redirect('courses.php');
}

$db = Database::getInstance();

// Get course image before deleting
$stmt = $db->prepare("SELECT image FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    setFlashMessage('error', 'Course not found.');
    redirect('courses.php');
}

// Delete course image
if ($course['image'] && $course['image'] !== 'default-course.jpg') {
    deleteFile($course['image'], 'courses');
}

// Delete the course (cascade will handle lessons, enrollments, and progress)
$stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Course and all associated data deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete course. Please try again.');
}

$stmt->close();
redirect('courses.php');
?>