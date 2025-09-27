0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

if (!$isLoggedIn) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$order_number = $_GET['order_number'] ?? '';

if (empty($order_number)) {
    header('Location: ' . BASE_URL . 'pages/orders.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = null;
$status_history = [];

try {
    // Fetch order details, ensuring it belongs to the current user
    $order_sql = "SELECT * FROM orders WHERE order_number = :order_number AND customer_id = :user_id";
    $order_stmt = $db->prepare($order_sql);
    $order_stmt->execute(['order_number' => $order_number, 'user_id' => $userId]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Fetch status history
        $history_sql = "SELECT * FROM order_status_history WHERE order_id = :order_id ORDER BY created_at ASC";
        $history_stmt = $db->prepare($history_sql);
        $history_stmt->execute(['order_id' => $order['id']]);
        $status_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Track Order Error: " . $e->getMessage());
    // Handle error gracefully
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/track-order.css">

<main class="track-order-page">
    <div class="container">
        <?php if (!$order): ?>
            <div class="alert alert-error">
                <h2>Order Not Found</h2>
                <p>The order number "<?php echo htmlspecialchars($order_number); ?>" could not be found in your account.</p>
                <a href="<?php echo BASE_URL; ?>pages/orders.php" class="btn btn-primary">View My Orders</a>
            </div>
        <?php else: ?>
            <div class="track-header">
                <h1>Track Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                <p>Current Status: <span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
            </div>

            <div class="tracking-timeline">
                <?php if (empty($status_history)): ?>
                    <p>No tracking information available for this order yet.</p>
                <?php else: ?>
                    <?php foreach (array_reverse($status_history) as $index => $history): ?>
                        <div class="timeline-item <?php echo $index === 0 ? 'current' : ''; ?>">
                            <div class="timeline-icon">
                                <i class="fas <?php 
                                    switch ($history['status']) {
                                        case 'shipped': echo 'fa-truck'; break;
                                        case 'delivered': echo 'fa-check-circle'; break;
                                        case 'processing': echo 'fa-cogs'; break;
                                        case 'confirmed': echo 'fa-clipboard-check'; break;
                                        case 'cancelled': echo 'fa-times-circle'; break;
                                        default: echo 'fa-receipt'; break;
                                    }
                                ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h3><?php echo ucfirst($history['status']); ?></h3>
                                <p><?php echo htmlspecialchars($history['notes']); ?></p>
                                <span class="timeline-date"><?php echo date('F j, Y - g:ia', strtotime($history['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="track-footer">
                <a href="<?php echo BASE_URL; ?>pages/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">View Full Order Details</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>