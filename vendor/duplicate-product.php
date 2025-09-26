<?php
// vendor/duplicate-product.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/middleware.php';
requireVendor();

$database = new Database();
$db = $database->getConnection();

$vendor_id = $_SESSION['vendor_id'];
$product_id_to_duplicate = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id_to_duplicate) {
    header('Location: products.php?error=invalid_id');
    exit();
}

try {
    $db->beginTransaction();

    // 1. Fetch the original product, ensuring it belongs to the vendor
    $original_product_sql = "SELECT * FROM products WHERE id = :id AND vendor_id = :vendor_id";
    $original_product_stmt = $db->prepare($original_product_sql);
    $original_product_stmt->execute(['id' => $product_id_to_duplicate, 'vendor_id' => $vendor_id]);
    $original_product = $original_product_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$original_product) {
        throw new Exception('permission_denied');
    }

    // 2. Duplicate images
    $new_image_names = [];
    if (!empty($original_product['images'])) {
        $original_images = explode(',', $original_product['images']);
        foreach ($original_images as $original_image) {
            $original_image = trim($original_image);
            $source_path = PRODUCT_UPLOAD_PATH . $original_image;
            if (file_exists($source_path) && is_file($source_path)) {
                $extension = pathinfo($original_image, PATHINFO_EXTENSION);
                $new_filename = bin2hex(random_bytes(16)) . '.' . $extension;
                $destination_path = PRODUCT_UPLOAD_PATH . $new_filename;
                if (copy($source_path, $destination_path)) {
                    $new_image_names[] = $new_filename;
                }
            }
        }
    }
    $new_images_string = implode(',', $new_image_names);

    // 3. Prepare new product data
    $new_name = '[COPY] ' . $original_product['name'];
    $new_slug = generateSlug($new_name) . '-' . uniqid();

    // 4. Insert the new (duplicated) product as a draft
    $insert_sql = "INSERT INTO products (vendor_id, name, slug, description, price, compare_price, category_id, quantity, sku, images, status, is_featured, created_at, updated_at) VALUES (:vendor_id, :name, :slug, :description, :price, :compare_price, :category_id, :quantity, :sku, :images, 'draft', 0, NOW(), NOW())";
    
    $insert_stmt = $db->prepare($insert_sql);
    $insert_stmt->execute([
        ':vendor_id' => $vendor_id,
        ':name' => $new_name,
        ':slug' => $new_slug,
        ':description' => $original_product['description'],
        ':price' => $original_product['price'],
        ':compare_price' => $original_product['compare_price'],
        ':category_id' => $original_product['category_id'],
        ':quantity' => $original_product['quantity'],
        ':sku' => $original_product['sku'],
        ':images' => $new_images_string,
    ]);

    $db->commit();
    header('Location: products.php?success=Product+duplicated+successfully.+It+has+been+saved+as+a+draft.');
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    $error_key = ($e->getMessage() === 'permission_denied') ? 'permission_denied' : 'duplicate_failed';
    error_log("Product Duplication Error: " . $e->getMessage());
    header('Location: products.php?error=' . $error_key);
    exit();
}