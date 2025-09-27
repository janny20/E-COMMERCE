<?php
// pages/profile.php - Customer Profile Page

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/functions.php'; // Include functions for file upload

// --- AJAX HANDLERS ---
// These are placed at the top to handle POST requests before any HTML is output.

// Handle AJAX avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];
    
    // Check for login status within the AJAX handler
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Authentication error. Please log in again.';
        echo json_encode($response);
        exit();
    }
    $ajaxUserId = $_SESSION['user_id'];

    // Establish a dedicated DB connection for this action
    $database = new Database();
    $db = $database->getConnection();

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['avatar'], USER_UPLOAD_PATH);
        
        if ($upload_result['success']) {
            $avatar_filename = $upload_result['file_name'];
            
            // Check if a profile record exists to decide between INSERT and UPDATE
            $check_profile_query = "SELECT id, avatar FROM user_profiles WHERE user_id = :user_id";
            $check_stmt = $db->prepare($check_profile_query);
            $check_stmt->bindParam(':user_id', $ajaxUserId, PDO::PARAM_INT);
            $check_stmt->execute();
            $existing_profile = $check_stmt->fetch(PDO::FETCH_ASSOC);

            $db->beginTransaction();

            try {
                // Delete old avatar if it exists
                if ($existing_profile && !empty($existing_profile['avatar'])) {
                    $old_avatar_path = USER_UPLOAD_PATH . $existing_profile['avatar'];
                    if (file_exists($old_avatar_path)) {
                        unlink($old_avatar_path);
                    }
                }

                if ($existing_profile) {
                    // Profile exists, so UPDATE
                    $query = "UPDATE user_profiles SET avatar = :avatar WHERE user_id = :user_id";
                } else {
                    // Profile does not exist, so INSERT
                    $query = "INSERT INTO user_profiles (user_id, avatar) VALUES (:user_id, :avatar)";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':avatar', $avatar_filename);
                $stmt->bindParam(':user_id', $ajaxUserId, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $db->commit();
                    $_SESSION['avatar'] = $avatar_filename;
                    $response['success'] = true;
                    $response['message'] = 'Avatar updated successfully!';
                    $response['file_name'] = $avatar_filename;
                } else {
                    throw new Exception('Failed to update avatar in the database.');
                }
            } catch (Exception $e) {
                $db->rollBack();
                // Delete the newly uploaded file if the DB operation failed
                if (file_exists(USER_UPLOAD_PATH . $avatar_filename)) {
                    unlink(USER_UPLOAD_PATH . $avatar_filename);
                }
                $response['message'] = $e->getMessage();
                error_log('Avatar Upload Error: ' . $e->getMessage());
            }
        } else {
            $response['message'] = $upload_result['message'];
        }
    } else {
        $error_code = $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE;
        $message = 'An unknown upload error occurred.';
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'File is too large. Please upload a smaller image.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'The file was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was selected for upload.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Server configuration error: Missing a temporary folder.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Server error: Failed to write file to disk.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = 'A server extension stopped the file upload.';
                break;
        }
        $response['message'] = $message;
    }
    
    echo json_encode($response);
    exit();
}

// --- END AJAX HANDLERS ---

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

// Process profile update
$success_message = '';
$error_message = '';

// Handle standard form submission for profile info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');

    try {
        // Check if a profile record exists to decide between INSERT and UPDATE
        $check_profile_query = "SELECT id FROM user_profiles WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_profile_query);
        $check_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $check_stmt->execute();
        $profile_exists = $check_stmt->fetch();

        if ($profile_exists) {
            $query = "UPDATE user_profiles SET first_name = :first_name, last_name = :last_name, phone = :phone, address = :address, city = :city, state = :state, country = :country, zip_code = :zip_code WHERE user_id = :user_id";
        } else {
            $query = "INSERT INTO user_profiles (user_id, first_name, last_name, phone, address, city, state, country, zip_code) VALUES (:user_id, :first_name, :last_name, :phone, :address, :city, :state, :country, :zip_code)";
        }
        
        $update_stmt = $db->prepare($query);
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
            // Refresh data
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            // Update session variable to reflect the name change in the header
            $_SESSION['username'] = !empty($user_data['first_name']) ? $user_data['first_name'] : $user_data['username'];
        } else {
            $error_message = 'Failed to update profile. Please try again.';
        }
    } catch (Exception $e) {
        $error_message = 'Error updating profile: ' . $e->getMessage();
    }
}

// --- PAGE LOGIC ---

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// If the logged-in user is a vendor, redirect to vendor profile
if (isset($_SESSION['user_type']) && strtolower($_SESSION['user_type']) === 'vendor') {
    header('Location: ' . BASE_URL . 'vendor/profile.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user data for display
$stmt->execute(); // Re-execute to get fresh data after potential update
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
                                        <?php echo strtoupper(substr($user_data['username'] ?? 'U', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="avatar-upload-btn"><i class="fas fa-camera"></i></div>
                            </label>
                            <input type="file" id="avatar-upload-input" name="avatar" accept="image/*" style="display: none;">
                            <input type="hidden" name="update_avatar" value="1">
                        </div>
                    </form>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars(trim($user_data['first_name'] . ' ' . $user_data['last_name'])) ?: htmlspecialchars($user_data['username']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p class="profile-member">Member since <?php echo date('M Y', strtotime($user_data['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo $orders_count; ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $reviews_count; ?></div>
                        <div class="stat-label">Reviews</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $wishlist_count; ?></div>
                        <div class="stat-label">Wishlist</div>
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
                    <a href="<?php echo BASE_URL; ?>pages/my-reviews.php" class="nav-item">
                        <i class="fas fa-star"></i>
                        <span>My Reviews (<?php echo $reviews_count; ?>)</span>
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
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    /**
     * Initializes all interactive components on the profile page.
     */
    function initProfilePage() {

        // --- Avatar Auto-Upload ---
        function initAvatarUpload() {
            const avatarInput = document.getElementById('avatar-upload-input');
            if (!avatarInput) return;

            avatarInput.addEventListener('change', function() {
                if (!this.files || this.files.length === 0) return;

                const avatarContainer = document.querySelector('.profile-avatar');
                const formData = new FormData(document.getElementById('avatarForm'));
                
                avatarContainer.classList.add('uploading');

                fetch(window.location.href, { method: 'POST', body: formData })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json();
                        } else {
                            return response.text().then(text => { 
                                throw new Error("Server did not return JSON. Response: " + text);
                            });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            const newAvatarUrl = '<?php echo BASE_URL; ?>uploads/users/' + data.file_name;
                            const cacheBuster = '?t=' + new Date().getTime();
                            const finalUrl = newAvatarUrl + cacheBuster;

                            const preloader = new Image();
                            preloader.onload = () => {
                                const headerAvatar = document.querySelector('.header-avatar, .header-avatar-placeholder');
                                if (headerAvatar) {
                                    const newHeader = preloader.cloneNode();
                                    newHeader.className = headerAvatar.className.replace('-placeholder', '');
                                    headerAvatar.replaceWith(newHeader);
                                }

                                const profileAvatar = document.querySelector('.avatar-image, .avatar-placeholder');
                                if (profileAvatar) {
                                    const newProfile = preloader.cloneNode();
                                    newProfile.className = profileAvatar.className.replace('-placeholder', '');
                                    profileAvatar.replaceWith(newProfile);
                                }
                                showNotification('Avatar updated successfully!', 'success');
                            };
                            preloader.onerror = () => showNotification('Failed to load the new avatar image.', 'error');
                            preloader.src = finalUrl;
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
            });
        }

        // --- Initialize all components ---
        initAvatarUpload();
    }

    // Run the initializer
    initProfilePage();
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