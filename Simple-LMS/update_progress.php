<?php
// update_progress.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

checkStudent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$db = Database::getInstance();
$user_id = getUserId();
$lesson_id = (int)($_POST['lesson_id'] ?? 0);
$course_id = (int)($_POST['course_id'] ?? 0);
$completed = (int)($_POST['completed'] ?? 0);

if ($lesson_id <= 0 || $course_id <= 0) {
    setFlashMessage('error', 'Invalid lesson or course ID.');
    redirect('course_detail.php?id=' . $course_id);
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

// Check if lesson belongs to the course
$stmt = $db->prepare("SELECT id FROM lessons WHERE id = ? AND course_id = ?");
$stmt->bind_param("ii", $lesson_id, $course_id);
$stmt->execute();
$lesson = $stmt->get_result();
$stmt->close();

if ($lesson->num_rows === 0) {
    setFlashMessage('error', 'Lesson not found in this course.');
    redirect('course_detail.php?id=' . $course_id);
}

// Update or insert progress
$completed_at = $completed ? date('Y-m-d H:i:s') : null;

$stmt = $db->prepare("
    INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    completed = VALUES(completed),
    completed_at = VALUES(completed_at)
");

$stmt->bind_param("iiis", $user_id, $lesson_id, $completed, $completed_at);

if ($stmt->execute()) {
    $message = $completed ? 'Lesson marked as complete!' : 'Lesson marked as incomplete.';
    setFlashMessage('success', $message);
} else {
    setFlashMessage('error', 'Failed to update progress. Please try again.');
}

$stmt->close();
redirect('course_detail.php?id=' . $course_id);
?>