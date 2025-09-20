<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
// NOTE: You will need a mail sending library or a configured mail function.
// require_once '../includes/mail.php'; 

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT id, last_reset_request_at FROM users WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Rate limiting: check if a request was made in the last 5 minutes
                $rate_limit_seconds = 300; // 5 minutes
                if ($user['last_reset_request_at'] !== null) {
                    $last_request_time = new DateTime($user['last_reset_request_at']);
                    $now = new DateTime('now');
                    $interval = $now->getTimestamp() - $last_request_time->getTimestamp();

                    if ($interval < $rate_limit_seconds) {
                        $wait_time = ceil(($rate_limit_seconds - $interval) / 60);
                        $error = "You have requested a password reset recently. Please wait about " . $wait_time . " minute(s) before trying again.";
                    }
                }

                if (empty($error)) {
                    $token = bin2hex(random_bytes(50));
                    $expires = new DateTime('now');
                    $expires->add(new DateInterval('PT1H')); // 1 hour expiry
                    $now_formatted = (new DateTime('now'))->format('Y-m-d H:i:s');

                    $update_query = "UPDATE users SET reset_token = :token, reset_token_expires_at = :expires, last_reset_request_at = :now WHERE email = :email";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(':token', $token);
                    $update_stmt->bindParam(':expires', $expires->format('Y-m-d H:i:s'));
                    $update_stmt->bindParam(':now', $now_formatted);
                    $update_stmt->bindParam(':email', $email);

                    if ($update_stmt->execute()) {
                        $reset_link = BASE_URL . 'pages/reset-password.php?token=' . $token;
                        
                        // For a real application, use a robust email library
                        $subject = 'Password Reset Request for ShopSphere';
                        ob_start();
                        include '../includes/templates/email/password_reset.php';
                        $email_body = ob_get_clean();
                        $headers = "From: no-reply@shopsphere.com\r\n";
                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                        
                        // mail($email, $subject, $email_body, $headers);
                    }
                }
            }
            
            // To prevent user enumeration, always show a generic success message if there wasn't a specific error like rate limiting.
            if (empty($error)) {
                $message = 'If an account with that email exists, a password reset link has been sent.';
            }

        } catch (Exception $e) {
            error_log('Forgot Password Error: ' . $e->getMessage());
            $error = 'Could not process your request. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ShopSphere</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pages/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="auth-container">
        <div class="auth-card animate-fade-in">
            <div class="auth-header">
                <h1 class="auth-title">Forgot Your Password?</h1>
                <p class="auth-subtitle">Enter your email to receive a reset link</p>
            </div>
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="" class="auth-form">
                    <div class="auth-form-group">
                        <label class="auth-form-label" for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="auth-form-input" required placeholder="e.g., your.email@example.com">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="auth-form-submit">Send Reset Link</button>
                    </div>
                </form>
                <div class="auth-back-link-container">
                    <a href="login.php" class="back-to-home-link"><i class="fas fa-arrow-left"></i> <span>Back to Login</span></a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.auth-form');
            const submitButton = form.querySelector('.auth-form-submit');

            if (form && submitButton) {
                form.addEventListener('submit', function() {
                    // Check if form is valid before showing spinner
                    if (form.checkValidity()) {
                        submitButton.classList.add('loading');
                        submitButton.disabled = true;
                    }
                });
            }
        });
    </script>
</body>
</html>