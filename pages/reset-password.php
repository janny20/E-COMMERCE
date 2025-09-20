<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$message = '';
$show_form = false;

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, reset_token_expires_at FROM users WHERE reset_token = :token LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $expires = new DateTime($user['reset_token_expires_at']);
            $now = new DateTime('now');

            if ($now > $expires) {
                $error = 'Your password reset token has expired. Please request a new one.';
            } else {
                $show_form = true;
            }
        } else {
            $error = 'Invalid reset token. Please check the link or request a new one.';
        }
    } catch (Exception $e) {
        error_log('Reset Password Error: ' . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $show_form) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || empty($confirm_password)) {
        $error = 'Please enter and confirm your new password.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE users SET password = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE reset_token = :token";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':password', $hashed_password);
        $update_stmt->bindParam(':token', $token);

        if ($update_stmt->execute()) {
            $message = 'Your password has been reset successfully! You can now log in with your new password.';
            $show_form = false;
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ShopSphere</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="auth-container">
        <div class="auth-card animate-fade-in">
            <div class="auth-header">
                <h1 class="auth-title">Reset Your Password</h1>
                <p class="auth-subtitle">Choose a new, strong password</p>
            </div>
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                    <div class="form-actions">
                        <a href="login.php" class="auth-form-submit">Go to Login</a>
                    </div>
                <?php elseif (!empty($error) && !$show_form): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($show_form): ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="" class="auth-form">
                        <div class="auth-form-group">
                            <label class="auth-form-label" for="password">New Password</label>
                            <input type="password" id="password" name="password" class="auth-form-input" required minlength="6">
                        </div>
                        <div class="auth-form-group">
                            <label class="auth-form-label" for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="auth-form-input" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="auth-form-submit">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>