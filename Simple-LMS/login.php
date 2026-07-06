<?php
// login.php

require_once 'config/session.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$error = '';
$debug = []; // For debugging

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Debug: Log the attempt
    $debug['email'] = $email;
    $debug['password_length'] = strlen($password);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            // Check if connection is working
            if (!$connection) {
                $error = 'Database connection failed. Please check your configuration.';
            } else {
                // Prepare statement to prevent SQL injection
                $stmt = $connection->prepare("SELECT id, full_name, email, password, role, profile_image FROM users WHERE email = ?");
                
                if (!$stmt) {
                    $error = 'Database error: ' . $connection->error;
                } else {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $debug['rows_found'] = $result->num_rows;
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        $debug['user_found'] = $user['email'];
                        $debug['hashed_password'] = $user['password'];
                        
                        // Verify password
                        if (password_verify($password, $user['password'])) {
                            // Login successful
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['full_name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['profile_image'] = $user['profile_image'];
                            
                            setFlashMessage('success', 'Welcome back, ' . $user['full_name'] . '!');
                            
                            if ($user['role'] === 'admin') {
                                redirect('admin/dashboard.php');
                            } else {
                                redirect('dashboard.php');
                            }
                        } else {
                            $error = 'Invalid password.';
                            $debug['password_verify'] = 'failed';
                        }
                    } else {
                        $error = 'Email not found.';
                        $debug['user_found'] = false;
                    }
                    $stmt->close();
                }
            }
        } catch (Exception $e) {
            $error = 'System error: ' . $e->getMessage();
            $debug['exception'] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 12px;
            font-family: monospace;
            display: <?php echo isset($debug) ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <div class="auth-header">
                    <h1><?php echo APP_NAME; ?></h1>
                    <p>Sign in to continue</p>
                </div>
                
                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <!-- Debug Information -->
                <?php if (isset($debug) && !empty($debug)): ?>
                    <div class="debug-info">
                        <strong>Debug Information:</strong><br>
                        <?php foreach ($debug as $key => $value): ?>
                            <?php echo htmlspecialchars($key) . ': ' . htmlspecialchars(print_r($value, true)) . '<br>'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@example.com'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" value="Admin@123" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>