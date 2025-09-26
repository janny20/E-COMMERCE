<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$order = null;

if ($order_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM orders WHERE id = :order_id AND customer_id = :customer_id";
    $stmt = $db->prepare($sql);
    $stmt->execute(['order_id' => $order_id, 'customer_id' => $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/checkout.css">

<main class="checkout-page">
    <div class="container">
        <div class="checkout-header">
            <h1>Order Complete</h1>
            <div class="checkout-steps">
                <div class="checkout-step completed"><span class="step-number">1</span><span class="step-text">Shopping Cart</span></div>
                <div class="checkout-step completed"><span class="step-number">2</span><span class="step-text">Checkout</span></div>
                <div class="checkout-step active"><span class="step-number">3</span><span class="step-text">Order Complete</span></div>
            </div>
        </div>

        <div class="order-success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Thank You For Your Order!</h2>
            
            <?php if ($order): ?>
                <p>Your order <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong> has been placed successfully.</p>
                <p>A confirmation email has been sent to your registered email address. You can view your order details in your account.</p>
            <?php else: ?>
                <p>Your order has been placed successfully. You can view your order details in your account.</p>
            <?php endif; ?>

            <div class="success-actions">
                <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary">Continue Shopping</a>
                <a href="<?php echo BASE_URL; ?>pages/orders.php" class="btn btn-outline">View My Orders</a>
            </div>
        </div>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>