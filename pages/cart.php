<?php
// Include config
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
if (!$isLoggedIn || !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

// Get cart items
$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, p.name, p.price, p.images, p.quantity as stock_quantity, 
                 v.business_name, v.business_logo 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          JOIN vendors v ON p.vendor_id = v.id 
          WHERE c.user_id = :user_id";


// Get user ID from session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$shipping = 0;
$tax = 0;
$total = 0;

foreach ($cart_items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
}

// Calculate shipping (free over $50, otherwise $5.99)
$shipping = $subtotal > 50 ? 0 : 5.99;

// Calculate tax (8% of subtotal)
$tax = $subtotal * 0.08;

// Calculate total
$total = $subtotal + $shipping + $tax;

// Include header
require_once '../includes/header.php';


// Add cart-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/cart.css">';

// Customer navigation (second nav)
?>
<nav class="main-nav customer-nav">
    <div class="container">
        <ul class="nav-menu">
            <li><a href="<?php echo BASE_URL; ?>pages/home.php">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a></li>
            <li class="dropdown">
                <a href="#">Categories <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-content">
                    <?php
                    $category_query = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name";
                    $category_stmt = $db->prepare($category_query);
                    $category_stmt->execute();
                    $categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $category) {
                        echo '<a href="' . BASE_URL . 'pages/products.php?category=' . htmlspecialchars($category['slug']) . '">' . htmlspecialchars($category['name']) . '</a>';
                    }
                    ?>
                </div>
            </li>
            <li><a href="#">Today\'s Deals</a></li>
            <li><a href="<?php echo BASE_URL; ?>pages/login.php?type=vendor">Become a Vendor</a></li>
        </ul>
    </div>
</nav>
?>

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

                <?php
                require_once '../includes/config.php';
                require_once '../includes/auth.php';

                // Start session if not started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
// ...existing code...

                $auth = new Auth();
                $isLoggedIn = $auth->isLoggedIn();
                if (!$isLoggedIn || !isset($_SESSION['user_id'])) {
                    header('Location: ' . BASE_URL . 'pages/login.php');
                    exit();
                }

                $userId = $_SESSION['user_id'];

                // Get cart items for user
                $database = new Database();
// ...existing code...
                    $tax = $subtotal * 0.08;
                    $total = $subtotal + $shipping + $tax;
                }

                // Include header and cart CSS
                require_once '../includes/header.php';
                echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/cart.css">';
                ?>
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="wishlist-btn" data-product-id="<?php echo $item['product_id']; ?>" title="Move to wishlist">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
// ...existing code...
                if (empty($cart_items)) {
                    echo '<div class="empty-cart">';
                    echo '<div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>';
                    echo '<h2>Your cart is empty</h2>';
                    echo '<p>Looks like you haven\'t added any items to your cart yet.</p>';
                    echo '<a href="products.php" class="btn btn-primary">Continue Shopping</a>';
                    echo '</div>';
                }
            </div>

<?php foreach ($cart_items as $item) { ?>
    <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
        <div class="item-product">
            <div class="product-image">
                <img src="../assets/images/products/<?php echo !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
            </div>
            <div class="product-details">
                <h3 class="product-name"><a href="product-detail.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                <div class="product-vendor">
                    <span class="vendor-logo"><img src="../assets/images/vendors/<?php echo $item['business_logo'] ?: 'default-logo.png'; ?>" alt="<?php echo htmlspecialchars($item['business_name']); ?>"></span>
                    <span class="vendor-name"><?php echo htmlspecialchars($item['business_name']); ?></span>
                </div>
            </div>
        </div>
        <div class="item-price"><span class="current-price">$<?php echo number_format($item['price'], 2); ?></span></div>
        <div class="item-quantity">
            <div class="quantity-controls">
                <button type="button" class="quantity-btn minus" data-item-id="<?php echo $item['id']; ?>">-</button>
                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" data-item-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                <button type="button" class="quantity-btn plus" data-item-id="<?php echo $item['id']; ?>">+</button>
            </div>
            <div class="stock-info">
                <?php if ($item['quantity'] > $item['stock_quantity']): ?>
                    <span class="stock-error">Only <?php echo $item['stock_quantity']; ?> available</span>
                <?php else: ?>
                    <span class="stock-available"><?php echo $item['stock_quantity']; ?> in stock</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="item-total"><span class="total-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></div>
        <div class="item-actions">
            <button class="remove-btn" data-item-id="<?php echo $item['id']; ?>" title="Remove item"><i class="fas fa-trash"></i></button>
            <button class="wishlist-btn" data-product-id="<?php echo $item['product_id']; ?>" title="Move to wishlist"><i class="fas fa-heart"></i></button>
        </div>
    </div>
<?php } ?>
// ...existing code...
                            <button class="btn btn-outline coupon-apply">Apply</button>
                        </div>
                    </div>

                    <div class="checkout-actions">
                        <a href="<?php echo BASE_URL; ?>pages/checkout.php" class="btn btn-primary btn-checkout">Proceed to Checkout</a>
                        <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-outline">Continue Shopping</a>
                    </div>

                    <div class="security-features">
                        <div class="security-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure checkout</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-lock"></i>
                            <span>SSL encrypted</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-undo"></i>
                            <span>30-day returns</span>
                        </div>
                    </div>
                </div>

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
                                            LIMIT 3";
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
// ...existing code...
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const quantityControls = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Plus button
            document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.itemId;
                    const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    const max = parseInt(input.getAttribute('max'));
                    const currentValue = parseInt(input.value);
                    
                    if (currentValue < max) {
                        input.value = currentValue + 1;
                        quantityControls.updateItem(itemId, input.value);
                    }
                });
            });
            
            // Minus button
            document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.itemId;
                    const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    const currentValue = parseInt(input.value);
                    
                    if (currentValue > 1) {
                        input.value = currentValue - 1;
                        quantityControls.updateItem(itemId, input.value);
                    }
                });
            });
            
            // Input change
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    const itemId = this.dataset.itemId;
                    const max = parseInt(this.getAttribute('max'));
                    let value = parseInt(this.value);
                    
                    if (isNaN(value) || value < 1) {
                        value = 1;
                    } else if (value > max) {
                        value = max;
                    }
                    
                    this.value = value;
                    quantityControls.updateItem(itemId, value);
                });
            });
            
            // Remove button
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.itemId;
                    quantityControls.removeItem(itemId);
                });
            });
        },
        
        updateItem: function(itemId, quantity) {
            // Simulate AJAX call to update cart
            const item = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
            const price = parseFloat(item.querySelector('.quantity-input').dataset.price);
            const totalElement = item.querySelector('.total-price');
            
            // Update total
            const total = price * quantity;
            totalElement.textContent = '$' + total.toFixed(2);
            
            // Update cart summary
            this.updateCartSummary();
        },
        
        removeItem: function(itemId) {
            // Simulate AJAX call to remove item
            const item = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
            item.style.opacity = '0';
            item.style.height = item.offsetHeight + 'px';
            
            setTimeout(() => {
                item.remove();
                this.updateCartSummary();
                
                // Check if cart is empty
                if (document.querySelectorAll('.cart-item').length === 0) {
                    this.showEmptyCart();
                }
            }, 300);
        },
        
        updateCartSummary: function() {
            // Recalculate totals
            let subtotal = 0;
            
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.quantity-input').dataset.price);
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                subtotal += price * quantity;
            });
            
            // Update UI
            const shipping = subtotal > 50 ? 0 : 5.99;
            const tax = subtotal * 0.08;
            const total = subtotal + shipping + tax;
            
            document.querySelector('.subtotal-amount').textContent = '$' + subtotal.toFixed(2);
            document.querySelector('.shipping-amount').innerHTML = shipping === 0 ? 
                '<span class="free-shipping">FREE</span>' : '$' + shipping.toFixed(2);
            document.querySelector('.tax-amount').textContent = '$' + tax.toFixed(2);
            document.querySelector('.total-amount').textContent = '$' + total.toFixed(2);
            
            // Update shipping progress
            if (subtotal < 50) {
                const progressFill = document.querySelector('.progress-fill');
                const progressText = document.querySelector('.shipping-progress p');
                const amountNeeded = 50 - subtotal;
                
                progressFill.style.width = (subtotal / 50) * 100 + '%';
                progressText.innerHTML = `Add $${amountNeeded.toFixed(2)} more for <strong>FREE shipping</strong>`;
            }
        },
        
        showEmptyCart: function() {
            const cartItems = document.querySelector('.cart-items');
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            `;
            
            document.querySelector('.cart-summary').style.display = 'none';
        }
    };
    
    // Initialize quantity controls
    quantityControls.init();
    
    // Coupon toggle
    const couponToggle = document.querySelector('.coupon-toggle');
    const couponForm = document.querySelector('.coupon-form');
    
    if (couponToggle && couponForm) {
        couponToggle.addEventListener('click', function() {
            couponForm.classList.toggle('active');
            this.querySelector('.fa-chevron-down').classList.toggle('active');
        });
    }
    
    // Add to cart buttons for recommended products
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            // Simulate add to cart
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-check"></i> Added';
                setTimeout(() => {
                    this.innerHTML = 'Add to Cart';
                    this.disabled = false;
                }, 2000);
            }, 1000);
        });
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>