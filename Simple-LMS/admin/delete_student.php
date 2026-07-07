<?php
// admin/delete_student.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/functions.php';

checkAdmin();

$student_id = (int)($_GET['id'] ?? 0);

if ($student_id <= 0) {
    setFlashMessage('error', 'Invalid student ID.');
    redirect('students.php');
}

$db = Database::getInstance();

// Get student info for image deletion
$stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    setFlashMessage('error', 'Student not found.');
    redirect('students.php');
}

// Delete profile image if exists
if ($student['profile_image'] && $student['profile_image'] !== 'default-user.png') {
    deleteFile($student['profile_image'], 'users');
}

// Delete student (cascade will handle enrollments and progress)
$stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Student deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete student. Please try again.');
}

$stmt->close();
redirect('students.php');
?>