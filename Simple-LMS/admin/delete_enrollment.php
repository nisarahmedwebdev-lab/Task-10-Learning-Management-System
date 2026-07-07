<?php
// admin/delete_enrollment.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

checkAdmin();

$enrollment_id = (int)($_GET['id'] ?? 0);

if ($enrollment_id <= 0) {
    setFlashMessage('error', 'Invalid enrollment ID.');
    redirect('enrollments.php');
}

$db = Database::getInstance();

// Delete enrollment (cascade will handle lesson_progress)
$stmt = $db->prepare("DELETE FROM enrollments WHERE id = ?");
$stmt->bind_param("i", $enrollment_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Student unenrolled successfully.');
} else {
    setFlashMessage('error', 'Failed to unenroll student. Please try again.');
}

$stmt->close();
redirect('enrollments.php');
?>