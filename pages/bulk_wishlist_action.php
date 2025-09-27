0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to perform this action.';
    $response['require_login'] = true;
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;
$product_ids = $_POST['product_ids'] ?? [];

if (empty($action) || !in_array($action, ['bulk_remove', 'bulk_add_to_cart'])) {
    $response['message'] = 'Invalid action specified.';
    echo json_encode($response);
    exit();
}

if (empty($product_ids) || !is_array($product_ids)) {
    $response['message'] = 'No products selected.';
    echo json_encode($response);
    exit();
}

// Sanitize product IDs to ensure they are all integers
$product_ids = array_map('intval', $product_ids);
$product_ids = array_filter($product_ids, fn($id) => $id > 0);

if (empty($product_ids)) {
    $response['message'] = 'Invalid product IDs provided.';
    echo json_encode($response);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();

    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    if ($action === 'bulk_remove') {
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $params = array_merge([$user_id], $product_ids);
        $stmt->execute($params);

        $response['success'] = true;
        $response['message'] = $stmt->rowCount() . ' item(s) removed from your wishlist.';
    } elseif ($action === 'bulk_add_to_cart') {
        $added_count = 0;
        foreach ($product_ids as $product_id) {
            // Check if product is in stock
            $stock_check_stmt = $db->prepare("SELECT quantity FROM products WHERE id = ?");
            $stock_check_stmt->execute([$product_id]);
            $stock_quantity = $stock_check_stmt->fetchColumn();

            if ($stock_quantity > 0) {
                // Check if item is already in cart
                $cart_check_stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $cart_check_stmt->execute([$user_id, $product_id]);
                $cart_item = $cart_check_stmt->fetch();

                if ($cart_item) {
                    // Item exists, update quantity
                    $update_cart_stmt = $db->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
                    $update_cart_stmt->execute([$cart_item['id']]);
                } else {
                    // Item does not exist, insert it
                    $insert_cart_stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                    $insert_cart_stmt->execute([$user_id, $product_id]);
                }
                $added_count++;
            }
        }

        // Remove all successfully processed items from wishlist
        $delete_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id IN ($placeholders)";
        $delete_stmt = $db->prepare($delete_sql);
        $params = array_merge([$user_id], $product_ids);
        $delete_stmt->execute($params);

        $response['success'] = true;
        $response['message'] = $added_count . ' item(s) moved to your cart.';
        $response['cartCount'] = getCartTotalItems($db, $user_id);
    }

    $db->commit();

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Bulk Wishlist Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);