<?php
// test_db.php

require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "✅ Database connection successful!<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
    
    // Check if admin exists
    $result = $connection->query("SELECT id, email, role FROM users WHERE email = 'admin@example.com'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ Admin user found!<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
    } else {
        echo "❌ Admin user NOT found!<br>";
        echo "You need to run the seed_admin.sql file.<br>";
    }
    
    // Show all users
    $result = $connection->query("SELECT id, email, role FROM users");
    echo "<br>All users in database:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['email'] . " (Role: " . $row['role'] . ")<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>