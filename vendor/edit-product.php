<?php
// vendor/edit-product.php
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
    header('Location: products.php');
    exit();
}

// Fetch the product to edit, ensuring it belongs to the vendor
$product_sql = "SELECT * FROM products WHERE id = :id AND vendor_id = :vendor_id";
$product_stmt = $db->prepare($product_sql);
$product_stmt->execute(['id' => $product_id, 'vendor_id' => $vendor_id]);
$product = $product_stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // Product not found or doesn't belong to the vendor
    header('Location: products.php?error=not_found');
    exit();
}

// Get categories for the dropdown
$categories_query = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submission for updating the product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $compare_price = !empty($_POST['compare_price']) ? floatval($_POST['compare_price']) : null;
    $quantity = intval($_POST['quantity']);
    $category_id = intval($_POST['category_id']);
    $sku = trim($_POST['sku']);
    $status = $_POST['status'];
    $images_to_delete = $_POST['delete_images'] ?? [];
    $new_images = $_FILES['new_images'] ?? null;
    
    $update_query = "UPDATE products SET 
                      name = :name,
                      slug = :slug,
                      description = :description,
                      price = :price,
                      compare_price = :compare_price,
                      quantity = :quantity,
                      category_id = :category_id,
                      sku = :sku,
                      status = :status,
                      updated_at = NOW()
                    WHERE id = :id AND vendor_id = :vendor_id";
    
    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':name', $name);
    $stmt->bindValue(':slug', generateSlug($name) . '-' . $product_id); // Keep slug consistent
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':compare_price', $compare_price);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':status', $status);

    // --- Image Handling ---
    $current_images = !empty($product['images']) ? explode(',', $product['images']) : [];

    // 1. Handle deletions
    foreach ($images_to_delete as $image_to_delete) {
        if (($key = array_search($image_to_delete, $current_images)) !== false) {
            unset($current_images[$key]);
            if (file_exists(PRODUCT_UPLOAD_PATH . $image_to_delete)) {
                unlink(PRODUCT_UPLOAD_PATH . $image_to_delete);
            }
        }
    }

    // 2. Handle new uploads
    if ($new_images && !empty($new_images['name'][0])) {
        $file_count = count($new_images['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $file = ['name' => $new_images['name'][$i], 'type' => $new_images['type'][$i], 'tmp_name' => $new_images['tmp_name'][$i], 'error' => $new_images['error'][$i], 'size' => $new_images['size'][$i]];
            $upload_result = uploadFile($file, PRODUCT_UPLOAD_PATH);
            if ($upload_result['success']) {
                $current_images[] = $upload_result['file_name'];
            }
        }
    }

    $final_image_string = implode(',', array_values($current_images)); // Re-index array
    $stmt->bindParam(':images', $final_image_string);
    // --- End Image Handling ---

    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        header("Location: products.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Failed to update product. Please try again.";
    }
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-product-form.css">

<div class="vendor-dashboard container">
    <div class="dashboard-header">
        <h1>Edit Product</h1>
        <a href="<?php echo BASE_URL; ?>vendor/products.php" class="btn btn-outline">← Back to Products</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="content-card" enctype="multipart/form-data" id="product-form">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <div class="form-section">
            <h3>Basic Information</h3>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="4" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Manage Images</h3>
            <div class="form-group">
                <label>Current Images</label>
                <div class="image-preview-container" id="image-preview-container">
                    <?php 
                    $product_images = !empty($product['images']) ? explode(',', $product['images']) : [];
                    if (empty($product_images)):
                    ?>
                        <p>No images uploaded for this product.</p>
                    <?php else: foreach ($product_images as $image): ?>
                        <div class="image-preview-wrapper">
                            <img src="<?php echo BASE_URL . 'uploads/products/' . htmlspecialchars($image); ?>" alt="Product Image">
                            <button type="button" class="delete-image-btn" data-filename="<?php echo htmlspecialchars($image); ?>">&times;</button>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="new_images">Add New Images</label>
                <input type="file" id="new_images" name="new_images[]" class="form-control" multiple accept="image/*">
            </div>
        </div>

        <div class="form-section">
            <h3>Pricing</h3>
            <div class="form-group">
                <label>Price ($) *</label>
                <input type="number" name="price" step="0.01" class="form-control" required value="<?php echo htmlspecialchars($product['price']); ?>">
            </div>
            <div class="form-group">
                <label>Compare Price ($)</label>
                <input type="number" name="compare_price" step="0.01" class="form-control" value="<?php echo htmlspecialchars($product['compare_price'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-section">
            <h3>Inventory & Organization</h3>
            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="quantity" class="form-control" required value="<?php echo htmlspecialchars($product['quantity']); ?>">
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo ($product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="draft" <?php echo ($product['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?php echo BASE_URL; ?>vendor/products.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-form');
    const previewContainer = document.getElementById('image-preview-container');

    if (previewContainer) {
        previewContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-image-btn')) {
                e.preventDefault();
                const button = e.target;
                const filename = button.dataset.filename;
                const previewWrapper = button.parentElement;

                // Create a hidden input to mark this image for deletion on form submission
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'delete_images[]';
                hiddenInput.value = filename;
                form.appendChild(hiddenInput);

                // Remove the preview from the view
                previewWrapper.style.display = 'none';
            }
        });
    }
});
</script>

<?php
require_once '../includes/footer.php';
?>