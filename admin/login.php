<?php
// Set session cookie lifetime to 30 days for admin login
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30, // 30 days
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth = new Auth();
$error = '';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header('Location: admin-dashboard.php');
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields.';
    } else {
        // Check if user exists and is an admin
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT u.* FROM users u WHERE u.email = :email AND u.user_type = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                header('Location: admin-dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
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
    <title>Admin Login - ShopSphere</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon-login-new.svg">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-login.css?v=20250918">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-login">
    <div class="login-container">
        <div class="login-card">
            <div class="login-branding">
                <div class="branding-content">
                    <div class="login-logo">UniMall Admin</div>
                    <h2>Platform Management</h2>
                    <p>Secure access to the UniMall administration panel.</p>
                </div>
            </div>
            <div class="login-form-wrapper">
                <div class="login-header">
                    <h1>Admin Login</h1>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Email Address" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">Login</button>

                    <div class="test-credentials">
                        <p><strong>For Testing:</strong></p>
                        <p>Email: <code>admin@example.com</code></p>
                        <p>Password: <code>password</code></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>