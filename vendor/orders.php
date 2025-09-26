<?php
// vendor/orders.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
