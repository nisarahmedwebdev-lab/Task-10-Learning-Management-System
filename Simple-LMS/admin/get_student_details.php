<?php
// admin/get_student_details.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$student_id = (int)($_GET['id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
    exit;
}

$db = Database::getInstance();

// Get student details
$stmt = $db->prepare("SELECT id, full_name, email, profile_image, created_at FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student not found.']);
    exit;
}

// Get enrolled courses with progress
$query = "
    SELECT 
        c.id,
        c.title,
        COUNT(DISTINCT l.id) as total_lessons,
        COUNT(DISTINCT lp.id) as completed_lessons,
        ROUND(IFNULL(COUNT(DISTINCT lp.id) / NULLIF(COUNT(DISTINCT l.id), 0) * 100, 0)) as progress
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
    WHERE e.user_id = ?
    GROUP BY c.id, c.title
    ORDER BY e.enrolled_at DESC
";

$stmt = $db->prepare($query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$courses = $stmt->get_result();

$courses_data = [];
while ($course = $courses->fetch_assoc()) {
    $courses_data[] = [
        'id' => $course['id'],
        'title' => $course['title'],
        'total_lessons' => (int)$course['total_lessons'],
        'completed_lessons' => (int)$course['completed_lessons'],
        'progress' => (int)$course['progress']
    ];
}
$stmt->close();

// Calculate overall statistics
$total_lessons = 0;
$completed_lessons = 0;

foreach ($courses_data as $course) {
    $total_lessons += $course['total_lessons'];
    $completed_lessons += $course['completed_lessons'];
}

$overall_progress = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;

// Prepare response
$response = [
    'success' => true,
    'student' => [
        'id' => $student['id'],
        'full_name' => $student['full_name'],
        'email' => $student['email'],
        'profile_image' => getUserImage($student['profile_image']),
        'joined_date' => date('M d, Y', strtotime($student['created_at'])),
        'enrolled_courses' => count($courses_data),
        'total_lessons' => $total_lessons,
        'completed_lessons' => $completed_lessons,
        'overall_progress' => $overall_progress,
        'courses' => $courses_data
    ]
];

echo json_encode($response);
?>