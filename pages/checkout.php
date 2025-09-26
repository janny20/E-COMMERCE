<?php
// Include config
require_once '../includes/config.php';

// Get cart items
$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, p.name, p.price, p.images, v.business_name 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          JOIN vendors v ON p.vendor_id = v.id 
          WHERE c.user_id = :user_id";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user is logged in, redirect if not
if (!$isLoggedIn) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
}

$shipping = $subtotal > 50 ? 0 : 5.99;
$tax = $subtotal * 0.08;
$total = $subtotal + $shipping + $tax;

// Get user data
$user_query = "SELECT u.*, up.* 
               FROM users u 
               LEFT JOIN user_profiles up ON u.id = up.user_id 
               WHERE u.id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$user_stmt->execute();
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';

// Add checkout-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/checkout.css">';
?>

<div class="checkout-page">
    <div class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <div class="checkout-steps">
                <div class="checkout-step completed">
                    <span class="step-number">1</span>
                    <span class="step-text">Shopping Cart</span>
                </div>
                <div class="checkout-step active">
                    <span class="step-number">2</span>
                    <span class="step-text">Checkout</span>
                </div>
                <div class="checkout-step">
                    <span class="step-number">3</span>
                    <span class="step-text">Order Complete</span>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['checkout_error'])): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem;">
                <?php echo htmlspecialchars($_SESSION['checkout_error']); unset($_SESSION['checkout_error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>pages/process_payment.php" class="checkout-form" id="checkout-form">
            <div class="checkout-content">
                <div class="checkout-main">
                    <div class="checkout-section">
                        <h2 class="section-title">Shipping Information</h2>
                        <div class="section-content">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" required 
                                           value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" required 
                                           value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" required 
                                           value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group full-width">
                                    <label for="shipping_address">Shipping Address *</label>
                                    <textarea id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <input type="text" id="city" name="city" required 
                                           value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="state">State *</label>
                                    <input type="text" id="state" name="state" required 
                                           value="<?php echo htmlspecialchars($user_data['state'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="zip_code">ZIP Code *</label>
                                    <input type="text" id="zip_code" name="zip_code" required 
                                           value="<?php echo htmlspecialchars($user_data['zip_code'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="country">Country *</label>
                                    <select id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="US" <?php echo ($user_data['country'] ?? '') == 'US' ? 'selected' : ''; ?>>United States</option>
                                        <option value="UK" <?php echo ($user_data['country'] ?? '') == 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="CA" <?php echo ($user_data['country'] ?? '') == 'CA' ? 'selected' : ''; ?>>Canada</option>
                                        <option value="AU" <?php echo ($user_data['country'] ?? '') == 'AU' ? 'selected' : ''; ?>>Australia</option>
                                        <option value="NG" <?php echo ($user_data['country'] ?? '') == 'NG' ? 'selected' : ''; ?>>Nigeria</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <h2 class="section-title">Payment Method</h2>
                        <div class="section-content">
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="payment_card" name="payment_method" value="card" checked>
                                    <label for="payment_card">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Credit/Debit Card</span>
                                    </label>
                                    <div class="payment-details">
                                        <div class="form-grid">
                                            <div class="form-group full-width">
                                                <label for="card_number">Card Number *</label>
                                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                            </div>
                                            <div class="form-group">
                                                <label for="card_name">Name on Card *</label>
                                                <input type="text" id="card_name" name="card_name" placeholder="John Doe">
                                            </div>
                                            <div class="form-group">
                                                <label for="card_expiry">Expiry Date *</label>
                                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                                            </div>
                                            <div class="form-group">
                                                <label for="card_cvv">CVV *</label>
                                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="payment_momo" name="payment_method" value="mobile_money">
                                    <label for="payment_momo">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>Mobile Money (MTN, Telecel)</span>
                                    </label>
                                    <div class="payment-details">
                                        <div class="form-group">
                                            <label for="momo_number">Phone Number *</label>
                                            <input type="tel" id="momo_number" name="momo_number" placeholder="024 123 4567">
                                        </div>
                                        <div class="form-group">
                                            <label for="momo_network">Network *</label>
                                            <select id="momo_network" name="momo_network">
                                                <option value="mtn">MTN Mobile Money</option>
                                                <option value="telecel">Telecel Cash</option>
                                            </select>
                                        </div>
                                        <p class="form-hint">You will receive a prompt on your phone to approve the payment.</p>
                                    </div>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="payment_paypal" name="payment_method" value="paypal">
                                    <label for="payment_paypal">
                                        <i class="fab fa-paypal"></i>
                                        <span>PayPal</span>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="payment_bank" name="payment_method" value="bank_transfer">
                                    <label for="payment_bank">
                                        <i class="fas fa-university"></i>
                                        <span>Bank Transfer</span>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="payment_cod" name="payment_method" value="cash_on_delivery">
                                    <label for="payment_cod">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Cash on Delivery</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <h2 class="section-title">Order Notes</h2>
                        <div class="section-content">
                            <div class="form-group">
                                <label for="order_notes">Additional Notes (Optional)</label>
                                <textarea id="order_notes" name="order_notes" rows="3" placeholder="Special instructions for delivery..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="../assets/images/products/<?php echo !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Qty: <?php echo $item['quantity']; ?></p>
                                    <p class="item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Shipping:</span>
                                <span>
                                    <?php if ($shipping == 0): ?>
                                        <span class="free-shipping">FREE</span>
                                    <?php else: ?>
                                        $<?php echo number_format($shipping, 2); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="total-row">
                                <span>Tax:</span>
                                <span>$<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <div class="coupon-section">
                            <div class="coupon-toggle">
                                <i class="fas fa-tag"></i>
                                <span>Apply coupon code</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="coupon-form">
                                <input type="text" placeholder="Enter coupon code" class="coupon-input">
                                <button type="button" class="btn btn-outline coupon-apply">Apply</button>
                            </div>
                        </div>

                        <div class="checkout-actions">
                            <button type="submit" class="btn btn-primary btn-place-order">
                                <i class="fas fa-lock"></i>
                                Place Order
                            </button>
                            <a href="<?php echo BASE_URL; ?>pages/cart.php" class="btn btn-outline">Back to Cart</a>
                        </div>

                        <div class="security-notice">
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
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method toggle
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentDetails = document.querySelectorAll('.payment-details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            paymentDetails.forEach(detail => {
                detail.style.display = 'none';
            });
            
            const selectedDetails = this.parentElement.querySelector('.payment-details');
            if (selectedDetails) {
                selectedDetails.style.display = 'block';
            }
        });
    });
    
    // Show card details by default
    document.querySelector('#payment_card').dispatchEvent(new Event('change'));
    
    // Coupon toggle
    const couponToggle = document.querySelector('.coupon-toggle');
    const couponForm = document.querySelector('.coupon-form');
    
    if (couponToggle && couponForm) {
        couponToggle.addEventListener('click', function() {
            couponForm.classList.toggle('active');
            this.querySelector('.fa-chevron-down').classList.toggle('active');
        });
    }
    
    // Form validation
    const checkoutForm = document.querySelector('.checkout-form');
    const placeOrderBtn = document.querySelector('.btn-place-order');
    
    checkoutForm.addEventListener('submit', function(e) {
        // The form will be submitted after validation, so we only prevent it if invalid.
        
        // Basic validation
        let isValid = true;
        const requiredFields = this.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger-color)';
                // Only mark as invalid if the field is visible
                if (field.offsetParent !== null) {
                    isValid = false;
                }
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            e.preventDefault(); // Stop form submission
            alert('Please fill in all required fields.');
        } else {
            // If form is valid, show loading state
            placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            placeOrderBtn.disabled = true;
        }
    });
    
    // Card number formatting
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value.substring(0, 19);
        });
    }
    
    // Expiry date formatting
    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value.substring(0, 5);
        });
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>