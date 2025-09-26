<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/middleware.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isVendor()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$vendor_id = $_SESSION['vendor_id'];
$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$tracking_number = filter_input(INPUT_POST, 'tracking_number', FILTER_SANITIZE_STRING);
$shipping_carrier = filter_input(INPUT_POST, 'shipping_carrier', FILTER_SANITIZE_STRING);

$allowed_statuses = ['processing', 'shipped', 'delivered', 'cancelled'];

if (!$item_id || !$status || !in_array($status, $allowed_statuses)) {
    $response['message'] = 'Invalid data provided.';
    echo json_encode($response);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verify vendor owns this item
    $verify_sql = "SELECT order_id FROM order_items WHERE id = :item_id AND vendor_id = :vendor_id";
    $verify_stmt = $db->prepare($verify_sql);
    $verify_stmt->execute(['item_id' => $item_id, 'vendor_id' => $vendor_id]);
    $order_item = $verify_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order_item) {
        $response['message'] = 'You do not have permission to update this item.';
        echo json_encode($response);
        exit();
    }

    // Update the item
    $update_sql = "UPDATE order_items SET status = :status";
    $params = ['status' => $status, 'item_id' => $item_id];

    if ($status === 'shipped' && !empty($tracking_number) && !empty($shipping_carrier)) {
        $update_sql .= ", tracking_number = :tracking_number, shipping_carrier = :shipping_carrier";
        $params['tracking_number'] = $tracking_number;
        $params['shipping_carrier'] = $shipping_carrier;
    }

    $update_sql .= " WHERE id = :item_id";
    $update_stmt = $db->prepare($update_sql);

    if ($update_stmt->execute($params)) {
        // Optional: Update the main order status if all items are now shipped/delivered
        $order_id = $order_item['order_id'];
        $check_all_items_sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'shipped' OR status = 'delivered' THEN 1 ELSE 0 END) as fulfilled FROM order_items WHERE order_id = :order_id";
        $check_stmt = $db->prepare($check_all_items_sql);
        $check_stmt->execute(['order_id' => $order_id]);
        $counts = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($counts['total'] === $counts['fulfilled']) {
            $db->prepare("UPDATE orders SET status = 'shipped' WHERE id = :order_id")->execute(['order_id' => $order_id]);
        }

        $response['success'] = true;
        $response['message'] = 'Item status updated successfully!';
    } else {
        $response['message'] = 'Failed to update item status.';
    }
} catch (PDOException $e) {
    error_log("Update Order Item Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);