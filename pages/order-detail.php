<?php
// pages/order-detail.php - Customer-facing order detail page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    header('Location: ' . BASE_URL . 'pages/orders.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = null;
$order_items = [];

try {
    // Fetch main order details, ensuring it belongs to the logged-in customer
    $order_sql = "SELECT * FROM orders WHERE id = :order_id AND customer_id = :customer_id";
    $order_stmt = $db->prepare($order_sql);
    $order_stmt->execute(['order_id' => $order_id, 'customer_id' => $customer_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Fetch all items for this order
        $items_sql = "SELECT oi.*, p.name as product_name, p.images, v.business_name
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      JOIN vendors v ON oi.vendor_id = v.id
                      WHERE oi.order_id = :order_id";
        $items_stmt = $db->prepare($items_sql);
        $items_stmt->execute(['order_id' => $order_id]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Customer Order Detail Error: " . $e->getMessage());
    die("An error occurred while fetching your order details.");
}

if (!$order) {
    header('Location: ' . BASE_URL . 'pages/orders.php?error=not_found');
    exit();
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/orders.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/customer-order-detail.css">

<main class="orders-page">
    <div class="container">
        <div class="orders-header">
            <h1>Order Details</h1>
            <p><a href="<?php echo BASE_URL; ?>pages/orders.php">&larr; Back to My Orders</a></p>
        </div>

        <div class="order-detail-card">
            <div class="order-detail-header">
                <div class="detail-group">
                    <span>ORDER NUMBER</span>
                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                </div>
                <div class="detail-group">
                    <span>DATE PLACED</span>
                    <strong><?php echo date('M d, Y', strtotime($order['created_at'])); ?></strong>
                </div>
                <div class="detail-group">
                    <span>TOTAL AMOUNT</span>
                    <strong>$<?php echo money($order['total_amount']); ?></strong>
                </div>
                <div class="detail-group">
                    <span>OVERALL STATUS</span>
                    <strong class="status-badge status-<?php echo str_replace('_', '-', $order['status']); ?>"><?php echo ucwords(str_replace('_', ' ', $order['status'])); ?></strong>
                </div>
            </div>

            <div class="order-detail-body">
                <div class="order-items-list">
                    <h3>Items in this Order</h3>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item-row">
                            <div class="item-product-info">
                                <?php $img = !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>
                                <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <div>
                                    <a href="<?php echo BASE_URL; ?>pages/product-detail.php?id=<?php echo $item['product_id']; ?>" class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                    <p class="item-vendor">Sold by: <a href="<?php echo BASE_URL; ?>pages/vendor.php?id=<?php echo $item['vendor_id']; ?>"><?php echo htmlspecialchars($item['business_name']); ?></a></p>
                                    <p class="item-price-qty">$<?php echo money($item['price']); ?> &times; <?php echo $item['quantity']; ?></p>
                                </div>
                            </div>
                            <div class="item-status-info">
                                <p class="item-status status-<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></p>
                                <?php if ($item['status'] === 'shipped' && !empty($item['tracking_number'])): ?>
                                    <a href="<?php echo getTrackingUrl($item['shipping_carrier'], $item['tracking_number']); ?>" target="_blank" class="btn btn-sm btn-outline btn-track">
                                        <i class="fas fa-truck"></i> Track Package
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary-panels">
                    <div class="summary-panel">
                        <h4>Shipping Address</h4>
                        <address><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></address>
                    </div>
                    <div class="summary-panel">
                        <h4>Payment Information</h4>
                        <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
                    </div>
                    <div class="summary-panel">
                        <h4>Order Summary</h4>
                        <div class="total-row"><span>Subtotal:</span> <span>$<?php echo money($order['total_amount'] - $order['shipping_amount'] - $order['tax_amount']); ?></span></div>
                        <div class="total-row"><span>Shipping:</span> <span>$<?php echo money($order['shipping_amount']); ?></span></div>
                        <div class="total-row"><span>Tax:</span> <span>$<?php echo money($order['tax_amount']); ?></span></div>
                        <div class="total-row grand-total"><span>Total:</span> <span>$<?php echo money($order['total_amount']); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>