<?php
// register.php

require_once 'config/session.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$errors = [];
$form_data = [];
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];
    
    // Server-side validation
    if (empty($form_data['full_name'])) {
        $errors['full_name'] = 'Full name is required.';
    }
    
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    
    if (empty($form_data['password'])) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($form_data['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $form_data['password'])) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $form_data['password'])) {
        $errors['password'] = 'Password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $form_data['password'])) {
        $errors['password'] = 'Password must contain at least one special character.';
    }
    
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    // Check if email already exists
    if (empty($errors['email'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $form_data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors['email'] = 'Email already registered.';
        }
        $stmt->close();
    }
    
    // Handle profile image upload
    $profile_image = null;
    if (empty($errors) && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        require_once 'includes/upload.php';
        $upload_result = uploadFile($_FILES['profile_image'], 'users');
        
        if ($upload_result['success']) {
            $profile_image = $upload_result['filename'];
        } else {
            $errors['profile_image'] = $upload_result['message'];
        }
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (full_name, email, password, profile_image, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->bind_param("ssss", $form_data['full_name'], $form_data['email'], $hashed_password, $profile_image);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Registration successful! Please login.');
            redirect('login.php');
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <div class="auth-header">
                    <h1><?php echo APP_NAME; ?></h1>
                    <p>Create your account</p>
                </div>
                
                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" id="registerForm">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>" required>
                        <?php if (!empty($errors['full_name'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['full_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                        <?php if (!empty($errors['email'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <small>Password must be at least 8 characters, with 1 uppercase, 1 number, and 1 special character.</small>
                        <?php if (!empty($errors['password'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image">Profile Image (Optional)</label>
                        <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png">
                        <small>Allowed: JPG, JPEG, PNG (Max 2MB)</small>
                        <?php if (!empty($errors['profile_image'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['profile_image']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/register.js"></script>
</body>
</html>