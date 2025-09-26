<?php
// vendor/orders.php
<<<<<<< HEAD
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
// Removed unknown function requireVendor();
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b


$vendor_id = $_SESSION['vendor_id'] ?? null;
if (!$vendor_id) {
  die('Vendor not logged in.');
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

<<<<<<< HEAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id']) && !empty($_POST['status'])) {
    // Update order item status via parent order (depending on app design)
    $item_id = (int)$_POST['item_id'];
    $new_status = $_POST['status'];
    // Simplified: update orders table status for that order_id
  $stmt = $db->prepare("SELECT order_id FROM order_items WHERE id = ? AND vendor_id = ?");
  $stmt->execute([$item_id, $vendor_id]);
  $row = $stmt->fetch();
  if ($row) {
    $order_id = $row['order_id'];
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
  }
    header('Location: orders.php');
    exit;
}

// Fetch order items for this vendor grouped by order
$stmt = $db->prepare("SELECT o.id as order_id, o.customer_id, o.total_amount, o.status, o.created_at,
          oi.id as item_id, oi.product_id, oi.qty, oi.price, p.name
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE oi.vendor_id = ?
        ORDER BY o.created_at DESC");
$stmt->execute([$vendor_id]);
$rows = $stmt->fetchAll();

$orders = [];
foreach ($rows as $r) {
    $oid = $r['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'order' => [
                'id' => $oid,
                'user_id' => $r['user_id'],
                'status' => $r['status'],
                'total_amount' => $r['total_amount'],
                'created_at' => $r['created_at']
            ],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = [
        'item_id' => $r['item_id'],
        'product_id' => $r['product_id'],
        'title' => $r['title'],
        'qty' => $r['qty'],
        'price' => $r['price'],
    ];
}
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container vendor-orders">
  <nav><a href="<?php echo BASE_URL; ?>vendor/dashboard.php">Dashboard</a> | <a href="<?php echo BASE_URL; ?>vendor/products.php">Products</a></nav>
    <h2>Orders containing your items</h2>
    <?php if (empty($orders)): ?>
      <p>No orders yet.</p>
    <?php else: ?>
      <?php foreach($orders as $ord): ?>
        <div class="order-card">
          <strong>Order #<?=htmlspecialchars($ord['order']['id'])?></strong>
          <div>Status: <?=htmlspecialchars($ord['order']['status'])?></div>
          <div>Placed: <?=htmlspecialchars($ord['order']['created_at'])?></div>
          <div>Total (order): $<?=number_format($ord['order']['total_amount'], 2)?></div>
          <h4>Your items</h4>
          <ul>
            <?php foreach($ord['items'] as $it): ?>
              <li>
                <?=htmlspecialchars($it['title'])?> — <?=htmlspecialchars($it['qty'])?> × $<?=number_format($it['price'], 2)?></div>
                <form method="post" style="display:inline">
                  <input type="hidden" name="item_id" value="<?=htmlspecialchars($it['item_id'])?>">
                  <select name="status">
                    <option value="pending">Pending</option>
                    <option value="shipped">Shipped</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  <button class="btn btn-sm">Update</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
=======
// Fetch order items for this vendor grouped by order
$sql = "SELECT 
            o.id as order_id, 
            o.order_number,
            o.status as order_status, 
            o.created_at,
            SUM(oi.total) as vendor_total,
            COUNT(oi.id) as vendor_item_count,
            up.first_name,
            up.last_name
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        LEFT JOIN user_profiles up ON o.customer_id = up.user_id
        WHERE oi.vendor_id = :vendor_id
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute(['vendor_id' => $vendor_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">

<div class="vendor-dashboard container">
    <div class="dashboard-header">
        <h1>My Orders</h1>
        <p>Manage and fulfill your customer orders.</p>
    </div>

    <?php if (empty($orders)): ?>
      <div class="content-card" style="text-align:center; padding: 2rem;">
        <p>You have no orders yet.</p>
      </div>
    <?php else: ?>
        <div class="content-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Your Items</th>
                            <th>Your Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="order-detail.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="order-id-link">
                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </a>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])); ?></td>
                                <td><?php echo htmlspecialchars($order['vendor_item_count']); ?></td>
                                <td>$<?php echo money($order['vendor_total']); ?></td>
                                <td><span class="status-badge status-<?php echo str_replace('_', '-', $order['order_status']); ?>"><?php echo ucwords(str_replace('_', ' ', $order['order_status'])); ?></span></td>
                                <td><a href="order-detail.php?id=<?php echo htmlspecialchars($order['order_id']); ?>" class="btn btn-sm btn-outline">View Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
