<?php
// Ensure session is started so config.php can read session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once '../includes/config.php';

// Check if user is logged in
if (!$isLoggedIn || !$userId) {
    // Instead of redirecting, show a message if session is missing
    echo '<div class="container"><div class="alert alert-error">You must be logged in to view your profile. Please register or log in.</div></div>';
    require_once '../includes/footer.php';
    exit();
}

// If the logged-in user is a vendor, redirect to vendor profile
if (isset($userType) && strtolower($userType) === 'vendor') {
    header('Location: ' . BASE_URL . 'vendor/profile.php');
    exit();
}

// Get user data
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
    // Include header so the nav loads, then show a helpful message
    require_once '../includes/header.php';
    echo '<div class="container" style="margin-top:2rem;">';
    echo '<div class="alert alert-error">User profile not found. Please contact support.</div>';
    echo '</div>';
    require_once '../includes/footer.php';
    error_log('pages/profile.php: no user data for user_id ' . ($userId ?? 'N/A'));
    exit();
}

// Get user orders count
$orders_query = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = :user_id";
$orders_stmt = $db->prepare($orders_query);
$orders_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$orders_stmt->execute();
$orders_count = $orders_stmt->fetch(PDO::FETCH_ASSOC)['order_count'];

// Process profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    
    try {
        // Update user profile
        $update_query = "UPDATE user_profiles 
                        SET first_name = :first_name, last_name = :last_name, phone = :phone,
                            address = :address, city = :city, state = :state, 
                            country = :country, zip_code = :zip_code
                        WHERE user_id = :user_id";
        
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':first_name', $first_name);
        $update_stmt->bindParam(':last_name', $last_name);
        $update_stmt->bindParam(':phone', $phone);
        $update_stmt->bindParam(':address', $address);
        $update_stmt->bindParam(':city', $city);
        $update_stmt->bindParam(':state', $state);
        $update_stmt->bindParam(':country', $country);
        $update_stmt->bindParam(':zip_code', $zip_code);
        $update_stmt->bindParam(':user_id', $userId);
        
        if ($update_stmt->execute()) {
            $success_message = 'Profile updated successfully!';
            // Refresh user data
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = 'Failed to update profile. Please try again.';
        }
    } catch (Exception $e) {
        $error_message = 'Error updating profile: ' . $e->getMessage();
    }
}

// Include header
require_once '../includes/header.php';

// Add profile-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/profile.css">';
?>

<div class="profile-page">
    <div class="container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>

                <nav class="profile-nav">
                    <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-item active">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <div class="avatar-placeholder">
                    <a href="<?php echo BASE_URL; ?>pages/orders.php" class="nav-item">
                        </div>
                        <button class="avatar-upload-btn" title="Upload photo">
                            <i class="fas fa-camera"></i>
                    <a href="<?php echo BASE_URL; ?>pages/wishlist.php" class="nav-item">
                    </div>
                    
                    <div class="profile-info">
                    <a href="<?php echo BASE_URL; ?>pages/addresses.php" class="nav-item">
                        <p class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p class="profile-member">Member since <?php echo date('M Y', strtotime($user_data['created_at'])); ?></p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-item">
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $orders_count; ?></div>
                    <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-item">
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Reviews</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Wishlist</div>
                        </div>
                    </div>
                </div>
                
                <nav class="profile-nav">
                    <a href="profile.php" class="nav-item active">
                        <i class="fas fa-user"></i>
                        <span>Profile Information</span>
                    </a>
                    <a href="orders.php" class="nav-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My Orders</span>
                    </a>
                    <a href="wishlist.php" class="nav-item">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <a href="addresses.php" class="nav-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Addresses</span>
                    </a>
                    <a href="settings.php" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>

            <div class="profile-main">
                <div class="profile-section">
                    <h2 class="section-title">Personal Information</h2>
                    
                    <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required 
                                       value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required 
                                       value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                                <span class="form-note">Email cannot be changed</span>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($user_data['state'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input type="text" id="zip_code" name="zip_code" 
                                       value="<?php echo htmlspecialchars($user_data['zip_code'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <select id="country" name="country">
                                    <option value="">Select Country</option>
                                    <option value="US" <?php echo ($user_data['country'] ?? '') == 'US' ? 'selected' : ''; ?>>United States</option>
                                    <option value="UK" <?php echo ($user_data['country'] ?? '') == 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="CA" <?php echo ($user_data['country'] ?? '') == 'CA' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="AU" <?php echo ($user_data['country'] ?? '') == 'AU' ? 'selected' : ''; ?>>Australia</option>
                                    <option value="NG" <?php echo ($user_data['country'] ?? '') == 'NG' ? 'selected' : ''; ?>>Nigeria</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="reset" class="btn btn-outline">Reset</button>
                        </div>
                    </form>
                </div>

                <div class="profile-section">
                    <h2 class="section-title">Security</h2>
                    <div class="security-settings">
                        <div class="security-item">
                            <div class="security-info">
                                <h3>Change Password</h3>
                                <p>Update your password to keep your account secure</p>
                            </div>
                            <button class="btn btn-outline" onclick="openPasswordModal()">Change Password</button>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h3>Two-Factor Authentication</h3>
                                <p>Add an extra layer of security to your account</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" id="2fa-toggle" class="toggle-input">
                                <label for="2fa-toggle" class="toggle-label"></label>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h3>Login Activity</h3>
                                <p>View your recent login history and devices</p>
                            </div>
                            <button class="btn btn-outline">View Activity</button>
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
                            <div class="toggle-switch">
                                <input type="checkbox" id="email-toggle" class="toggle-input" checked>
                                <label for="email-toggle" class="toggle-label"></label>
                            </div>
                        </div>
                        
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>SMS Notifications</h3>
                                <p>Receive order updates via SMS</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" id="sms-toggle" class="toggle-input">
                                <label for="sms-toggle" class="toggle-label"></label>
                            </div>
                        </div>
                        
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>Marketing Communications</h3>
                                <p>Receive offers and promotional emails</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" id="marketing-toggle" class="toggle-input">
                                <label for="marketing-toggle" class="toggle-label"></label>
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
            <button class="modal-close" onclick="closePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form class="password-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                    <button type="button" class="btn btn-outline" onclick="closePasswordModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPasswordModal() {
    document.getElementById('passwordModal').classList.add('show');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.remove('show');
}

// Close modal when clicking outside
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});

// Toggle switches
document.querySelectorAll('.toggle-input').forEach(toggle => {
    toggle.addEventListener('change', function() {
        console.log('Toggle changed:', this.id, this.checked);
    });
});

// Form validation
document.querySelector('.profile-form').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'var(--danger-color)';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>