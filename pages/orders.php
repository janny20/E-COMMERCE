<?php
// Include config
require_once '../includes/config.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

// Get orders
$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build query
$query = "SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.total) as items_total 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.customer_id = :user_id";

$params = [':user_id' => $userId];

if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND o.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($date_filter)) {
    if ($date_filter === 'last30') {
        $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    } elseif ($date_filter === 'last90') {
        $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
    } elseif ($date_filter === 'last365') {
        $query .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY)";
    }
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';

// Add orders-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/orders.css">';
?>

<div class="orders-page">
    <div class="container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p>Track and manage your orders</p>
        </div>

        <div class="orders-filters">
            <div class="filter-group">
                <label for="status-filter">Filter by Status:</label>
                <select id="status-filter" class="filter-select">
                    <option value="all" <?php echo $status_filter === '' ? 'selected' : ''; ?>>All Orders</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="date-filter">Filter by Date:</label>
                <select id="date-filter" class="filter-select">
                    <option value="">All Time</option>
                    <option value="last30" <?php echo $date_filter === 'last30' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="last90" <?php echo $date_filter === 'last90' ? 'selected' : ''; ?>>Last 90 Days</option>
                    <option value="last365" <?php echo $date_filter === 'last365' ? 'selected' : ''; ?>>Last 365 Days</option>
                </select>
            </div>

            <div class="filter-group">
                <button class="btn btn-outline" onclick="resetFilters()">Reset Filters</button>
            </div>
        </div>

        <div class="orders-content">
            <?php if (!empty($orders)): ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3 class="order-number">Order #<?php echo $order['order_number']; ?></h3>
                                    <p class="order-date">Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-details">
                                <div class="order-items-preview">
                                    <?php
                                    // Get order items
                                    $items_query = "SELECT oi.*, p.name, p.images, v.business_name 
                                                  FROM order_items oi 
                                                  JOIN products p ON oi.product_id = p.id 
                                                  JOIN vendors v ON p.vendor_id = v.id 
                                                  WHERE oi.order_id = :order_id 
                                                  LIMIT 3";
                                    $items_stmt = $db->prepare($items_query);
                                    $items_stmt->bindParam(':order_id', $order['id'], PDO::PARAM_INT);
                                    $items_stmt->execute();
                                    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>

                                    <div class="items-images">
                                        <?php foreach ($order_items as $item): ?>
                                            <div class="item-image">
                                                <img src="../assets/images/products/<?php echo !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if ($order['item_count'] > 3): ?>
                                            <div class="item-more">+<?php echo $order['item_count'] - 3; ?> more</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="items-info">
                                        <p><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?> from <?php echo $order['item_count'] > 1 ? 'multiple vendors' : htmlspecialchars($order_items[0]['business_name']); ?></p>
                                    </div>
                                </div>

                                <div class="order-totals">
                                    <div class="total-amount">$<?php echo number_format($order['total_amount'], 2); ?></div>
                                    <p class="total-items">Total for <?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?></p>
                                </div>
                            </div>

                            <div class="order-actions">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">View Details</a>
                                
                                <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                                    <button class="btn btn-outline btn-cancel" data-order-id="<?php echo $order['id']; ?>">Cancel Order</button>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button class="btn btn-primary btn-reorder" data-order-id="<?php echo $order['id']; ?>">Reorder</button>
                                    <a href="#" class="btn btn-outline">Return Items</a>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'shipped'): ?>
                                    <button class="btn btn-primary btn-track" data-order-id="<?php echo $order['id']; ?>">Track Package</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pagination">
                    <a href="#" class="pagination-item disabled">&laquo; Previous</a>
                    <a href="#" class="pagination-item active">1</a>
                    <a href="#" class="pagination-item">2</a>
                    <a href="#" class="pagination-item">3</a>
                    <a href="#" class="pagination-item">Next &raquo;</a>
                </div>
            <?php else: ?>
                <div class="empty-orders">
                    <div class="empty-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h2>No orders found</h2>
                    <p><?php echo !empty($status_filter) || !empty($date_filter) ? 'Try adjusting your filters.' : 'You haven\'t placed any orders yet.'; ?></p>
                    <?php if (!empty($status_filter) || !empty($date_filter)): ?>
                        <button class="btn btn-outline" onclick="resetFilters()">Clear Filters</button>
                    <?php else: ?>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    
    function applyFilters() {
        const status = statusFilter.value;
        const date = dateFilter.value;
        
        let url = 'orders.php?';
        if (status !== 'all') url += `status=${status}&`;
        if (date) url += `date=${date}`;
        
        window.location.href = url;
    }
    
    statusFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);
    
    // Cancel order
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Are you sure you want to cancel this order?')) {
                // Simulate cancel order
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
                this.disabled = true;
                
                setTimeout(() => {
                    alert('Order cancellation request submitted.');
                    this.innerHTML = 'Cancel Order';
                    this.disabled = false;
                }, 1500);
            }
        });
    });
    
    // Reorder
    document.querySelectorAll('.btn-reorder').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            this.disabled = true;
            
            setTimeout(() => {
                alert('Items added to cart successfully!');
                window.location.href = 'cart.php';
            }, 1500);
        });
    });
    
    // Track package
    document.querySelectorAll('.btn-track').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            alert('Tracking information would open here for order #' + orderId);
        });
    });
});

function resetFilters() {
    window.location.href = 'orders.php';
}
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>