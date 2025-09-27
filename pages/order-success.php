0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if user is logged in and an order ID is provided
if (!$isLoggedIn || !isset($_GET['order_id'])) {
    header('Location: ' . BASE_URL . 'pages/orders.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();

// Fetch the order details, ensuring it belongs to the current user
$order_query = "SELECT * FROM orders WHERE id = :order_id AND customer_id = :user_id";
$order_stmt = $db->prepare($order_query);
$order_stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

// If order not found, redirect
if (!$order) {
    header('Location: ' . BASE_URL . 'pages/orders.php');
    exit();
}

// Fetch order items
$items_query = "SELECT oi.*, p.name as product_name, p.images 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->execute(['order_id' => $order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/order-success.css">

<div class="order-success-page">
    <div class="container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Thank You For Your Order!</h1>
            <p>Your order has been placed successfully. A confirmation email has been sent to you.</p>
            <p class="order-number">Order Number: <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></p>
        </div>

        <div class="order-summary-container">
            <div class="order-details-main">
                <div class="order-items-section">
                    <h2>Items Ordered</h2>
                    <div class="order-items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <?php $img = !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>
                                    <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h4 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <p class="item-qty">Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="order-details-sidebar">
                <div class="detail-card">
                    <h3>Shipping Information</h3>
                    <p>
                        <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong><br>
                        <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                        <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> <?php echo htmlspecialchars($order['shipping_zip']); ?><br>
                        <?php echo htmlspecialchars($order['shipping_country']); ?>
                    </p>
                </div>
                <div class="detail-card">
                    <h3>Payment Method</h3>
                    <p><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($order['payment_method']))); ?></p>
                </div>
                <div class="detail-card totals-card">
                    <h3>Order Summary</h3>
                    <div class="totals-grid">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                        <span>Shipping</span>
                        <span>$<?php echo number_format($order['shipping_cost'], 2); ?></span>
                        <span>Tax</span>
                        <span>$<?php echo number_format($order['tax_amount'], 2); ?></span>
                        <strong class="grand-total">Total</strong>
                        <strong class="grand-total">$<?php echo number_format($order['total_amount'], 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="success-actions">
            <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-outline">Continue Shopping</a>
            <a href="<?php echo BASE_URL; ?>pages/orders.php" class="btn btn-primary">View My Orders</a>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>