<?php
// includes/header.php

// Ensure APP_URL is defined
if (!defined('APP_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Determine the base path for assets
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
} else {
    $base_path = '';
}

// Load functions if not already loaded
if (!function_exists('getUserImage')) {
    require_once __DIR__ . '/functions.php';
}

// Determine if we're on student pages (NOT admin)
$is_student_page = false;
$current_file = basename($_SERVER['PHP_SELF']);

// Student pages
$student_pages = ['dashboard.php', 'courses.php', 'course_detail.php', 'lesson.php'];

// Check if it's a student page AND not admin
if (in_array($current_file, $student_pages) && strpos($_SERVER['PHP_SELF'], '/admin/') === false) {
    $is_student_page = true;
}

// Check if we need TinyMCE
$use_tinymce = false;
if (strpos($_SERVER['PHP_SELF'], 'lesson_form.php') !== false) {
    $use_tinymce = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? APP_NAME; ?></title>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- TinyMCE Rich Text Editor with API Key -->
    <?php if ($use_tinymce): ?>
    <script src="https://cdn.tiny.cloud/1/cxlbvbj8wr546pct09uhrhcwv913ej87xh6hnyzz5zv6vz0e/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin.css">
    
    <!-- Student Panel CSS - ONLY on student pages -->
    <?php if ($is_student_page): ?>
        <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/student-panel.css">
    <?php endif; ?>
    
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo $base_path . $extra_css; ?>">
    <?php endif; ?>
</head>
<body>