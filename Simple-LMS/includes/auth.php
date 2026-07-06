<?php
// includes/auth.php

// Fix the path - go up one level to find config
require_once __DIR__ . '/../config/session.php';

function checkAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to continue.');
        redirect('login.php');
    }
}

function checkAdmin() {
    checkAuth();
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        redirect('../dashboard.php');
    }
}

function checkStudent() {
    checkAuth();
    if (!isStudent()) {
        setFlashMessage('error', 'Access denied. Student privileges required.');
        redirect('../admin/dashboard.php');
    }
}
?>