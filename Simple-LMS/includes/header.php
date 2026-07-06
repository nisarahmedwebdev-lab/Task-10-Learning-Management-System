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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/responsive.css">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo $base_path . $extra_css; ?>">
    <?php endif; ?>
</head>
<body>