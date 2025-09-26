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
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id) {
    $response['message'] = 'Invalid product specified.';
    echo json_encode($response);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();

    // 1. Add to cart (or update quantity)
    $cart_check_sql = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $cart_check_stmt = $db->prepare($cart_check_sql);
    $cart_check_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    $cart_item = $cart_check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        $new_quantity = $cart_item['quantity'] + 1;
        $cart_update_sql = "UPDATE cart SET quantity = :quantity WHERE id = :id";
        $cart_update_stmt = $db->prepare($cart_update_sql);
        $cart_update_stmt->execute(['quantity' => $new_quantity, 'id' => $cart_item['id']]);
    } else {
        $cart_insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)";
        $cart_insert_stmt = $db->prepare($cart_insert_sql);
        $cart_insert_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    }

    // 2. Remove from wishlist
    $wishlist_delete_sql = "DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
    $wishlist_delete_stmt = $db->prepare($wishlist_delete_sql);
    $wishlist_delete_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);

    // 3. Get updated cart count
    $count_sql = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute(['user_id' => $user_id]);
    $response['cartCount'] = $count_stmt->fetchColumn();

    $db->commit();
    $response['success'] = true;
    $response['message'] = 'Item moved to cart!';

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log("Move to Cart Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);