<?php
// pages/register.php - STRICT REGISTRATION
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

// Include auth class
require_once '../includes/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Check if user type is specified in URL
$user_type = isset($_GET['type']) && in_array($_GET['type'], ['customer', 'vendor']) ? $_GET['type'] : 'customer';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already exists. Please use a different email.';
        } else {
            // Additional data for vendors
            $additional_data = [];
            if ($user_type == 'vendor') {
                $business_name = trim($_POST['business_name'] ?? '');
                if (empty($business_name)) {
                    $error = 'Business name is required for vendor accounts.';
                } else {
                    $additional_data['business_name'] = $business_name;
                }
            }
            
            if (empty($error)) {
                // Register user
                if ($auth->register($username, $email, $password, $user_type, $additional_data)) {
                    // Login user after registration
                    if ($auth->login($email, $password)) {
                        // Set session variables
                        $_SESSION['user_id'] = $auth->getUserId();
                        $_SESSION['username'] = $auth->getUsername();
                        $_SESSION['user_type'] = $auth->getUserType();
                        
                        // After successful registration, set a welcome message in session
                        $_SESSION['welcome_message'] = 'Welcome, ' . htmlspecialchars($username) . '!';
                        
                        // Redirect to home page
                        header('Location: home.php');
                        exit();
                    } else {
                        $error = 'Registration successful but automatic login failed. Please try logging in.';
                    }
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShopSphere</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon-register.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages/auth.css">
    <!-- Add FontAwesome CDN for icons if not present -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="register-page">
    <div class="auth-container">
        <div class="auth-card animate-fade-in">
            <div class="auth-header">
                <div class="auth-logo">ShopSphere</div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join our community and start shopping</p>
            </div>
            <div class="auth-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="auth-form" id="registrationForm">
                    <div class="form-grid">
                        <div class="auth-form-group">
                            <label class="auth-form-label" for="username">Username *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="username" name="username" class="auth-form-input" required placeholder="Choose a username"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       minlength="3" maxlength="50">
                            </div>
                            <div class="form-hint">3-50 characters, letters, numbers, and underscores only.</div>
                        </div>
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
                                <input type="password" id="password" name="password" class="auth-form-input" required placeholder="Create a strong password" minlength="6">
                                <span id="togglePassword" class="password-toggle-icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="form-hint">Must be at least 6 characters.</div>
                        </div>
                        <div class="auth-form-group">
                            <label class="auth-form-label" for="user_type">Account Type *</label>
                            <select id="user_type" name="user_type" class="form-select" required onchange="toggleVendorField()">
                                <option value="customer" <?php echo $user_type == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="vendor" <?php echo $user_type == 'vendor' ? 'selected' : ''; ?>>Vendor</option>
                            </select>
                        </div>
                        <div class="auth-form-group business-name-field <?php echo $user_type == 'vendor' ? 'visible' : ''; ?>" id="business_name_field">
                            <label class="auth-form-label" for="business_name">Business Name *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-store"></i>
                                <input type="text" id="business_name" name="business_name" class="auth-form-input" placeholder="Your business name"
                                       value="<?php echo isset($_POST['business_name']) ? htmlspecialchars($_POST['business_name']) : ''; ?>"
                                       <?php echo $user_type == 'vendor' ? 'required' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="auth-form-submit">Create Account</button>
                    </div>
                </form>
                <div class="auth-back-link-container">
                    <a href="../landing.php" class="back-to-home-link"><i class="fas fa-arrow-left"></i> <span>Back to Home</span></a>
                </div>
            </div>
            <div class="auth-footer">
                <p class="auth-footer-text">
                    Already have an account? <a href="login.php" class="auth-footer-link">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
    <script>
    function toggleVendorField() {
        const userType = document.getElementById('user_type').value;
        const businessField = document.getElementById('business_name_field');
        const businessInput = document.getElementById('business_name');
        if (userType === 'vendor') {
            businessField.classList.add('visible');
            businessInput.setAttribute('required', 'required');
        } else {
            businessField.classList.remove('visible');
            businessInput.removeAttribute('required');
            businessInput.value = '';
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
</body>
</html>