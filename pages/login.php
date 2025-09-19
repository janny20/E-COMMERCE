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
    // Verify reCAPTCHA v3
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LeCBM8rAAAAAKgoK7zTnlTDCxeUdiLME1OPeZ4j';
    $recaptcha_response = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_token);
    $recaptcha = json_decode($recaptcha_response, true);
    if (empty($recaptcha['success']) || $recaptcha['score'] < 0.5) {
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
        <div class="auth-card animate-fade-in" style="max-width:480px;width:100%;margin:0 auto;">
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
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-input" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Password *</label>
                            <div style="position:relative;">
                                <input type="password" id="password" name="password" class="form-input" required style="padding-right:40px;">
                                <span id="togglePassword" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);cursor:pointer;color:#888;font-size:1.2rem;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full">Login</button>
                    </div>
                    <!-- Back arrow at the bottom of the form, styled as a real arrow -->
                    <div style="display:flex;justify-content:center;align-items:center;margin-top:12px;">
                        <a href="../landing.php" class="back-arrow" style="display:inline-flex;align-items:center;gap:8px;color:#222;font-size:1.1rem;text-decoration:none;font-weight:500;">
                            <i class="fas fa-arrow-left" style="font-size:1.5rem;"></i>
                            <span style="font-size:0.95rem;">Back to Home</span>
                        </a>
                    </div>
                </form>
            </div>
            <div class="auth-footer">
                <p class="auth-footer-text">
                    Don't have an account? <a href="register.php" class="auth-footer-link">Create one here</a>
                </p>
                <p><a href="forgot-password.php">Forgot your password?</a></p>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        if (!email.value.trim() || !password.value.trim()) {
            e.preventDefault();
            alert('Please fill all fields.');
        }
    });
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
    <script src="https://www.google.com/recaptcha/api.js?render=6LeCBM8rAAAAAHI7h5xUIvM46nOnAiZumCWLP3S6"></script>
    <script>
    grecaptcha.ready(function() {
        grecaptcha.execute('6LeCBM8rAAAAAHI7h5xUIvM46nOnAiZumCWLP3S6', {action: 'login'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
    </script>
    <style>
    .animate-fade-in {
        animation: fadeInUp 0.7s cubic-bezier(.23,1,.32,1);
    }
    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(40px) scale(0.98);
        }
        60% {
            opacity: 1;
            transform: translateY(-8px) scale(1.02);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    .auth-card {
        box-shadow: 0 8px 32px rgba(0,0,0,0.10);
        transition: box-shadow 0.3s;
    }
    .auth-card:hover {
        box-shadow: 0 16px 48px rgba(0,0,0,0.16);
    }
    .btn, .form-input, .form-select {
        transition: box-shadow 0.2s, border-color 0.2s, background 0.2s;
    }
    .btn:hover {
        box-shadow: 0 2px 8px rgba(0,123,255,0.12);
        background: #0056b3;
    }
    .form-input:focus, .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.10);
    }
    </style>
</body>
</html>