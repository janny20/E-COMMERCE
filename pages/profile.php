<?php
// Ensure session is started so config.php can read session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once '../includes/config.php';
require_once '../includes/functions.php'; // Include functions for file upload

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

// Handle AJAX avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['avatar'], USER_UPLOAD_PATH);
        
        if ($upload_result['success']) {
            $avatar_filename = $upload_result['file_name'];
            
            $update_avatar_query = "UPDATE user_profiles SET avatar = :avatar WHERE user_id = :user_id";
            $update_avatar_stmt = $db->prepare($update_avatar_query);
            $update_avatar_stmt->bindParam(':avatar', $avatar_filename);
            $update_avatar_stmt->bindParam(':user_id', $userId);
            
            if ($update_avatar_stmt->execute()) {
                $_SESSION['avatar'] = $avatar_filename;
                $response['success'] = true;
                $response['message'] = 'Avatar updated successfully!';
                $response['file_name'] = $avatar_filename;
            } else {
                $response['message'] = 'Failed to update avatar in the database.';
            }
        } else {
            $response['message'] = $upload_result['message'];
        }
    } else {
        $response['message'] = 'No file was uploaded or an upload error occurred.';
    }
    
    echo json_encode($response);
    exit();
}

// Handle standard form submission for profile info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $zip_code = trim($_POST['zip_code'] ?? '');

        try {
            $update_query = "UPDATE user_profiles SET first_name = :first_name, last_name = :last_name, phone = :phone, address = :address, city = :city, state = :state, country = :country, zip_code = :zip_code WHERE user_id = :user_id";
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
                $stmt->execute();
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                // Update session variable to reflect the name change in the header
                $_SESSION['username'] = $user_data['first_name'];
            } else {
                $error_message = 'Failed to update profile. Please try again.';
            }
        } catch (Exception $e) {
            $error_message = 'Error updating profile: ' . $e->getMessage();
        }
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

        <div class="profile-content">
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <form id="avatarForm" method="POST" enctype="multipart/form-data" action="">
                        <div class="profile-avatar">
                            <label for="avatar-upload-input" class="avatar-upload-label" title="Change profile photo">
                                <?php if (!empty($user_data['avatar'])): ?>
                                    <img src="<?php echo BASE_URL . 'uploads/users/' . htmlspecialchars($user_data['avatar']); ?>" alt="User Avatar" class="avatar-image">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($user_data['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="avatar-upload-btn"><i class="fas fa-camera"></i></div>
                            </label>
                            <input type="file" id="avatar-upload-input" name="avatar" accept="image/*" style="display: none;">
                            <input type="hidden" name="update_avatar" value="1">
                        </div>
                    </form>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p class="profile-member">Member since <?php echo date('M Y', strtotime($user_data['created_at'])); ?></p>
                    </div>
                </div>
                
                <nav class="profile-nav">
                    <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-item active">
                        <i class="fas fa-user"></i>
                        <span>Profile Information</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/orders.php" class="nav-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My Orders (<?php echo $orders_count; ?>)</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/wishlist.php" class="nav-item">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-item">
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
                    <h2 class="section-title">Personal Information</h2>

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
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
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
                            <div class="preference-actions">
                                <button class="btn btn-outline" onclick="openPasswordModal()">Change Password</button>
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
                                <span class="preference-status"></span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="email_notifications" class="toggle-input" <?php echo !empty($user_data['email_notifications']) ? 'checked' : ''; ?>>
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
                                <span class="preference-status"></span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="sms_notifications" class="toggle-input" <?php echo !empty($user_data['sms_notifications']) ? 'checked' : ''; ?>>
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
                                <span class="preference-status"></span>
                                <div class="toggle-switch">
                                    <input type="checkbox" id="marketing_communications" class="toggle-input" <?php echo !empty($user_data['marketing_communications']) ? 'checked' : ''; ?>>
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

<script>
// Professional form validation and submission handling
document.addEventListener('DOMContentLoaded', function() {
    // --- Password Modal ---
    const passwordModal = document.getElementById('passwordModal');
    const openModalBtn = document.querySelector('button[onclick="openPasswordModal()"]');
    const closeModalBtn = document.getElementById('passwordModalClose');
    const cancelModalBtn = document.getElementById('passwordModalCancel');

    const openPasswordModal = () => passwordModal.classList.add('show');
    const closePasswordModal = () => passwordModal.classList.remove('show');

    if (openModalBtn) openModalBtn.addEventListener('click', openPasswordModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closePasswordModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closePasswordModal);
    if (passwordModal) {
        passwordModal.addEventListener('click', (e) => {
            if (e.target === passwordModal) closePasswordModal();
        });
    }

    // --- Avatar Auto-Upload ---
    const avatarInput = document.getElementById('avatar-upload-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const avatarContainer = document.querySelector('.profile-avatar');
                const formData = new FormData(document.getElementById('avatarForm'));
                
                avatarContainer.classList.add('uploading');

                fetch('profile.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newAvatarUrl = '<?php echo BASE_URL; ?>uploads/users/' + data.file_name;
                        const cacheBuster = '?t=' + new Date().getTime();

                        // Update profile page avatar
                        const profileImg = document.querySelector('.avatar-image');
                        if (profileImg) {
                            profileImg.src = newAvatarUrl + cacheBuster;
                        } else {
                            const placeholder = document.querySelector('.avatar-placeholder');
                            if (placeholder) {
                                const newImg = document.createElement('img');
                                newImg.src = newAvatarUrl;
                                newImg.alt = 'User Avatar';
                                newImg.className = 'avatar-image';
                                placeholder.replaceWith(newImg);
                            }
                        }

                        // Update header avatar
                        const headerAvatar = document.querySelector('.header-avatar');
                        if (headerAvatar) {
                            headerAvatar.src = newAvatarUrl + cacheBuster;
                        } else {
                            const headerPlaceholder = document.querySelector('.header-avatar-placeholder');
                            if (headerPlaceholder) {
                                const newHeaderImg = document.createElement('img');
                                newHeaderImg.src = newAvatarUrl;
                                newHeaderImg.alt = 'Profile';
                                newHeaderImg.className = 'header-avatar';
                                headerPlaceholder.replaceWith(newHeaderImg);
                            }
                        }

                        showNotification('Avatar updated successfully!', 'success');
                    } else {
                        showNotification(data.message || 'An error occurred during upload.', 'error');
                    }
                })
                .catch(error => {
                    showNotification('A network error occurred. Please try again.', 'error');
                    console.error('Upload Error:', error);
                })
                .finally(() => {
                    avatarContainer.classList.remove('uploading');
                });
            }
        });
    }

    // --- Password Change Form ---
    const passwordForm = document.getElementById('passwordChangeForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const messageContainer = document.getElementById('password-modal-messages');

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"></span> Updating...';
            messageContainer.innerHTML = '';

            const formData = new FormData(form);
            formData.append('change_password_ajax', '1');

            fetch('profile.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                const alertClass = data.success ? 'alert-success' : 'alert-error';
                messageContainer.innerHTML = `<div class="alert ${alertClass}">${data.message}</div>`;
                
                if (data.success) {
                    form.reset();
                    setTimeout(() => {
                        closePasswordModal();
                        messageContainer.innerHTML = '';
                    }, 2000);
                }
            })
            .catch(error => {
                messageContainer.innerHTML = `<div class="alert alert-error">A network error occurred. Please try again.</div>`;
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Update Password';
            });
        });
    }

    // --- Toggle Switches ---
    document.querySelectorAll('.toggle-input').forEach(toggle => {
        if (toggle.disabled) return;

        toggle.addEventListener('change', function() {
            const settingId = this.id;
            const isChecked = this.checked;
            const preferenceItem = this.closest('.preference-item, .security-item');
            const statusSpan = preferenceItem ? preferenceItem.querySelector('.preference-status') : null;

            if (statusSpan) {
                statusSpan.textContent = 'Saving...';
                statusSpan.style.color = 'var(--text-light)';
            }

            const formData = new FormData();
            formData.append('update_preferences_ajax', '1');
            formData.append('setting', settingId);
            formData.append('value', isChecked);

            fetch('profile.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (statusSpan) {
                    if (data.success) {
                        statusSpan.textContent = 'Saved!';
                        statusSpan.style.color = 'var(--success-color)';
                    } else {
                        statusSpan.textContent = 'Error';
                        statusSpan.style.color = 'var(--danger-color)';
                    }
                    setTimeout(() => { statusSpan.textContent = ''; }, 2000);
                }
            })
            .catch(error => {
                if (statusSpan) { statusSpan.textContent = 'Error'; statusSpan.style.color = 'var(--danger-color)'; }
                console.error('Error:', error);
            });
        });
    });

    // --- Profile Info Form ---
    const profileForm = document.querySelector('.profile-form');
    if (!profileForm) return;

    profileForm.addEventListener('submit', function(e) {
        const form = this;
        const submitButton = form.querySelector('button[name="update_profile"]');
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        // Clear previous client-side errors
        const existingError = form.parentElement.querySelector('.client-side-error');
        if (existingError) {
            existingError.remove();
        }

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger-color)';
                isValid = false;
            } else {
                field.style.borderColor = ''; // Reset border color
            }
        });

        if (!isValid) {
            e.preventDefault();
            
            // Create and display a professional error message
            const errorMessageDiv = document.createElement('div');
            errorMessageDiv.className = 'alert alert-error client-side-error';
            errorMessageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please fill in all required fields.';
            
            // Insert the message before the form
            form.parentElement.insertBefore(errorMessageDiv, form);
            errorMessageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            // If form is valid, show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"></span> Saving...';
        }
    });
    
    const resetButton = profileForm.querySelector('button[type="reset"]');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to discard your changes? This cannot be undone.')) {
                profileForm.reset(); // Manually trigger the form reset if confirmed
            }
        });
    });
});
</script>

<?php
// Display notifications for form submissions
if (!empty($success_message)) {
    echo "<script>document.addEventListener('DOMContentLoaded', function() { showNotification('" . addslashes($success_message) . "', 'success'); });</script>";
}
if (!empty($error_message)) {
    echo "<script>document.addEventListener('DOMContentLoaded', function() { showNotification('" . addslashes($error_message) . "', 'error'); });</script>";
}
?>

<?php
// Include footer
require_once '../includes/footer.php';
?>