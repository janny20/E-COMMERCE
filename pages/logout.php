<?php
// pages/logout.php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once '../includes/config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Process logout if confirmed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_logout'])) {
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Redirect to login page
    header('Location: login.php');
    exit();
}

// If user cancels logout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_logout'])) {
    // Redirect back to home page
    header('Location: home.php');
    exit();
}

// If user is not logged in but somehow reached this page
if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

// Include header
require_once '../includes/header.php';
echo '<link rel="stylesheet" href="../assets/css/pages/logout.css">';
?>

<div class="logout-container">
    <div class="logout-card">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h1 class="logout-title">Logout</h1>
        
        <p class="logout-message">
            Are you sure you want to logout from your account?<br>
            You'll need to login again to access your account.
        </p>

        <form method="POST" action="">
            <div class="logout-actions">
                <button type="submit" name="confirm_logout" class="logout-btn logout-btn-primary">
                    Yes, Logout
                </button>
                <button type="submit" name="cancel_logout" class="logout-btn logout-btn-outline">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>