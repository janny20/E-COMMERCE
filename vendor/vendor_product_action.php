<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    $response['message'] = 'Access denied. Please log in as a vendor.';
    $response['require_login'] = true;
    echo json_encode($response);
    exit();
}

$vendor_id = $_SESSION['vendor_id'];
$action = $_POST['action'] ?? '';

$database = new Database();
$db = $database->getConnection();

try {
    switch ($action) {
        case 'add':
        case 'update':
            $product_id = $_POST['product_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $compare_price = (float)($_POST['compare_price'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            $status = trim($_POST['status'] ?? 'draft');
            $short_description = trim($_POST['short_description'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $specifications = trim($_POST['specifications'] ?? '');

            // Basic validation
            if (empty($name) || empty($category_id) || $price <= 0 || $quantity < 0 || empty($short_description)) {
                throw new Exception('Please fill all required product fields correctly.');
            }
            if (!in_array($status, ['active', 'inactive', 'draft'])) {
                throw new Exception('Invalid product status.');
            }

            $current_images = [];
            if ($action === 'update') {
                // Fetch existing product images
                $existing_product_query = "SELECT images FROM products WHERE id = :product_id AND vendor_id = :vendor_id";
                $existing_product_stmt = $db->prepare($existing_product_query);
                $existing_product_stmt->execute(['product_id' => $product_id, 'vendor_id' => $vendor_id]);
                $existing_product = $existing_product_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing_product) {
                    throw new Exception('Product not found or access denied.');
                }
                $current_images = !empty($existing_product['images']) ? explode(',', $existing_product['images']) : [];

                // Handle images removed from the form
                $images_from_form = json_decode($_POST['existing_images_data'] ?? '[]', true);
                $images_to_delete = array_diff($current_images, $images_from_form);

                foreach ($images_to_delete as $img_name) {
                    $file_path = PRODUCT_UPLOAD_PATH . $img_name;
                    if (file_exists($file_path) && is_file($file_path)) {
                        unlink($file_path);
                    }
                }
                $current_images = $images_from_form; // Update current images to only those remaining in the form
            }

            // Handle new image uploads
            $new_uploaded_images = [];
            if (isset($_FILES['new_images'])) {
                foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['new_images']['name'][$key],
                            'type' => $_FILES['new_images']['type'][$key],
                            'tmp_name' => $tmp_name,
                            'error' => $_FILES['new_images']['error'][$key],
                            'size' => $_FILES['new_images']['size'][$key],
                        ];
                        $upload_result = uploadFile($file, PRODUCT_UPLOAD_PATH);
                        if ($upload_result['success']) {
                            $new_uploaded_images[] = $upload_result['file_name'];
                        } else {
                            // Log error but continue with other images
                            error_log("Product image upload failed: " . $upload_result['message']);
                        }
                    }
                }
            }

            $final_images = array_merge($current_images, $new_uploaded_images);
            $images_string = implode(',', array_filter($final_images)); // Filter out any empty strings

            if ($action === 'add') {
                $insert_query = "INSERT INTO products (vendor_id, category_id, name, short_description, description, specifications, price, compare_price, quantity, images, status, created_at, updated_at)
                                 VALUES (:vendor_id, :category_id, :name, :short_description, :description, :specifications, :price, :compare_price, :quantity, :images, :status, NOW(), NOW())";
                $stmt = $db->prepare($insert_query);
                $stmt->execute([
                    'vendor_id' => $vendor_id,
                    'category_id' => $category_id,
                    'name' => $name,
                    'short_description' => $short_description,
                    'description' => $description,
                    'specifications' => $specifications,
                    'price' => $price,
                    'compare_price' => $compare_price,
                    'quantity' => $quantity,
                    'images' => $images_string,
                    'status' => $status
                ]);
                $response['success'] = true;
                $response['message'] = 'Product added successfully!';
                $response['redirect'] = BASE_URL . 'vendor/products.php';
            } elseif ($action === 'update') {
                $update_query = "UPDATE products SET
                                 category_id = :category_id, name = :name, short_description = :short_description,
                                 description = :description, specifications = :specifications, price = :price,
                                 compare_price = :compare_price, quantity = :quantity, images = :images,
                                 status = :status, updated_at = NOW()
                                 WHERE id = :product_id AND vendor_id = :vendor_id";
                $stmt = $db->prepare($update_query);
                $stmt->execute([
                    'category_id' => $category_id,
                    'name' => $name,
                    'short_description' => $short_description,
                    'description' => $description,
                    'specifications' => $specifications,
                    'price' => $price,
                    'compare_price' => $compare_price,
                    'quantity' => $quantity,
                    'images' => $images_string,
                    'status' => $status,
                    'product_id' => $product_id,
                    'vendor_id' => $vendor_id
                ]);
                $response['success'] = true;
                $response['message'] = 'Product updated successfully!';
                $response['updated_images'] = array_filter($final_images); // Send back updated image list for JS to re-render
            }
            break;

        case 'delete':
            $product_id = $_POST['product_id'] ?? null;
            if (!$product_id) {
                throw new Exception('Product ID is required for deletion.');
            }

            // Fetch product images before deleting the product record
            $get_images_query = "SELECT images FROM products WHERE id = :product_id AND vendor_id = :vendor_id";
            $get_images_stmt = $db->prepare($get_images_query);
            $get_images_stmt->execute(['product_id' => $product_id, 'vendor_id' => $vendor_id]);
            $product_images_string = $get_images_stmt->fetchColumn();
            $product_images = !empty($product_images_string) ? explode(',', $product_images_string) : [];

            $delete_query = "DELETE FROM products WHERE id = :product_id AND vendor_id = :vendor_id";
            $stmt = $db->prepare($delete_query);
            $stmt->execute(['product_id' => $product_id, 'vendor_id' => $vendor_id]);

            if ($stmt->rowCount() > 0) {
                // Delete associated image files
                foreach ($product_images as $img_name) {
                    $file_path = PRODUCT_UPLOAD_PATH . $img_name;
                    if (file_exists($file_path) && is_file($file_path)) {
                        unlink($file_path);
                    }
                }
                $response['success'] = true;
                $response['message'] = 'Product deleted successfully!';
            } else {
                throw new Exception('Product not found or access denied.');
            }
            break;

        default:
            throw new Exception('Invalid action specified.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

?>