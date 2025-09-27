0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

if (!$isLoggedIn || $userType !== 'customer') {
    header('Location: ' . BASE_URL . 'pages/login.php?redirect=shipping-address.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$addresses_query = "SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC";
$stmt = $db->prepare($addresses_query);
$stmt->execute(['user_id' => $userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/profile.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/shipping-address.css">

<div class="profile-page">
    <div class="container">
        <div class="profile-header">
            <h1>Shipping Addresses</h1>
            <p>Manage your saved shipping addresses for faster checkout.</p>
        </div>

        <div class="profile-content">
            <aside class="profile-sidebar">
                <!-- Sidebar content can be loaded via an include for consistency -->
                <?php include '../includes/profile_sidebar.php'; ?>
            </aside>

            <div class="profile-main">
                <div class="address-header">
                    <h2 class="section-title">My Addresses</h2>
                    <button class="btn btn-primary" id="addNewAddressBtn"><i class="fas fa-plus"></i> Add New Address</button>
                </div>

                <div class="address-list" id="addressList">
                    <?php if (empty($addresses)): ?>
                        <div class="empty-state">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>No addresses saved</h3>
                            <p>Add a shipping address to get started.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>" data-address-id="<?php echo $address['id']; ?>">
                                <?php if ($address['is_default']): ?>
                                    <div class="default-badge">Default</div>
                                <?php endif; ?>
                                <div class="address-details">
                                    <p class="address-label"><?php echo htmlspecialchars($address['label'] ?: 'Address'); ?></p>
                                    <p><strong><?php echo htmlspecialchars($address['full_name']); ?></strong></p>
                                    <p><?php echo htmlspecialchars($address['address_line_1']); ?></p>
                                    <?php if (!empty($address['address_line_2'])): ?>
                                        <p><?php echo htmlspecialchars($address['address_line_2']); ?></p>
                                    <?php endif; ?>
                                    <p><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['zip_code']); ?></p>
                                    <p><?php echo htmlspecialchars($address['country']); ?></p>
                                    <?php if (!empty($address['phone'])): ?>
                                        <p>Phone: <?php echo htmlspecialchars($address['phone']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="address-actions">
                                    <button class="btn-link btn-edit-address">Edit</button>
                                    <button class="btn-link btn-delete-address">Delete</button>
                                    <?php if (!$address['is_default']): ?>
                                        <button class="btn-link btn-set-default">Set as Default</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Address Modal -->
<div class="modal" id="addressModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="addressModalTitle">Add New Address</h3>
            <button class="modal-close" id="addressModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addressForm">
                <input type="hidden" name="action" id="addressAction">
                <input type="hidden" name="address_id" id="addressId">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="label">Label (e.g., Home, Work)</label>
                        <input type="text" id="label" name="label" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="address_line_1">Address Line 1 *</label>
                        <input type="text" id="address_line_1" name="address_line_1" class="form-control" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="address_line_2">Address Line 2 (Optional)</label>
                        <input type="text" id="address_line_2" name="address_line_2" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State / Province *</label>
                        <input type="text" id="state" name="state" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="zip_code">ZIP / Postal Code *</label>
                        <input type="text" id="zip_code" name="zip_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country *</label>
                        <select id="country" name="country" class="form-control" required>
                            <option value="">Select Country</option>
                            <option value="US">United States</option>
                            <option value="UK">United Kingdom</option>
                            <option value="CA">Canada</option>
                            <option value="AU">Australia</option>
                            <option value="NG">Nigeria</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="phone">Phone Number (Optional)</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_default" id="is_default">
                            Set as default shipping address
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Ensure the JS file exists before including
$jsPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/js/pages/shipping-address.js';
if (file_exists($jsPath)) {
    echo '<script src="' . BASE_URL . 'assets/js/pages/shipping-address.js"></script>';
}

// Safely include the footer if it exists
$footerPath = $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
if (file_exists($footerPath)) {
    require_once '../includes/footer.php';
}
?>