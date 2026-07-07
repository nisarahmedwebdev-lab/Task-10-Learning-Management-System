<?php
// admin/get_enrollment_details.php

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

$enrollment_id = (int)($_GET['id'] ?? 0);

if ($enrollment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid enrollment ID.']);
    exit;
}

$db = Database::getInstance();

// Get enrollment details
$query = "
    SELECT 
        e.id,
        e.enrolled_at,
        u.id as user_id,
        u.full_name as student_name,
        u.email as student_email,
        u.profile_image,
        c.id as course_id,
        c.title as course_title,
        c.image as course_image,
        COUNT(DISTINCT l.id) as total_lessons,
        COUNT(DISTINCT lp.id) as completed_lessons,
        ROUND(IFNULL(COUNT(DISTINCT lp.id) / NULLIF(COUNT(DISTINCT l.id), 0) * 100, 0)) as progress
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN lessons l ON c.id = l.course_id
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = u.id
    WHERE e.id = ?
    GROUP BY e.id, u.id, u.full_name, u.email, u.profile_image, c.id, c.title, c.image, e.enrolled_at
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $enrollment_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$enrollment) {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found.']);
    exit;
}

// Get lesson progress details
$query = "
    SELECT 
        l.id,
        l.title,
        l.order_number,
        IFNULL(lp.completed, 0) as completed
    FROM lessons l
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
    WHERE l.course_id = ?
    ORDER BY l.order_number ASC
";

$stmt = $db->prepare($query);
$stmt->bind_param("ii", $enrollment['user_id'], $enrollment['course_id']);
$stmt->execute();
$lessons = $stmt->get_result();

$lessons_data = [];
while ($lesson = $lessons->fetch_assoc()) {
    $lessons_data[] = [
        'id' => $lesson['id'],
        'title' => $lesson['title'],
        'order_number' => $lesson['order_number'],
        'completed' => (bool)$lesson['completed']
    ];
}
$stmt->close();

// Prepare response
$response = [
    'success' => true,
    'enrollment' => [
        'id' => $enrollment['id'],
        'student_name' => $enrollment['student_name'],
        'student_email' => $enrollment['student_email'],
        'student_image' => getUserImage($enrollment['profile_image']),
        'course_title' => $enrollment['course_title'],
        'course_image' => getCourseImage($enrollment['course_image']),
        'enrolled_date' => date('M d, Y H:i', strtotime($enrollment['enrolled_at'])),
        'total_lessons' => (int)$enrollment['total_lessons'],
        'completed_lessons' => (int)$enrollment['completed_lessons'],
        'progress' => (int)$enrollment['progress'],
        'lessons' => $lessons_data
    ]
];

echo json_encode($response);
?>