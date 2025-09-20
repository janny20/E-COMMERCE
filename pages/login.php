<?php
// pages/login.php - STRICT AUTHENTICATION
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config first
require_once '../includes/config.php';

// Remove redirect for logged-in users so form always shows
/*
if ($isLoggedIn) {
    header('Location: home.php');
    exit();
}
*/

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $recaptcha_token = $_POST['g-recaptcha-response'] ?? '';
    // Verify reCAPTCHA v2
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LeZCs8rAAAAAIBTFXd1aeCwLtqYf859rPSqyWeI';
    $recaptcha_response = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_token);
    $recaptcha = json_decode($recaptcha_response, true);
    if (empty($recaptcha['success'])) {
        $error = 'reCAPTCHA verification failed. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill all fields.';
    } else {
        if ($auth->login($email, $password)) {
            // Set session variables
            $_SESSION['user_id'] = $auth->getUserId();
            $_SESSION['username'] = $auth->getUsername();
            $_SESSION['user_type'] = $auth->getUserType();
            
            // Redirect to home page after successful login
            header('Location: home.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopSphere</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon-login-new.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages/auth.css">
    <!-- Add FontAwesome CDN for icons if not present -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Google reCAPTCHA CSS -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="login-page">
    <div class="auth-container">
        <div class="auth-card animate-fade-in">
            <div class="auth-header">
                <div class="auth-logo">ShopSphere</div>
                <h1 class="auth-title">Login to Your Account</h1>
                <p class="auth-subtitle">Access your account and start shopping</p>
            </div>
            <div class="auth-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="auth-form" id="loginForm">
                    <div class="form-grid">
                        <div class="auth-form-group">
                            <label class="auth-form-label" for="email">Email Address *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="auth-form-input" required placeholder="e.g., your.email@example.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        <div class="auth-form-group">
                            <label class="auth-form-label" for="password">Password *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" class="auth-form-input" required placeholder="Enter your password">
                                <span id="togglePassword" class="password-toggle-icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="auth-form-forgot">
                                <a href="forgot-password.php" class="auth-form-forgot-link">Forgot Password?</a>
                            </div>
                        </div>
                    </div>
                    <!-- Google reCAPTCHA v2 Checkbox widget -->
                    <div class="auth-recaptcha">
                        <div class="g-recaptcha" data-sitekey="6LeZCs8rAAAAAFZJNWRINV5zDyNlXMsxEnzJsJdO"></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="auth-form-submit">Login</button>
                    </div>
                </form>
                <div class="auth-back-link-container">
                    <a href="../landing.php" class="back-to-home-link"><i class="fas fa-arrow-left"></i> <span>Back to Home</span></a>
                </div>
            </div>
            <div class="auth-footer">
                <p class="auth-footer-text">
                    Don't have an account? <a href="register.php" class="auth-footer-link">Create one here</a>
                </p>
            </div>
        </div>
    </div>
    <script>
    // Show/hide password toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pwd = document.getElementById('password');
        const icon = this.querySelector('i');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    </script>
</body>
</html>