0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if user is logged in and an order ID is provided
if (!$isLoggedIn || !isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/orders.php');
    exit();
}

$order_id = intval($_GET['id']);
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
$items_query = "SELECT oi.*, p.name as product_name, p.images, r.id as review_id
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN reviews r ON r.product_id = oi.product_id AND r.user_id = :user_id
                WHERE oi.order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->execute(['order_id' => $order_id, 'user_id' => $user_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/order-success.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/order-detail.css">

<div class="order-success-page">
    <div class="container">
        <div class="success-header">
            <h1>Order Details</h1>
            <p>Order Number: <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></p>
            <p>Placed on: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        </div>

        <div class="order-summary-container">
            <div class="order-details-main">
                <div class="order-items-section">
                    <h2>Items in this Order</h2>
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
                                <div class="item-review-action">
                                    <?php if ($order['status'] === 'delivered'): ?>
                                        <?php if ($item['review_id']): ?>
                                            <button class="btn btn-sm btn-outline-success" disabled><i class="fas fa-check"></i> Reviewed</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary btn-write-review" 
                                                    data-product-id="<?php echo $item['product_id']; ?>" 
                                                    data-product-name="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                Write a Review
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
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
            <a href="<?php echo BASE_URL; ?>pages/orders.php" class="btn btn-primary">Back to My Orders</a>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal" id="reviewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="reviewModalTitle">Write a Review</h3>
            <button class="modal-close" id="reviewModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <form id="reviewForm">
                <input type="hidden" id="reviewProductId" name="product_id">
                <div class="form-group">
                    <label for="reviewRating">Your Rating</label>
                    <div class="star-rating">
                        <i class="far fa-star" data-value="1"></i>
                        <i class="far fa-star" data-value="2"></i>
                        <i class="far fa-star" data-value="3"></i>
                        <i class="far fa-star" data-value="4"></i>
                        <i class="far fa-star" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="reviewRating" required>
                    <div class="error-message" id="ratingError"></div>
                </div>
                <div class="form-group">
                    <label for="reviewComment">Your Review</label>
                    <textarea name="comment" id="reviewComment" rows="5" class="form-control" placeholder="Tell us what you think about the product..." required></textarea>
                    <div class="error-message" id="commentError"></div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/pages/order-detail.js"></script>

<?php
require_once '../includes/footer.php';
?>