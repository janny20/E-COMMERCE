<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// --- CONFIGURATION ---
define('PLATFORM_COMMISSION_RATE', 0.10); // 10% commission

// Check if user is logged in and it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Get cart items for the user
$cart_query = "SELECT c.product_id, c.quantity, p.price, p.vendor_id
               FROM cart c
               JOIN products p ON c.product_id = p.id
               WHERE c.user_id = :user_id";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->execute(['user_id' => $user_id]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: ' . BASE_URL . 'pages/cart.php');
    exit();
}

// --- SIMULATE PAYMENT GATEWAY INTERACTION ---
// In a real application, you would integrate with Stripe, PayPal, or a local gateway here.
// The gateway would return a transaction ID and a success status.
// For this example, we'll assume the payment is always successful.
$payment_successful = true;
$payment_method = $_POST['payment_method'] ?? 'unknown';
$payment_status = 'completed'; // Assume payment is completed instantly

if (!$payment_successful) {
    // Redirect back to checkout with an error message
    $_SESSION['checkout_error'] = "Payment failed. Please try again.";
    header('Location: ' . BASE_URL . 'pages/checkout.php');
    exit();
}

// --- GET FINAL CART DATA (including coupon) ---
$cart_data = getCartData($db, $user_id);
$total = (float) str_replace(',', '', $cart_data['total']);
$subtotal = (float) str_replace(',', '', $cart_data['subtotal']);
$discount = (float) str_replace(',', '', $cart_data['discount']);
$tax = (float) str_replace(',', '', $cart_data['tax']);
$shipping = (float) str_replace(',', '', $cart_data['shipping']);

// --- PROCESS ORDER ---
try {
    $db->beginTransaction();

    // 1. Create the main order record
    $order_number = 'ORD-' . strtoupper(uniqid());
    $order_notes = filter_var($_POST['order_notes'] ?? '', FILTER_SANITIZE_STRING);

    // Determine shipping address
    $selected_address_id = $_POST['selected_address_id'] ?? null;
    $shipping_name = '';
    $shipping_address_full = '';

    if ($selected_address_id && $selected_address_id !== 'new') {
        // Use a saved address
        $addr_stmt = $db->prepare("SELECT * FROM user_addresses WHERE id = :id AND user_id = :user_id");
        $addr_stmt->execute(['id' => $selected_address_id, 'user_id' => $user_id]);
        $address = $addr_stmt->fetch(PDO::FETCH_ASSOC);
        if ($address) {
            $shipping_name = $address['full_name'];
            $shipping_address_full = implode(', ', array_filter([$address['address_line_1'], $address['address_line_2'], $address['city'], $address['state'], $address['zip_code'], $address['country']]));
        } else {
            throw new Exception("Selected shipping address not found.");
        }
    } else {
        // Use the new address from the form
        $shipping_name = trim($_POST['full_name'] ?? '');
        $shipping_address_full = implode(', ', array_filter([
            trim($_POST['address_line_1'] ?? ''),
            trim($_POST['address_line_2'] ?? ''),
            trim($_POST['city'] ?? ''),
            trim($_POST['state'] ?? ''),
            trim($_POST['zip_code'] ?? ''),
            trim($_POST['country'] ?? '')
        ]));
    }

    $order_sql = "INSERT INTO orders (order_number, customer_id, total_amount, tax_amount, shipping_amount, shipping_name, shipping_address, order_notes, payment_method, payment_status, status)
                  VALUES (:order_number, :customer_id, :total_amount, :tax_amount, :shipping_amount, :shipping_name, :shipping_address, :order_notes, :payment_method, :payment_status, 'processing')";
    $order_stmt = $db->prepare($order_sql);
    $order_stmt->execute([
        ':order_number' => $order_number,
        ':customer_id' => $user_id,
        ':total_amount' => $total,
        ':tax_amount' => $tax,
        ':shipping_amount' => $shipping,
        ':shipping_name' => $shipping_name,
        ':shipping_address' => $shipping_address_full,
        ':order_notes' => $order_notes,
        ':payment_method' => $payment_method,
        ':payment_status' => $payment_status
    ]);
    $order_id = $db->lastInsertId();

    // Log initial order status
    logOrderStatus($db, $order_id, 'processing', 'Order placed successfully.');

    // 2. Create order items and vendor earnings records
    $order_item_sql = "INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price, total, status)
                       VALUES (:order_id, :product_id, :vendor_id, :quantity, :price, :total, 'processing')";
    $order_item_stmt = $db->prepare($order_item_sql);

    $earning_sql = "INSERT INTO vendor_earnings (vendor_id, order_id, order_item_id, item_total_amount, commission_rate, commission_amount, net_earning)
                    VALUES (:vendor_id, :order_id, :order_item_id, :item_total_amount, :commission_rate, :commission_amount, :net_earning)";
    $earning_stmt = $db->prepare($earning_sql);

    foreach ($cart_items as $item) {
        $item_total = $item['price'] * $item['quantity'];

        // Insert into order_items
        $order_item_stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['product_id'],
            ':vendor_id' => $item['vendor_id'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price'],
            ':total' => $item_total
        ]);
        $order_item_id = $db->lastInsertId();

        // Calculate and insert into vendor_earnings
        $commission_amount = $item_total * PLATFORM_COMMISSION_RATE;
        $net_earning = $item_total - $commission_amount;

        $earning_stmt->execute([
            ':vendor_id' => $item['vendor_id'],
            ':order_id' => $order_id,
            ':order_item_id' => $order_item_id,
            ':item_total_amount' => $item_total,
            ':commission_rate' => PLATFORM_COMMISSION_RATE,
            ':commission_amount' => $commission_amount,
            ':net_earning' => $net_earning
        ]);
    }

    // 3. Update coupon usage if one was applied
    if (isset($_SESSION['cart_coupon']['id'])) {
        $coupon_id = $_SESSION['cart_coupon']['id'];
        
        // Add coupon info to the order
        $update_order_stmt = $db->prepare("UPDATE orders SET coupon_code = :code, discount_amount = :discount WHERE id = :order_id");
        $update_order_stmt->execute([
            ':code' => $_SESSION['cart_coupon']['code'],
            ':discount' => $discount,
            ':order_id' => $order_id
        ]);

        // Increment coupon usage
        $update_coupon_stmt = $db->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = :coupon_id");
        $update_coupon_stmt->execute([':coupon_id' => $coupon_id]);
        unset($_SESSION['cart_coupon']); // Clear coupon from session after use
    }

    // 3. Clear the user's cart
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = :user_id";
    $clear_cart_stmt = $db->prepare($clear_cart_sql);
    $clear_cart_stmt->execute(['user_id' => $user_id]);

    // 4. Commit the transaction
    $db->commit();

    // 5. Redirect to a success page
    header('Location: ' . BASE_URL . 'pages/order-success.php?order_id=' . $order_id);
    exit();

} catch (PDOException $e) {
    // If anything fails, roll back the entire transaction
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    // Log the error and show a generic error message
    error_log("Checkout Error: " . $e->getMessage());
    $_SESSION['checkout_error'] = "An error occurred while processing your order. Please contact support.";
    header('Location: ' . BASE_URL . 'pages/checkout.php');
    exit();
}