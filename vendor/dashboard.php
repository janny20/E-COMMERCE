<?php
// vendor/dashboard.php
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
$stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM vendor_earnings WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$total_earnings = $stmt->fetchColumn();
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css?v=2">

<div class="vendor-dashboard container">
  <div class="dashboard-header">
    <h1 class="dashboard-title">Vendor Dashboard</h1>
    <p class="dashboard-subtitle">Welcome back, <?=htmlspecialchars($_SESSION['username'] ?? 'Vendor')?>!</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-box-open"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?=htmlspecialchars($products_count)?></span>
            <span class="stat-label">Total Products</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?=htmlspecialchars($orders_count)?></span>
            <span class="stat-label">Total Orders</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?=htmlspecialchars($pending_count)?></span>
            <span class="stat-label">Pending Orders</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <span class="stat-number">$<?=money($total_earnings)?></span>
            <span class="stat-label">Total Earnings</span>
        </div>
    </div>
  </div>

  <div class="quick-actions">
      <a href="<?php echo BASE_URL; ?>vendor/products.php" class="action-btn"><i class="action-icon fas fa-boxes"></i><span class="action-label">Manage Products</span></a>
      <a href="<?php echo BASE_URL; ?>vendor/orders.php" class="action-btn"><i class="action-icon fas fa-file-invoice-dollar"></i><span class="action-label">View Orders</span></a>
      <a href="<?php echo BASE_URL; ?>vendor/earnings.php" class="action-btn"><i class="action-icon fas fa-chart-line"></i><span class="action-label">Earnings Report</span></a>
      <a href="<?php echo BASE_URL; ?>vendor/profile.php" class="action-btn"><i class="action-icon fas fa-user-cog"></i><span class="action-label">Edit Profile</span></a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
