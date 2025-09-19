<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Set page title
$page_title = "Orders Management";

// Handle order status update
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['status'])) {
    $order_id = $_GET['id'];
    $status = $_GET['status'];
    
    $valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($status, $valid_statuses)) {
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id);
        
        if ($stmt->execute()) {
            $success = "Order status updated successfully.";
        } else {
            $error = "Error updating order status.";
        }
    } else {
        $error = "Invalid order status.";
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query with filters
$query = "SELECT o.*, u.username, u.email 
          FROM orders o 
          JOIN users u ON o.customer_id = u.id 
          WHERE 1=1";
$params = [];

if (!empty($status_filter) && $status_filter != 'all') {
    $query .= " AND o.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(o.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(o.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header
include_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Orders Management</h1>
        <p>View and manage customer orders</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Order Filters</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="admin-orders.php" class="btn btn-outline">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>All Orders</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($orders)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_number']; ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($order['username']); ?></strong>
                                        <div class="text-muted"><?php echo htmlspecialchars($order['email']); ?></div>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View Details</a>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle">Update Status</button>
                                            <div class="dropdown-content">
                                                <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=pending">Pending</a>
                                                <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=confirmed">Confirmed</a>
                                                <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=processing">Processing</a>
                                                <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=shipped">Shipped</a>
                                                <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=delivered">Delivered</a>
                                                <a href="orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=cancelled">Cancelled</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once 'includes/admin-footer.php';
?>