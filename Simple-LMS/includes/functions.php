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
?>