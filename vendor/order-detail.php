<?php
// vendor/order-detail.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();

$vendor_id = $_SESSION['vendor_id'] ?? null;
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$vendor_id || !$order_id) {
    // Redirect or show error if vendor or order ID is missing
    header('Location: ' . BASE_URL . 'vendor/orders.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$order = null;
$order_items = [];
$customer = null;

try {
    // First, verify this vendor has items in this order to prevent unauthorized access
    $verify_sql = "SELECT COUNT(*) FROM order_items WHERE order_id = :order_id AND vendor_id = :vendor_id";
    $verify_stmt = $db->prepare($verify_sql);
    $verify_stmt->execute(['order_id' => $order_id, 'vendor_id' => $vendor_id]);
    if ($verify_stmt->fetchColumn() == 0) {
        // This vendor does not own any items in this order, deny access
        header('Location: ' . BASE_URL . 'vendor/orders.php?error=access_denied');
        exit();
    }

    // Fetch main order details
    $order_sql = "SELECT * FROM orders WHERE id = :order_id";
    $order_stmt = $db->prepare($order_sql);
    $order_stmt->execute(['order_id' => $order_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Fetch customer details
        $customer_sql = "SELECT u.email, up.first_name, up.last_name, up.phone 
                         FROM users u 
                         LEFT JOIN user_profiles up ON u.id = up.user_id 
                         WHERE u.id = :customer_id";
        $customer_stmt = $db->prepare($customer_sql);
        $customer_stmt->execute(['customer_id' => $order['customer_id']]);
        $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch only the order items belonging to this vendor
        $items_sql = "SELECT oi.*, p.name as product_name, p.images, p.sku
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = :order_id AND oi.vendor_id = :vendor_id";
        $items_stmt = $db->prepare($items_sql);
        $items_stmt->execute(['order_id' => $order_id, 'vendor_id' => $vendor_id]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Log error and show a generic message
    error_log("Vendor Order Detail Error: " . $e->getMessage());
    die("An error occurred while fetching order details.");
}

if (!$order) {
    header('Location: ' . BASE_URL . 'vendor/orders.php?error=not_found');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-order-detail.css">

<div class="vendor-dashboard container">
    <div class="dashboard-header">
        <h1>Order Details</h1>
        <p>
            <a href="<?php echo BASE_URL; ?>vendor/orders.php" class="btn btn-sm btn-outline">&larr; Back to Orders</a>
        </p>
    </div>

    <div class="order-detail-layout">
        <div class="order-main-content">
            <div class="content-card">
                <div class="content-card-header">
                    <h3>Items in Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Item Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $vendor_subtotal = 0;
                            foreach ($order_items as $item): 
                                $vendor_subtotal += $item['total'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="product-info-cell">
                                            <?php $img = !empty($item['images']) ? explode(',', $item['images'])[0] : 'default.jpg'; ?>
                                            <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-cell-image">
                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo money($item['price']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>$<?php echo money($item['total']); ?></td>
                                    <td>
                                        <select class="form-select form-select-sm item-status-select" data-item-id="<?php echo $item['id']; ?>" data-current-status="<?php echo $item['status']; ?>">
                                            <option value="processing" <?php echo $item['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $item['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $item['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $item['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <?php if (!empty($item['tracking_number'])): ?>
                                            <div class="tracking-info">
                                                <i class="fas fa-truck"></i>
                                                <?php echo htmlspecialchars($item['shipping_carrier'] . ': ' . $item['tracking_number']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Your Subtotal:</strong></td>
                                <td><strong>$<?php echo money($vendor_subtotal); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="order-sidebar">
            <div class="content-card">
                <div class="content-card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="summary-list">
                    <div class="summary-item">
                        <span>Order Number:</span>
                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Order Date:</span>
                        <span><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Order Status:</span>
                        <span class="status-badge status-<?php echo str_replace('_', '-', $order['status']); ?>"><?php echo ucwords(str_replace('_', ' ', $order['status'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Payment Method:</span>
                        <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Payment Status:</span>
                        <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="content-card-header">
                    <h3>Customer & Shipping</h3>
                </div>
                <div class="summary-list">
                    <div class="summary-item">
                        <span>Customer Name:</span>
                        <strong><?php echo htmlspecialchars(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? 'Guest'))); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Email:</span>
                        <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>"><?php echo htmlspecialchars($customer['email']); ?></a>
                    </div>
                    <div class="summary-item">
                        <span>Phone:</span>
                        <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></a>
                    </div>
                    <div class="summary-item shipping-address">
                        <span>Shipping Address:</span>
                        <address>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </address>
                    </div>
                </div>
            </div>

            <?php if (!empty($order['order_notes'])): ?>
            <div class="content-card">
                <div class="content-card-header">
                    <h3>Customer Notes</h3>
                </div>
                <div class="summary-list">
                    <p style="white-space: pre-wrap; font-size: var(--font-size-sm);"><?php echo htmlspecialchars($order['order_notes']); ?></p>
                </div>
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Info Modal -->
<div class="modal" id="trackingModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Tracking Information</h3>
            <button class="modal-close" id="trackingModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div id="tracking-modal-messages"></div>
            <form id="trackingForm">
                <input type="hidden" id="trackingOrderItemId" name="item_id">
                <div class="form-group">
                    <label for="shipping_carrier">Shipping Carrier</label>
                    <select id="shipping_carrier" name="shipping_carrier" class="form-control" required>
                        <option value="">Select a carrier</option>
                        <option value="UPS">UPS</option>
                        <option value="FedEx">FedEx</option>
                        <option value="USPS">USPS</option>
                        <option value="DHL">DHL</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tracking_number">Tracking Number</label>
                    <input type="text" id="tracking_number" name="tracking_number" class="form-control" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Tracking Info</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>