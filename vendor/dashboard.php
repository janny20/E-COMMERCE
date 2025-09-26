<?php
// vendor/dashboard.php
<<<<<<< HEAD
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();

<<<<<<< HEAD
// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get vendor ID
$vendor_id = $_SESSION['vendor_id'] ?? null;
// Check vendor approval status
$vendor_status = null;

if ($vendor_id) {
    // Check both the vendor's status and the user's status
    $query = "SELECT v.status as vendor_status, u.status as user_status 
              FROM vendors v 
              JOIN users u ON v.user_id = u.id 
              WHERE v.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$vendor_id]);
    $statuses = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user is deleted, that status takes precedence.
    $vendor_status = ($statuses && $statuses['user_status'] === 'deleted') ? 'deleted' : ($statuses['vendor_status'] ?? null);
}

// Enhanced status check
if ($vendor_status !== 'approved') {
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="vendor-dashboard container" style="margin-top:40px;text-align:center;padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">';
    
    switch ($vendor_status) {
        case 'suspended':
            echo '<h2 style="color:#c0392b;">Account Suspended</h2>';
            echo '<p>Your vendor account has been suspended. Please contact support for assistance.</p>';
            break;
        case 'deleted':
            echo '<h2 style="color:#c0392b;">Account Deleted</h2>';
            echo '<p>This account has been deleted. Please contact support for more information.</p>';
            break;
        case 'deleted':
            echo '<h2 style="color:#c0392b;">Account Deactivated</h2>';
            echo '<p>This vendor account has been deactivated. Please create a new account or contact support.</p>';
            break;
        case 'rejected':
            echo '<h2 style="color:#c0392b;">Application Rejected</h2>';
            echo '<p>We regret to inform you that your vendor application was not approved at this time.</p>';
            break;
        default: // Covers 'pending' and null status
            echo '<h2 style="color:#d35400;">Account Pending Approval</h2>';
            echo '<p>Your account is currently under review by our admin team. You will be notified once it is approved.</p>';
    }
    
    echo '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

=======
// Get vendor ID

$vendor_id = $_SESSION['vendor_id'] ?? null;
// Check vendor approval status
$vendor_status = null;
if ($vendor_id) {
  $stmt = $db->prepare("SELECT status FROM vendors WHERE id = ?");
  $stmt->execute([$vendor_id]);
  $vendor_status = $stmt->fetchColumn();
}
if ($vendor_status !== 'approved') {
  require_once __DIR__ . '/../includes/header.php';
  echo '<div class="vendor-dashboard container" style="margin-top:40px;text-align:center;">';
  echo '<h2 style="color:#d35400;">Vendor account pending...</h2>';
  echo '<p>Your account must be approved by the admin before you can access the dashboard.</p>';
  echo '</div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
// products count
$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM products WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$products_count = $stmt->fetchColumn();

// order items count
$stmt = $db->prepare("SELECT COUNT(DISTINCT order_id) FROM order_items WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$orders_count = $stmt->fetchColumn();

// pending items
$stmt = $db->prepare("SELECT COUNT(*) FROM order_items oi
                       JOIN orders o ON o.id = oi.order_id
                       WHERE oi.vendor_id = ? AND o.status = 'pending'");
$stmt->execute([$vendor_id]);
$pending_count = $stmt->fetchColumn();

// total earnings (sum of amounts in vendor_earnings)
<<<<<<< HEAD
$total_earnings = 0;
try {
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM vendor_earnings WHERE vendor_id = ?");
    $stmt->execute([$vendor_id]);
    $total_earnings = $stmt->fetchColumn();
} catch (PDOException $e) {
    // If the table doesn't exist, we can just set earnings to 0.
    // This is a temporary fix until the database is updated.
    if ($e->getCode() === '42S02') {
        $total_earnings = 0;
    } else {
        throw $e;
    }
}
=======
$stmt = $db->prepare("SELECT COALESCE(SUM(net_earning), 0) FROM vendor_earnings WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$total_earnings = $stmt->fetchColumn();
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="vendor-dashboard container">
  <div class="dashboard-header">
    <h1>Vendor Dashboard</h1>
    <p>Welcome, <?=htmlspecialchars($_SESSION['username'] ?? 'Vendor')?></p>
  </div>

  <div class="dashboard-stats">
    <div class="stat-card">
      <h3>Total Products</h3>
      <p class="stat-number"><?=htmlspecialchars($products_count)?></p>
    </div>
    <div class="stat-card">
      <h3>Total Orders</h3>
      <p class="stat-number"><?=htmlspecialchars($orders_count)?></p>
    </div>
    <div class="stat-card">
      <h3>Pending Orders</h3>
      <p class="stat-number"><?=htmlspecialchars($pending_count)?></p>
    </div>
    <div class="stat-card">
      <h3>Total Earnings</h3>
      <p class="stat-number">$<?=money($total_earnings)?></p>
    </div>
  </div>

  <nav class="vendor-nav">
    <a href="<?php echo BASE_URL; ?>vendor/products.php">Products</a> |
    <a href="<?php echo BASE_URL; ?>vendor/orders.php">Orders</a> |
    <a href="<?php echo BASE_URL; ?>vendor/earnings.php">Earnings</a> |
    <a href="<?php echo BASE_URL; ?>vendor/profile.php">Profile</a> |
    <a href="<?php echo BASE_URL; ?>pages/logout.php">Logout</a>
  </nav>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
