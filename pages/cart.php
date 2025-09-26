<?php
// Include config
require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
}

// Calculate shipping (free over $50, otherwise $5.99)
$shipping_cost = $subtotal > 50 ? 0 : 5.99;

// Calculate tax (8% of subtotal)
$tax_amount = $subtotal * 0.08;

// Calculate total
$total_amount = $subtotal + $shipping_cost + $tax_amount;

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
                                <button class="wishlist-btn" data-product-id="<?php echo $item['product_id']; ?>" title="Move to wishlist"><i class="fas fa-heart"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span class="subtotal-amount">$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Shipping</span>
                            <span class="shipping-amount"><?php echo $shipping_cost == 0 ? '<span class="free-shipping">FREE</span>' : '$' . number_format($shipping_cost, 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax (8%)</span>
                            <span class="tax-amount">$<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span class="total-amount">$<?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>

                    <?php if ($shipping_cost > 0): ?>
                    <div class="shipping-progress">
                        <p>Add $<?php echo number_format(50 - $subtotal, 2); ?> more for <strong>FREE shipping</strong></p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($subtotal / 50) * 100; ?>%;"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="coupon-section">
                        <form class="coupon-form">
                            <input type="text" class="form-control coupon-input" placeholder="Enter coupon code">
                            <button type="submit" class="btn btn-outline coupon-apply">Apply</button>
                        </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartPage = document.querySelector('.cart-page');
    if (!cartPage) return;

    // --- Debounce function to prevent spamming updates ---
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // --- Function to send AJAX request ---
    function sendCartRequest(action, itemId, quantity = null) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('item_id', itemId);
        if (quantity !== null) {
            formData.append('quantity', quantity);
        }

        const itemRow = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
        if (itemRow) itemRow.classList.add('loading');

        fetch('../ajax/update_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartUI(data.cart);
                if (action === 'remove') {
                    removeItemFromDOM(itemId);
                }
                if (action === 'update' && data.new_quantity) {
                    const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    if (input) input.value = data.new_quantity;
                }
                if (data.message && data.message !== 'Cart updated successfully.') {
                    showNotification(data.message, 'info');
                }
            } else {
                showNotification(data.message || 'Failed to update cart.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            if (itemRow) itemRow.classList.remove('loading');
        });
    }

    // --- Function to update the UI with new cart data ---
    function updateCartUI(cartData) {
        document.querySelector('.subtotal-amount').textContent = '$' + cartData.subtotal;
        document.querySelector('.tax-amount').textContent = '$' + cartData.tax;
        document.querySelector('.total-amount').textContent = '$' + cartData.total;
        
        const shippingAmountEl = document.querySelector('.shipping-amount');
        if (parseFloat(cartData.shipping) === 0) {
            shippingAmountEl.innerHTML = '<span class="free-shipping">FREE</span>';
        } else {
            shippingAmountEl.textContent = '$' + cartData.shipping;
        }

        document.querySelectorAll('.cart-link').forEach(el => {
            el.innerHTML = `<i class="fas fa-shopping-cart"></i> Cart (${cartData.cartCount})`;
        });

        const progressContainer = document.querySelector('.shipping-progress');
        if (progressContainer) {
            if (cartData.amountNeededForFreeShipping > 0) {
                progressContainer.style.display = 'block';
                const subtotal = parseFloat(cartData.subtotal.replace(/,/g, ''));
                const threshold = parseFloat(cartData.freeShippingThreshold);
                progressContainer.querySelector('p').innerHTML = `Add $${cartData.amountNeededForFreeShipping.toFixed(2)} more for <strong>FREE shipping</strong>`;
                progressContainer.querySelector('.progress-fill').style.width = `${(subtotal / threshold) * 100}%`;
            } else {
                progressContainer.style.display = 'none';
            }
        }

        document.querySelectorAll('.cart-item').forEach(itemRow => {
            const price = parseFloat(itemRow.querySelector('.quantity-input').dataset.price);
            const quantity = parseInt(itemRow.querySelector('.quantity-input').value);
            itemRow.querySelector('.item-total').textContent = '$' + (price * quantity).toFixed(2);
        });

        if (cartData.cartCount === 0) {
            showEmptyCartMessage();
        }
    }

    function removeItemFromDOM(itemId) {
        const itemRow = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
        if (itemRow) {
            itemRow.style.opacity = '0';
            itemRow.style.transform = 'translateX(-20px)';
            setTimeout(() => itemRow.remove(), 300);
        }
    }

    function showEmptyCartMessage() {
        const cartContent = document.querySelector('.cart-content');
        if (cartContent) {
            cartContent.innerHTML = `
                <div class="empty-cart" style="grid-column: 1 / -1;">
                    <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            `;
        }
    }

    const debouncedUpdateRequest = debounce((itemId, quantity) => sendCartRequest('update', itemId, quantity), 500);

    cartPage.addEventListener('click', function(e) {
        if (e.target.matches('.quantity-btn')) {
            const btn = e.target;
            const input = btn.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            const max = parseInt(input.max);
            const itemId = input.dataset.itemId;

            if (btn.classList.contains('plus') && value < max) value++;
            else if (btn.classList.contains('minus') && value > 1) value--;
            
            input.value = value;
            debouncedUpdateRequest(itemId, value);
        }

        if (e.target.closest('.remove-btn')) {
            const btn = e.target.closest('.remove-btn');
            const itemId = btn.dataset.itemId;
            if (confirm('Are you sure you want to remove this item?')) {
                sendCartRequest('remove', itemId);
            }
        }
    });

    cartPage.addEventListener('change', function(e) {
        if (e.target.matches('.quantity-input')) {
            const input = e.target;
            let value = parseInt(input.value);
            const max = parseInt(input.max);
            const itemId = input.dataset.itemId;

            if (isNaN(value) || value < 1) value = 1;
            else if (value > max) value = max;
            
            input.value = value;
            debouncedUpdateRequest(itemId, value);
        }
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>