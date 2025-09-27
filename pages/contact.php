0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

define('ADMIN_EMAIL', 'admin@example.com'); // IMPORTANT: Change this to your actual admin email

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $to = ADMIN_EMAIL;
        $email_subject = "New Contact Form Submission: " . $subject;
        
        $email_body = "You have received a new message from your website contact form.\n\n";
        $email_body .= "Here are the details:\n\n";
        $email_body .= "Name: $name\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Subject: $subject\n";
        $email_body .= "Message:\n$message\n";

        $headers = "From: noreply@yourdomain.com\r\n"; // Use a no-reply address for your domain
        $headers .= "Reply-To: $email\r\n";

        // For a production environment, it's highly recommended to use a library like PHPMailer
        // as the mail() function can be unreliable.
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success_message = 'Thank you for your message! We will get back to you shortly.';
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again later.';
            error_log("Mail function failed to send email from contact form.");
        }
    }
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/contact.css">

<main class="contact-page">
    <div class="contact-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you! Whether you have a question, feedback, or need assistance, our team is ready to help.</p>
        </div>
    </div>

    <div class="container">
        <div class="contact-content">
            <div class="contact-form-container">
                <h2>Send us a Message</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (!$success_message): ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="contact-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email *</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group full-width">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                        </div>
                        <div class="form-group full-width">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <div class="contact-info-container">
                <h2>Contact Information</h2>
                <p>You can also reach us through the following channels:</p>
                <ul class="contact-info-list">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>123 Market Street, Suite 456, Anytown, USA 12345</span>
                    </li>
                    <li>
                        <i class="fas fa-phone-alt"></i>
                        <a href="tel:+1234567890">+1 (234) 567-890</a>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:support@example.com">support@example.com</a>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Mon - Fri: 9:00 AM - 6:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>