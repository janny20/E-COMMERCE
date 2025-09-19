<?php
// vendor/orders.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();


$vendor_id = $_SESSION['vendor_id'] ?? null;
if (!$vendor_id) {
  die('Vendor not logged in.');
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

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
          <div>Total (order): $<?=money($ord['order']['total_amount'])?></div>
          <h4>Your items</h4>
          <ul>
            <?php foreach($ord['items'] as $it): ?>
              <li>
                <?=htmlspecialchars($it['title'])?> — <?=htmlspecialchars($it['qty'])?> × $<?=money($it['price'])?>
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
