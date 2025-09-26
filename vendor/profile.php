<?php
// vendor/profile.php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../pages/login.php');
    exit();
}

// Include config
require_once '../includes/config.php';

<<<<<<< HEAD
=======
// Handle AJAX logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_logo'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['logo'], VENDOR_UPLOAD_PATH);
        
        if ($upload_result['success']) {
            $logo_filename = $upload_result['file_name'];
            
            // Get old logo to delete it after successful update
            $old_logo_query = "SELECT business_logo FROM vendors WHERE user_id = :user_id";
            $old_logo_stmt = $db->prepare($old_logo_query);
            $old_logo_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $old_logo_stmt->execute();
            $old_logo = $old_logo_stmt->fetchColumn();

            // Update the database
            $update_logo_query = "UPDATE vendors SET business_logo = :logo WHERE user_id = :user_id";
            $update_logo_stmt = $db->prepare($update_logo_query);
            $update_logo_stmt->bindParam(':logo', $logo_filename);
            $update_logo_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            if ($update_logo_stmt->execute()) {
                // Delete old logo if it exists and is not a default one
                if ($old_logo && file_exists(VENDOR_UPLOAD_PATH . $old_logo)) {
                    unlink(VENDOR_UPLOAD_PATH . $old_logo);
                }
                $_SESSION['avatar'] = $logo_filename;
                $response['success'] = true;
                $response['message'] = 'Logo updated successfully!';
                $response['file_name'] = $logo_filename;
            } else {
                if (file_exists(VENDOR_UPLOAD_PATH . $logo_filename)) {
                    unlink(VENDOR_UPLOAD_PATH . $logo_filename);
                }
                $response['message'] = 'Failed to update logo in the database.';
            }
        } else {
            $response['message'] = $upload_result['message'];
        }
    } else {
        $response['message'] = 'An upload error occurred. Please select a valid image file.';
    }
    
    echo json_encode($response);
    exit();
}

// Handle AJAX banner upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_banner'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['banner'], VENDOR_UPLOAD_PATH);
        
        if ($upload_result['success']) {
            $banner_filename = $upload_result['file_name'];
            
            // Get old banner to delete it
            $old_banner_query = "SELECT banner_image FROM vendors WHERE user_id = :user_id";
            $old_banner_stmt = $db->prepare($old_banner_query);
            $old_banner_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $old_banner_stmt->execute();
            $old_banner = $old_banner_stmt->fetchColumn();

            // Update the database
            $update_banner_query = "UPDATE vendors SET banner_image = :banner WHERE user_id = :user_id";
            $update_banner_stmt = $db->prepare($update_banner_query);
            $update_banner_stmt->bindParam(':banner', $banner_filename);
            $update_banner_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            if ($update_banner_stmt->execute()) {
                // Delete old banner if it exists
                if ($old_banner && file_exists(VENDOR_UPLOAD_PATH . $old_banner)) {
                    unlink(VENDOR_UPLOAD_PATH . $old_banner);
                }
                $response['success'] = true;
                $response['message'] = 'Banner updated successfully!';
                $response['file_name'] = $banner_filename;
            } else {
                // If DB update fails, delete the newly uploaded file
                if (file_exists(VENDOR_UPLOAD_PATH . $banner_filename)) {
                    unlink(VENDOR_UPLOAD_PATH . $banner_filename);
                }
                $response['message'] = 'Failed to update banner in the database.';
            }
        } else {
            $response['message'] = $upload_result['message'];
        }
    } else {
        $response['message'] = 'An upload error occurred. Please select a valid image file.';
    }
    
    echo json_encode($response);
    exit();
}

// Handle AJAX password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password_ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];
    $database = new Database();
    $db = $database->getConnection();

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $response['message'] = 'Please fill in all password fields.';
    } elseif (strlen($new_password) < 6) {
        $response['message'] = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match.';
    } else {
        $pass_query = "SELECT password FROM users WHERE id = :user_id";
        $pass_stmt = $db->prepare($pass_query);
        $pass_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $pass_stmt->execute();
        $user_pass_data = $pass_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_pass_data && password_verify($current_password, $user_pass_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_query = "UPDATE users SET password = :password WHERE id = :user_id";
            $update_pass_stmt = $db->prepare($update_pass_query);
            $update_pass_stmt->bindParam(':password', $hashed_password);
            $update_pass_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

            if ($update_pass_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Password updated successfully!';
            } else {
                $response['message'] = 'Failed to update password. Please try again.';
            }
        } else {
            $response['message'] = 'Incorrect current password.';
        }
    }
    
    echo json_encode($response);
    exit();
}

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
// Get vendor data
$database = new Database();
$db = $database->getConnection();

$query = "SELECT v.*, u.email, u.username 
          FROM vendors v 
          JOIN users u ON v.user_id = u.id 
          WHERE v.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$vendor || !isset($vendor['id'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Vendor Profile</title>';
    echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/style.css">';
    echo '</head><body>';
    echo '<div class="container" style="margin-top:2rem;">';
    echo '<div class="alert alert-danger" style="padding:2rem;text-align:center;">Vendor account not found. Please contact support.</div>';
    echo '<a href="' . BASE_URL . 'vendor/dashboard.php" class="btn btn-primary" style="margin-top:1rem;">Back to Dashboard</a>';
    echo '</div></body></html>';
    error_log('Vendor profile page: vendor not found for user_id ' . ($_SESSION['user_id'] ?? 'N/A'));
    exit();
}

// Process form submission
<<<<<<< HEAD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
=======
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vendor_profile'])) {
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    $business_name = trim($_POST['business_name']);
    $business_description = trim($_POST['business_description']);
    $business_address = trim($_POST['business_address']);
    $business_phone = trim($_POST['business_phone']);
    $support_email = trim($_POST['support_email']);
<<<<<<< HEAD
=======
    
    // New store settings
    $store_status = trim($_POST['store_status']);
    $processing_time = trim($_POST['processing_time']);
    $shipping_policy = trim($_POST['shipping_policy']);
    $return_policy = trim($_POST['return_policy']);
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b

    $update_query = "UPDATE vendors SET 
                    business_name = :business_name,
                    business_description = :business_description,
                    business_address = :business_address,
                    business_phone = :business_phone,
                    support_email = :support_email,
<<<<<<< HEAD
=======
                    store_status = :store_status,
                    processing_time = :processing_time,
                    shipping_policy = :shipping_policy,
                    return_policy = :return_policy,
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                    updated_at = NOW()
                    WHERE user_id = :user_id";

    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':business_name', $business_name);
    $stmt->bindParam(':business_description', $business_description);
    $stmt->bindParam(':business_address', $business_address);
    $stmt->bindParam(':business_phone', $business_phone);
    $stmt->bindParam(':support_email', $support_email);
<<<<<<< HEAD
=======
    $stmt->bindParam(':store_status', $store_status);
    $stmt->bindParam(':processing_time', $processing_time);
    $stmt->bindParam(':shipping_policy', $shipping_policy);
    $stmt->bindParam(':return_policy', $return_policy);
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    $stmt->bindParam(':user_id', $_SESSION['user_id']);

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh vendor data
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
<<<<<<< HEAD
=======

        // Update session username to reflect the change immediately
        $_SESSION['username'] = $business_name;
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}

// Include header
require_once '../includes/header.php';
?>

<<<<<<< HEAD
=======
<!-- Add profile-specific CSS for consistency -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/profile.css">
<style>
    .banner-upload-section {
        background: var(--bg-white);
        border-radius: var(--border-radius);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        box-shadow: var(--shadow-sm);
    }
    .banner-upload-section h3 {
        font-size: var(--font-size-lg);
        margin-bottom: var(--spacing-xs);
    }
    .banner-upload-section p {
        margin-bottom: var(--spacing-lg);
        color: var(--text-secondary);
        font-size: var(--font-size-md);
    }
    .banner-preview {
        position: relative;
        width: 100%;
        height: 250px;
        border-radius: var(--border-radius);
        overflow: hidden;
        background-color: var(--bg-light);
        border: 2px dashed var(--border-color);
    }
    .banner-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .banner-upload-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.6);
        color: white;
        padding: var(--spacing-md) var(--spacing-lg);
        border-radius: var(--border-radius);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 5;
    }
    .banner-preview:hover .banner-upload-label {
        opacity: 1;
    }
    .banner-preview.uploading::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); z-index: 10; }
    .banner-preview.uploading::before { content: ''; position: absolute; top: 50%; left: 50%; width: 40px; height: 40px; margin-top: -20px; margin-left: -20px; border: 4px solid var(--border-color); border-top-color: var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; z-index: 11; }
</style>

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
<div class="vendor-profile">
    <div class="container">
        <div class="profile-header">
            <h1>Vendor Profile</h1>
            <p>Manage your vendor account information</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
<<<<<<< HEAD
            <div class="profile-avatar">
                <img src="<?php echo BASE_URL; ?>assets/images/vendors/<?php echo $vendor['business_logo'] ?: 'default-logo.png'; ?>" alt="<?php echo htmlspecialchars($vendor['business_name']); ?>" class="avatar-image">
                <label for="logo-upload" class="avatar-upload">
                    <i class="fas fa-camera"></i>
                    <input type="file" id="logo-upload" accept="image/*" style="display: none;">
                </label>
            </div>
=======
            <form id="logoForm" method="POST" enctype="multipart/form-data">
                <div class="profile-avatar">
                    <label for="logo-upload" class="avatar-upload-label" title="Change business logo">
                        <img src="<?php echo BASE_URL; ?>uploads/vendors/<?php echo htmlspecialchars($vendor['business_logo'] ?: 'default-logo.png'); ?>" alt="<?php echo htmlspecialchars($vendor['business_name']); ?>" class="avatar-image">
                        <div class="avatar-upload-btn"><i class="fas fa-camera"></i></div>
                    </label>
                    <input type="file" id="logo-upload" name="logo" accept="image/*" style="display: none;">
                    <input type="hidden" name="update_logo" value="1">
                </div>
            </form>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
            
            <div class="profile-info">
                <h2 class="profile-name"><?php echo htmlspecialchars($vendor['business_name']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($vendor['email']); ?></p>
                <p class="profile-join-date">Vendor since <?php echo date('M Y', strtotime($vendor['created_at'])); ?></p>
            </div>
            
            <div class="profile-stats">
                <div class="stat">
                    <?php
                    $products_query = "SELECT COUNT(*) as count FROM products WHERE vendor_id = :vendor_id";
                    $products_stmt = $db->prepare($products_query);
                    $products_stmt->bindParam(':vendor_id', $vendor['id'], PDO::PARAM_INT);
                    $products_stmt->execute();
                    $products_count = $products_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    ?>
                    <div class="stat-number"><?php echo $products_count; ?></div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat">
                    <?php
                    $orders_query = "SELECT COUNT(DISTINCT oi.order_id) as count 
                                   FROM order_items oi 
                                   JOIN products p ON oi.product_id = p.id 
                                   WHERE p.vendor_id = :vendor_id";
                    $orders_stmt = $db->prepare($orders_query);
                    $orders_stmt->bindParam(':vendor_id', $vendor['id'], PDO::PARAM_INT);
                    $orders_stmt->execute();
                    $orders_count = $orders_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    ?>
                    <div class="stat-number"><?php echo $orders_count; ?></div>
                    <div class="stat-label">Orders</div>
                </div>
                <div class="stat">
                    <div class="stat-number">4.8</div>
                    <div class="stat-label">Rating</div>
                </div>
            </div>
        </div>

<<<<<<< HEAD
=======
        <div class="banner-upload-section">
            <h3>Store Banner</h3>
            <p>This image will appear at the top of your public store page. Recommended size: 1200x400 pixels.</p>
            <form id="bannerForm" method="POST" enctype="multipart/form-data">
                <div class="banner-preview">
                    <img src="<?php echo BASE_URL; ?>uploads/vendors/<?php echo htmlspecialchars($vendor['banner_image'] ?: 'default-banner.png'); ?>" alt="Store Banner" id="bannerImagePreview">
                    <label for="banner-upload" class="banner-upload-label">
                        <i class="fas fa-camera"></i> Change Banner
                    </label>
                    <input type="file" id="banner-upload" name="banner" accept="image/*" style="display: none;">
                    <input type="hidden" name="update_banner" value="1">
                </div>
            </form>
        </div>

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
        <form method="POST" class="profile-form">
            <div class="form-section">
                <h3 class="section-title">Business Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Business Name *</label>
                        <input type="text" name="business_name" class="form-control" value="<?php echo htmlspecialchars($vendor['business_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Support Email *</label>
                        <input type="email" name="support_email" class="form-control" value="<?php echo htmlspecialchars($vendor['support_email'] ?? $vendor['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Business Phone</label>
                        <input type="tel" name="business_phone" class="form-control" value="<?php echo htmlspecialchars($vendor['business_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Business Address</label>
                        <textarea name="business_address" class="form-control form-textarea"><?php echo htmlspecialchars($vendor['business_address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Business Description</label>
                        <textarea name="business_description" class="form-control form-textarea" rows="4"><?php echo htmlspecialchars($vendor['business_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
<<<<<<< HEAD
=======
                <h3 class="section-title">Security</h3>
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
                </div>
            </div>

            <div class="form-section">
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                <h3 class="section-title">Store Settings</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Store Status</label>
<<<<<<< HEAD
                        <select class="form-control">
                            <option value="open" selected>Open</option>
                            <option value="closed">Closed</option>
                            <option value="vacation">On Vacation</option>
=======
                        <select name="store_status" class="form-control">
                            <option value="open" <?php echo ($vendor['store_status'] ?? 'open') == 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="closed" <?php echo ($vendor['store_status'] ?? '') == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            <option value="vacation" <?php echo ($vendor['store_status'] ?? '') == 'vacation' ? 'selected' : ''; ?>>On Vacation</option>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Order Processing Time</label>
<<<<<<< HEAD
                        <select class="form-control">
                            <option value="1">1 Business Day</option>
                            <option value="2">2 Business Days</option>
                            <option value="3">3 Business Days</option>
                            <option value="5">5 Business Days</option>
=======
                        <select name="processing_time" class="form-control">
                            <option value="1" <?php echo ($vendor['processing_time'] ?? '') == '1' ? 'selected' : ''; ?>>1 Business Day</option>
                            <option value="2" <?php echo ($vendor['processing_time'] ?? '') == '2' ? 'selected' : ''; ?>>2 Business Days</option>
                            <option value="3" <?php echo ($vendor['processing_time'] ?? '') == '3' ? 'selected' : ''; ?>>3 Business Days</option>
                            <option value="5" <?php echo ($vendor['processing_time'] ?? '') == '5' ? 'selected' : ''; ?>>5 Business Days</option>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Shipping Policy</label>
<<<<<<< HEAD
                        <select class="form-control">
                            <option value="free">Free Shipping</option>
                            <option value="flat">Flat Rate</option>
                            <option value="calculated">Calculated</option>
=======
                        <select name="shipping_policy" class="form-control">
                            <option value="free" <?php echo ($vendor['shipping_policy'] ?? '') == 'free' ? 'selected' : ''; ?>>Free Shipping</option>
                            <option value="flat" <?php echo ($vendor['shipping_policy'] ?? '') == 'flat' ? 'selected' : ''; ?>>Flat Rate</option>
                            <option value="calculated" <?php echo ($vendor['shipping_policy'] ?? '') == 'calculated' ? 'selected' : ''; ?>>Calculated</option>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Return Policy</label>
<<<<<<< HEAD
                        <select class="form-control">
                            <option value="30">30 Days Return</option>
                            <option value="14">14 Days Return</option>
                            <option value="7">7 Days Return</option>
                            <option value="none">No Returns</option>
=======
                        <select name="return_policy" class="form-control">
                            <option value="30" <?php echo ($vendor['return_policy'] ?? '') == '30' ? 'selected' : ''; ?>>30 Days Return</option>
                            <option value="14" <?php echo ($vendor['return_policy'] ?? '') == '14' ? 'selected' : ''; ?>>14 Days Return</option>
                            <option value="7" <?php echo ($vendor['return_policy'] ?? '') == '7' ? 'selected' : ''; ?>>7 Days Return</option>
                            <option value="none" <?php echo ($vendor['return_policy'] ?? '') == 'none' ? 'selected' : ''; ?>>No Returns</option>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
<<<<<<< HEAD
                <button type="submit" class="btn btn-primary">Save Changes</button>
=======
                <input type="hidden" name="update_vendor_profile" value="1">
                <button type="submit" class="btn btn-primary">Save All Changes</button>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                <button type="reset" class="btn btn-outline">Reset</button>
            </div>
        </form>
    </div>
</div>

<<<<<<< HEAD
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logo upload functionality
    const logoUpload = document.getElementById('logo-upload');
    const avatarImage = document.querySelector('.avatar-image');
    
    logoUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarImage.src = e.target.result;
                // Here you would typically upload the image to the server
            };
            reader.readAsDataURL(file);
        }
    });
=======
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
// Make modal functions global
function openPasswordModal() {
    const modal = document.getElementById('passwordModal');
    if (modal) modal.classList.add('show');
}
function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    if (modal) modal.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function() {
    // Logo upload functionality
    const logoUpload = document.getElementById('logo-upload');
    
    if (logoUpload) {
        logoUpload.addEventListener('change', function(e) {
            if (!this.files || this.files.length === 0) return;
            
            const avatarContainer = document.querySelector('.profile-avatar');
            const formData = new FormData(document.getElementById('logoForm'));

            avatarContainer.classList.add('uploading');

            fetch(window.location.href, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newLogoUrl = '<?php echo BASE_URL; ?>uploads/vendors/' + data.file_name;
                    const cacheBuster = '?t=' + new Date().getTime();
                    const finalUrl = newLogoUrl + cacheBuster;

                    const preloader = new Image();
                    preloader.onload = () => {
                        const avatarImage = document.querySelector('.avatar-image');
                        if (avatarImage) avatarImage.src = finalUrl;
                        showNotification('Logo updated successfully!', 'success');
                    };
                    preloader.onerror = () => showNotification('Failed to load new logo.', 'error');
                    preloader.src = finalUrl;
                } else {
                    showNotification(data.message || 'Upload failed.', 'error');
                }
            })
            .catch(error => {
                console.error('Upload Error:', error);
                showNotification('A network error occurred.', 'error');
            })
            .finally(() => {
                avatarContainer.classList.remove('uploading');
            });
        });
    }

    // Banner upload functionality
    const bannerUploadInput = document.getElementById('banner-upload');
    if (bannerUploadInput) {
        bannerUploadInput.addEventListener('change', function(e) {
            if (!this.files || this.files.length === 0) return;
            
            const bannerPreview = document.querySelector('.banner-preview');
            const formData = new FormData(document.getElementById('bannerForm'));

            bannerPreview.classList.add('uploading');

            fetch(window.location.href, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newBannerUrl = '<?php echo BASE_URL; ?>uploads/vendors/' + data.file_name;
                    const cacheBuster = '?t=' + new Date().getTime();
                    const finalUrl = newBannerUrl + cacheBuster;

                    const preloader = new Image();
                    preloader.onload = () => {
                        const bannerImage = document.getElementById('bannerImagePreview');
                        if (bannerImage) bannerImage.src = finalUrl;
                        showNotification('Banner updated successfully!', 'success');
                    };
                    preloader.onerror = () => showNotification('Failed to load new banner image.', 'error');
                    preloader.src = finalUrl;
                } else {
                    showNotification(data.message || 'Upload failed.', 'error');
                }
            })
            .catch(error => {
                console.error('Banner Upload Error:', error);
                showNotification('A network error occurred during banner upload.', 'error');
            })
            .finally(() => {
                bannerPreview.classList.remove('uploading');
            });
        });
    }

    // Password Modal
    const passwordModal = document.getElementById('passwordModal');
    if (passwordModal) {
        document.getElementById('passwordModalClose').addEventListener('click', closePasswordModal);
        document.getElementById('passwordModalCancel').addEventListener('click', closePasswordModal);
        passwordModal.addEventListener('click', (e) => {
            if (e.target === passwordModal) closePasswordModal();
        });

        const passwordForm = document.getElementById('passwordChangeForm');
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('change_password_ajax', '1');
            const messageContainer = document.getElementById('password-modal-messages');
            const submitButton = this.querySelector('button[type="submit"]');

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"></span> Updating...';
            messageContainer.innerHTML = '';

            fetch(window.location.href, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                const alertClass = data.success ? 'alert-success' : 'alert-error';
                messageContainer.innerHTML = `<div class="alert ${alertClass}">${data.message}</div>`;
                if (data.success) {
                    this.reset();
                    setTimeout(() => {
                        closePasswordModal();
                        messageContainer.innerHTML = '';
                    }, 2000);
                }
            })
            .catch(error => {
                messageContainer.innerHTML = `<div class="alert alert-error">A network error occurred.</div>`;
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Update Password';
            });
        });
    }
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>