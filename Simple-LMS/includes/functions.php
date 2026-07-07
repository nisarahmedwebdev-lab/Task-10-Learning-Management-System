<?php
// includes/functions.php

function getImagePath($folder, $filename, $default = 'default.jpg') {
    if (empty($filename) || $filename === 'default-user.png' || $filename === 'default-course.jpg') {
        return APP_URL . 'assets/images/' . $default;
    }
    return APP_URL . 'uploads/' . $folder . '/' . $filename;
}

function getUserImage($filename) {
    return getImagePath('users', $filename, 'default-user.png');
}

function getCourseImage($filename) {
    return getImagePath('courses', $filename, 'default-course.jpg');
}

function getDefaultImage($type) {
    if ($type === 'user') {
        return APP_URL . 'assets/images/default-user.png';
    } elseif ($type === 'course') {
        return APP_URL . 'assets/images/default-course.jpg';
    }
    return APP_URL . 'assets/images/default.jpg';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}
?>