<?php
// vendor/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();

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
$stmt = $db->prepare("SELECT COALESCE(SUM(net_earning), 0) FROM vendor_earnings WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$total_earnings = $stmt->fetchColumn();
?>
<?php 
$page_title = "Dashboard";
require_once __DIR__ . '/../includes/header.php'; 
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">

<div class="vendor-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Vendor Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Vendor'); ?>!</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box-open"></i></div>
                <div class="stat-info">
                    <p class="stat-number"><?php echo htmlspecialchars($products_count); ?></p>
                    <h3>Total Products</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-info">
                    <p class="stat-number"><?php echo htmlspecialchars($orders_count); ?></p>
                    <h3>Total Orders</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <p class="stat-number"><?php echo htmlspecialchars($pending_count); ?></p>
                    <h3>Pending Items</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <p class="stat-number">$<?php echo money($total_earnings); ?></p>
                    <h3>Total Earnings</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
