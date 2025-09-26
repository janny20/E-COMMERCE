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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $business_name = trim($_POST['business_name']);
    $business_description = trim($_POST['business_description']);
    $business_address = trim($_POST['business_address']);
    $business_phone = trim($_POST['business_phone']);
    $support_email = trim($_POST['support_email']);

    $update_query = "UPDATE vendors SET 
                    business_name = :business_name,
                    business_description = :business_description,
                    business_address = :business_address,
                    business_phone = :business_phone,
                    support_email = :support_email,
                    updated_at = NOW()
                    WHERE user_id = :user_id";

    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':business_name', $business_name);
    $stmt->bindParam(':business_description', $business_description);
    $stmt->bindParam(':business_address', $business_address);
    $stmt->bindParam(':business_phone', $business_phone);
    $stmt->bindParam(':support_email', $support_email);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh vendor data
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}

// Include header
require_once '../includes/header.php';
?>

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
            <div class="profile-avatar">
                <img src="<?php echo BASE_URL; ?>assets/images/vendors/<?php echo $vendor['business_logo'] ?: 'default-logo.png'; ?>" alt="<?php echo htmlspecialchars($vendor['business_name']); ?>" class="avatar-image">
                <label for="logo-upload" class="avatar-upload">
                    <i class="fas fa-camera"></i>
                    <input type="file" id="logo-upload" accept="image/*" style="display: none;">
                </label>
            </div>
            
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
                <h3 class="section-title">Store Settings</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Store Status</label>
                        <select class="form-control">
                            <option value="open" selected>Open</option>
                            <option value="closed">Closed</option>
                            <option value="vacation">On Vacation</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Order Processing Time</label>
                        <select class="form-control">
                            <option value="1">1 Business Day</option>
                            <option value="2">2 Business Days</option>
                            <option value="3">3 Business Days</option>
                            <option value="5">5 Business Days</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Shipping Policy</label>
                        <select class="form-control">
                            <option value="free">Free Shipping</option>
                            <option value="flat">Flat Rate</option>
                            <option value="calculated">Calculated</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Return Policy</label>
                        <select class="form-control">
                            <option value="30">30 Days Return</option>
                            <option value="14">14 Days Return</option>
                            <option value="7">7 Days Return</option>
                            <option value="none">No Returns</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="reset" class="btn btn-outline">Reset</button>
            </div>
        </form>
    </div>
</div>

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
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>