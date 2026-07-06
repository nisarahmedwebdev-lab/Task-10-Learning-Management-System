<?php
// index.php

require_once 'config/session.php';

// Redirect logged in users to their dashboard
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}

$page_title = 'Welcome to ' . APP_NAME;
include 'includes/header.php';
?>

<div class="container">
    <div class="hero">
        <h1>Welcome to <?php echo APP_NAME; ?></h1>
        <p>Your simple learning management system</p>
        <div class="hero-actions">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </div>
    </div>
    
    <div class="features">
        <div class="feature">
            <h3>📚 Learn Anywhere</h3>
            <p>Access your courses from any device, anytime.</p>
        </div>
        <div class="feature">
            <h3>📊 Track Progress</h3>
            <p>Monitor your learning progress and achievements.</p>
        </div>
        <div class="feature">
            <h3>🎓 Quality Content</h3>
            <p>Engaging lessons and resources to enhance learning.</p>
        </div>
    </div>
</div>

<style>
.hero {
    text-align: center;
    padding: 4rem 0;
    background: #fff;
    border-radius: 8px;
    margin: 2rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.hero h1 {
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.hero p {
    font-size: 1.2rem;
    color: #7f8c8d;
    margin-bottom: 2rem;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
}

.feature {
    text-align: center;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.feature h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.feature p {
    color: #7f8c8d;
}
</style>

<?php include 'includes/footer.php'; ?>