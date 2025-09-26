<?php
// ajax/get_product_details.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Product not found.'];
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    $response['message'] = 'Invalid product ID.';
    echo json_encode($response);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = "SELECT p.*, v.business_name, v.id as vendor_id, c.name as category_name
            FROM products p
            LEFT JOIN vendors v ON p.vendor_id = v.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id AND p.status = 'active'";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Sanitize and prepare data for JSON response
        $product['images'] = !empty($product['images']) ? explode(',', $product['images']) : ['default.jpg'];
        $product['description'] = nl2br(htmlspecialchars($product['description']));
        $product['price_formatted'] = money($product['price']);
        if (!empty($product['compare_price'])) {
            $product['compare_price_formatted'] = money($product['compare_price']);
        }

        $response['success'] = true;
        $response['product'] = $product;
        unset($response['message']);
    }

} catch (PDOException $e) {
    error_log("Quick View Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);