<?php
// vendor/dashboard.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();

// Get vendor ID
$vendor_id = $_SESSION['vendor_id'] ?? null;

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
