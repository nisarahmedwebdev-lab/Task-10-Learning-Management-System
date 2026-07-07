<?php
// admin/delete_lesson.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

checkAdmin();

$lesson_id = (int)($_GET['id'] ?? 0);
$course_id = (int)($_GET['course_id'] ?? 0);

if ($lesson_id <= 0 || $course_id <= 0) {
    setFlashMessage('error', 'Invalid lesson or course ID.');
    redirect('courses.php');
}

$db = Database::getInstance();

// First, delete lesson progress records
$stmt = $db->prepare("DELETE FROM lesson_progress WHERE lesson_id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$stmt->close();

// Then delete the lesson
$stmt = $db->prepare("DELETE FROM lessons WHERE id = ? AND course_id = ?");
$stmt->bind_param("ii", $lesson_id, $course_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Lesson deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete lesson. Please try again.');
}

$stmt->close();
redirect('lessons.php?course_id=' . $course_id);
?>