<?php
// Start session first to ensure login status is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once '../includes/config.php';

// Establish database connection early so it's available for the whole page
$database = new Database();
$db = $database->getConnection();

$cart_items = [];
if ($isLoggedIn && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Get cart items for logged-in user
    $query = "SELECT c.*, p.name, p.price, p.images, p.quantity as stock_quantity, 
                     v.business_name, v.business_logo 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              JOIN vendors v ON p.vendor_id = v.id 
              WHERE c.user_id = :user_id
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// If user is not logged in, $cart_items remains an empty array, and the page will display the "empty cart" message.

// Get cart data including totals and coupon info
$cart_data = getCartData($db, $userId);
$subtotal = $cart_data['subtotal'];
$total_amount = $cart_data['total'];

// Include header
require_once '../includes/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/cart.css">

<div class="cart-page">
    <div class="container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <div class="cart-steps">
                <div class="cart-step active">
                    <span class="step-number">1</span>
                    <span class="step-text">Shopping Cart</span>
                </div>
                <div class="cart-step">
                    <span class="step-number">2</span>
                    <span class="step-text">Checkout</span>
                </div>
                <div class="cart-step">
                    <span class="step-number">3</span>
                    <span class="step-text">Order Complete</span>
                </div>
            </div>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                            <div class="item-product">
                                <div class="product-image">
                                    <img src="../assets/images/products/<?php echo !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="product-details">
                                    <h3 class="product-name"><a href="product-detail.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                                    <p class="product-vendor">Sold by: <?php echo htmlspecialchars($item['business_name']); ?></p>
                                </div>
                            </div>
                            <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="item-quantity">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn minus" data-item-id="<?php echo $item['id']; ?>">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" data-item-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                                    <button type="button" class="quantity-btn plus" data-item-id="<?php echo $item['id']; ?>">+</button>
                                </div>
                            </div>
                            <div class="item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            <div class="item-actions">
                                <button class="remove-btn" data-item-id="<?php echo $item['id']; ?>" title="Remove item"><i class="fas fa-trash"></i></button>
                                <button class="move-to-wishlist-btn" data-item-id="<?php echo $item['id']; ?>" title="Move to wishlist"><i class="fas fa-heart"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span class="subtotal-amount">$<?php echo $subtotal; ?></span>
                        </div>
                        <div class="total-row discount-row" style="<?php echo ($cart_data['discount'] > 0) ? '' : 'display: none;'; ?>">
                            <span>Discount</span>
                            <span class="discount-amount text-success">-$<?php echo $cart_data['discount']; ?></span>
                        </div>
                        <div class="total-row">
                            <span>Shipping</span>
                            <span class="shipping-amount"><?php echo $cart_data['shipping'] == 0 ? '<span class="free-shipping">FREE</span>' : '$' . $cart_data['shipping']; ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax (8%)</span>
                            <span class="tax-amount">$<?php echo $cart_data['tax']; ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span class="total-amount">$<?php echo $total_amount; ?></span>
                        </div>
                    </div>

                    <?php if ($cart_data['amountNeededForFreeShipping'] > 0): ?>
                    <div class="shipping-progress">
                        <p>Add $<?php echo number_format($cart_data['amountNeededForFreeShipping'], 2); ?> more for <strong>FREE shipping</strong></p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo (($cart_data['subtotal_raw'] - $cart_data['discount']) / $cart_data['freeShippingThreshold']) * 100; ?>%;"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="coupon-section">
                        <form class="coupon-form">
                            <input type="text" class="form-control coupon-input" placeholder="Enter coupon code">
                            <button type="button" class="btn btn-outline coupon-apply">Apply</button>
                        </form>
                        <div class="applied-coupon" style="<?php echo $cart_data['coupon_code'] ? '' : 'display: none;'; ?>">
                            <span class="applied-coupon-info">
                                <i class="fas fa-check-circle text-success"></i> Coupon "<strong><span class="coupon-code-text"><?php echo htmlspecialchars($cart_data['coupon_code'] ?? ''); ?></span></strong>" applied!
                            </span>
                            <button class="btn-link remove-coupon-btn">Remove</button>
                        </div>
                    </div>

                    <div class="checkout-actions">
                        <a href="<?php echo BASE_URL; ?>pages/checkout.php" class="btn btn-primary btn-checkout">Proceed to Checkout</a>
                        <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-outline">Continue Shopping</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="recommended-products">
            <h3>You might also like</h3>
            <div class="recommended-grid">
                <?php
                // Get recommended products
                $recommended_query = "SELECT p.*, v.business_name 
                                    FROM products p 
                                    JOIN vendors v ON p.vendor_id = v.id 
                                    WHERE p.status = 'active' 
                                    ORDER BY RAND() 
                                    LIMIT 4";
                $recommended_stmt = $db->prepare($recommended_query);
                $recommended_stmt->execute();
                $recommended_products = $recommended_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($recommended_products as $product):
                ?>
                    <div class="recommended-product">
                        <div class="product-image">
                            <img src="../assets/images/products/<?php echo !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <button class="btn btn-sm add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Page-specific JavaScript -->
<script src="<?php echo BASE_URL; ?>assets/js/pages/cart.js"></script>

<?php
// Include footer
require_once '../includes/footer.php';
?>