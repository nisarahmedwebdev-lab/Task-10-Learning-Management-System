<?php
// includes/upload.php

require_once __DIR__ . '/../config/config.php';

function uploadFile($file, $subdirectory = '') {
    $result = ['success' => false, 'filename' => null, 'message' => ''];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error.';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $result['message'] = 'File size exceeds 2MB limit.';
        return $result;
    }
    
    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $result['message'] = 'Only JPG, JPEG, and PNG files are allowed.';
        return $result;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = UPLOAD_PATH . $subdirectory . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['message'] = 'File uploaded successfully.';
    } else {
        $result['message'] = 'Failed to move uploaded file.';
    }
    
    return $result;
}

function deleteFile($filename, $subdirectory = '') {
    if (empty($filename) || $filename === 'default-user.png' || $filename === 'default-course.jpg') {
        return false;
    }
    
    $filepath = UPLOAD_PATH . $subdirectory . '/' . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}
?>