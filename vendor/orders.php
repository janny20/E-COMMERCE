<?php
// vendor/orders.php
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

// Get vendor ID
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id FROM vendors WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);
$vendor_id = $vendor['id'];

// Get orders
$query = "SELECT o.*, u.username, u.email 
          FROM orders o 
          JOIN order_items oi ON o.id = oi.order_id 
          JOIN products p ON oi.product_id = p.id 
          JOIN users u ON o.customer_id = u.id 
          WHERE p.vendor_id = :vendor_id 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';
?>

<div class="vendor-orders">
    <div class="container">
        <div class="orders-header">
            <h1>Order Management</h1>
            <p>Manage and track your customer orders</p>
        </div>

        <div class="orders-filters">
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select class="filter-select">
                    <option value="all">All Orders</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Date Range</label>
                <select class="filter-select">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="all">All Time</option>
                </select>
            </div>
        </div>

        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number">Order #<?php echo $order['order_number']; ?></div>
                        <div class="order-date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>

                <div class="order-customer">
                    <div class="customer-name"><?php echo htmlspecialchars($order['username']); ?></div>
                    <div class="customer-email"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>

                <div class="order-total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>

                <div class="order-actions">
                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                    <select class="status-select">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    </select>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>