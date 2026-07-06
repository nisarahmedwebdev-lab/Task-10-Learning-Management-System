<?php
// enroll.php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

checkStudent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('courses.php');
}

$db = Database::getInstance();
$user_id = getUserId();
$course_id = (int)($_POST['course_id'] ?? 0);

if ($course_id <= 0) {
    setFlashMessage('error', 'Invalid course selection.');
    redirect('courses.php');
}

// Check if already enrolled
$stmt = $db->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    setFlashMessage('error', 'You are already enrolled in this course.');
    redirect('dashboard.php');
}
$stmt->close();

// Enroll the student
$stmt = $db->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $course_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Successfully enrolled in the course!');
} else {
    setFlashMessage('error', 'Failed to enroll. Please try again.');
}

$stmt->close();
redirect('dashboard.php');
?>