0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// --- PAGE LOGIC ---

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php?redirect=settings.php');
    exit();
}

// If the logged-in user is a vendor, redirect to vendor profile
if (isset($_SESSION['user_type']) && strtolower($_SESSION['user_type']) === 'vendor') {
    header('Location: ' . BASE_URL . 'vendor/profile.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user data for display
$database = new Database();
$db = $database->getConnection();

$query = "SELECT u.*, up.* 
          FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE u.id = :user_id";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    // This case is unlikely if the user is logged in, but it's a good safeguard.
    require_once '../includes/header.php';
    echo '<div class="container" style="margin-top:2rem;"><div class="alert alert-danger">Could not load user profile. Please try again later or contact support.</div></div>';
    require_once '../includes/footer.php';
    error_log('CRITICAL: User profile data not found for logged-in user_id: ' . $userId);
    exit();
}

// Get user stats for the profile card
$orders_stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :user_id");
$orders_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$orders_stmt->execute();
$orders_count = $orders_stmt->fetchColumn();

$reviews_stmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = :user_id");
$reviews_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$reviews_stmt->execute();
$reviews_count = $reviews_stmt->fetchColumn();

$wishlist_stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = :user_id");
$wishlist_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$wishlist_stmt->execute();
$wishlist_count = $wishlist_stmt->fetchColumn();

// Include header
require_once '../includes/header.php';

// Add profile-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/profile.css">';
?>

<div class="profile-page">
    <div class="container">
        <div class="profile-header">
            <h1>Settings</h1>
            <p>Manage your security and notification preferences</p>
        </div>

        <div class="profile-content">
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php if (!empty($user_data['avatar'])): ?>
                            <img src="<?php echo BASE_URL . 'uploads/users/' . htmlspecialchars($user_data['avatar']); ?>" alt="User Avatar" class="avatar-image">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($user_data['username'] ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars(trim($user_data['first_name'] . ' ' . $user_data['last_name'])) ?: htmlspecialchars($user_data['username']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                </div>

                <nav class="profile-nav">
                    <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-item">
                        <i class="fas fa-user"></i>
                        <span>Profile Information</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/orders.php" class="nav-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My Orders (<?php echo $orders_count; ?>)</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/my-reviews.php" class="nav-item">
                        <i class="fas fa-star"></i>
                        <span>My Reviews (<?php echo $reviews_count; ?>)</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/wishlist.php" class="nav-item">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-item active">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </aside>

            <div class="profile-main">
                <div class="profile-section">
                    <h2 class="section-title">Security</h2>
                    <div class="security-settings">
                        <div class="security-item">
                            <div class="security-info">
                                <h3>Change Password</h3>
                                <p>Update your password to keep your account secure</p>
                            </div>
                            <div class="preference-actions">
                                <button class="btn btn-outline" id="openPasswordModalBtn">Change Password</button>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h3>Two-Factor Authentication</h3>
                                <p>Add an extra layer of security to your account</p>
                            </div>
                            <div class="preference-actions">
                                <span class="preference-status"></span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="2fa_toggle" class="toggle-input" disabled>
                                    <label for="2fa_toggle" class="toggle-label"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h2 class="section-title">Account Preferences</h2>
                    <div class="preferences-settings">
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>Email Notifications</h3>
                                <p>Manage what emails you receive from us</p>
                            </div>
                            <div class="preference-actions">
                                <div class="toggle-switch">
                                    <input type="checkbox" id="email_notifications" class="toggle-input preference-toggle" data-setting="email_notifications" <?php echo !empty($user_data['email_notifications']) ? 'checked' : ''; ?>>
                                    <label for="email_notifications" class="toggle-label"></label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>SMS Notifications</h3>
                                <p>Receive order updates via SMS</p>
                            </div>
                            <div class="preference-actions">
                                <div class="toggle-switch">
                                    <input type="checkbox" id="sms_notifications" class="toggle-input preference-toggle" data-setting="sms_notifications" <?php echo !empty($user_data['sms_notifications']) ? 'checked' : ''; ?>>
                                    <label for="sms_notifications" class="toggle-label"></label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>Marketing Communications</h3>
                                <p>Receive offers and promotional emails</p>
                            </div>
                            <div class="preference-actions">
                                <div class="toggle-switch">
                                    <input type="checkbox" id="marketing_communications" class="toggle-input preference-toggle" data-setting="marketing_communications" <?php echo !empty($user_data['marketing_communications']) ? 'checked' : ''; ?>>
                                    <label for="marketing_communications" class="toggle-label"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Modal -->
<div class="modal" id="passwordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button class="modal-close" id="passwordModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div id="password-modal-messages"></div>
            <form class="password-form" id="passwordChangeForm">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                    <button type="button" class="btn btn-outline" id="passwordModalCancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/pages/settings.js"></script>

<?php
require_once '../includes/footer.php';
?>