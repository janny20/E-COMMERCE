<?php
// vendor/delete-product.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/middleware.php';
requireVendor();

$database = new Database();
$db = $database->getConnection();

$vendor_id = $_SESSION['vendor_id'];
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    header('Location: products.php?error=invalid_id');
    exit();
}

try {
    // First, fetch the product to get image filenames and verify ownership
    $product_sql = "SELECT images FROM products WHERE id = :id AND vendor_id = :vendor_id";
    $product_stmt = $db->prepare($product_sql);
    $product_stmt->execute(['id' => $product_id, 'vendor_id' => $vendor_id]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        // Product not found or doesn't belong to the vendor
        header('Location: products.php?error=permission_denied');
        exit();
    }

    // Product exists and belongs to the vendor, proceed with deletion
    $delete_sql = "DELETE FROM products WHERE id = :id AND vendor_id = :vendor_id";
    $delete_stmt = $db->prepare($delete_sql);
    
    if ($delete_stmt->execute(['id' => $product_id, 'vendor_id' => $vendor_id])) {
        // Deletion from DB was successful, now delete associated images
        if (!empty($product['images'])) {
            $images_to_delete = explode(',', $product['images']);
            foreach ($images_to_delete as $image) {
                $image_path = PRODUCT_UPLOAD_PATH . trim($image);
                if (file_exists($image_path) && is_file($image_path)) {
                    unlink($image_path);
                }
            }
        }
        header('Location: products.php?success=Product+deleted+successfully.');
        exit();
    } else {
        header('Location: products.php?error=delete_failed');
        exit();
    }

} catch (PDOException $e) {
    // Catch potential foreign key constraint violations (e.g., product is in an order)
    if ($e->getCode() == '23000') {
        header('Location: products.php?error=delete_failed');
        exit();
    }
    error_log("Product Deletion Error: " . $e->getMessage());
    header('Location: products.php?error=db_error');
    exit();
}