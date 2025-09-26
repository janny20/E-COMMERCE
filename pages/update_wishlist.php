<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.', 'action' => 'none'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to manage your wishlist.';
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

    // Check if the item is already in the wishlist
    $check_sql = "SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);

    if ($check_stmt->fetch()) {
        // Item exists, so remove it
        $delete_sql = "DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
        $delete_stmt = $db->prepare($delete_sql);
        if ($delete_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id])) {
            $response['success'] = true;
            $response['message'] = 'Removed from wishlist.';
            $response['action'] = 'removed';
        } else {
            $response['message'] = 'Failed to remove from wishlist.';
        }
    } else {
        // Item does not exist, so add it
        $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)";
        $insert_stmt = $db->prepare($insert_sql);
        if ($insert_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id])) {
            $response['success'] = true;
            $response['message'] = 'Added to wishlist!';
            $response['action'] = 'added';
        } else {
            $response['message'] = 'Failed to add to wishlist.';
        }
    }

} catch (PDOException $e) {
    error_log("Wishlist Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);