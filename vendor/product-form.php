0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$vendor_id = $_SESSION['vendor_id']; // Assuming vendor_id is stored in session
$db_error = '';
$product = null;
$is_edit = false;
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page_title = "Add New Product";
$existing_images = [];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch categories for dropdown
    $categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
    $categories_stmt = $db->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($product_id) {
        $is_edit = true;
        $page_title = "Edit Product";

        $product_query = "SELECT * FROM products WHERE id = :product_id AND vendor_id = :vendor_id";
        $product_stmt = $db->prepare($product_query);
        $product_stmt->execute(['product_id' => $product_id, 'vendor_id' => $vendor_id]);
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $_SESSION['error_message'] = "Product not found or you don't have permission to edit it.";
            header('Location: ' . BASE_URL . 'vendor/products.php');
            exit();
        }
        $existing_images = !empty($product['images']) ? explode(',', $product['images']) : [];
    }

} catch (PDOException $e) {
    $db_error = "Database error: " . htmlspecialchars($e->getMessage());
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-product-form.css">

<div class="product-form-page">
    <div class="container">
        <div class="form-header">
            <h1><?php echo $page_title; ?></h1>
            <p><?php echo $is_edit ? 'Update your product details and images.' : 'Add a new product to your store.'; ?></p>
        </div>

        <?php if ($db_error): ?>
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="product-form-container">
            <form id="product-form" action="<?php echo BASE_URL; ?>ajax/vendor_product_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'add'; ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <?php endif; ?>

                <div class="form-section">
                    <h2 class="form-section-title">Product Information</h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select a Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($product['category_id']) && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="price">Price *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="compare_price">Compare Price (Optional)</label>
                            <input type="number" id="compare_price" name="compare_price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['compare_price'] ?? ''); ?>">
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Stock Quantity *</label>
                            <input type="number" id="quantity" name="quantity" min="0" value="<?php echo htmlspecialchars($product['quantity'] ?? ''); ?>" required>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="draft" <?php echo (isset($product['status']) && $product['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="active" <?php echo (isset($product['status']) && $product['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($product['status']) && $product['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group full-width">
                            <label for="short_description">Short Description *</label>
                            <textarea id="short_description" name="short_description" rows="3" required><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></textarea>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Full Description</label>
                            <textarea id="description" name="description" rows="6"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group full-width">
                            <label for="specifications">Specifications</label>
                            <textarea id="specifications" name="specifications" rows="4" placeholder="e.g., Color: Red, Material: Cotton, Size: M, L, XL"><?php echo htmlspecialchars($product['specifications'] ?? ''); ?></textarea>
                            <div class="error-message"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">Product Images</h2>
                    <div class="form-group full-width">
                        <label for="product-images-input" class="image-upload-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop images here or click to browse</p>
                        </label>
                        <input type="file" id="product-images-input" name="images[]" accept="image/*" multiple>
                        <div class="error-message"></div>
                    </div>
                    <div class="image-previews" id="image-previews">
                        <!-- Image previews will be rendered here by JavaScript -->
                    </div>
                    <input type="hidden" id="existing-images-data" name="existing_images_data" value='<?php echo json_encode($existing_images); ?>'>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <a href="<?php echo BASE_URL; ?>vendor/products.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/pages/vendor-product-form.js"></script>

<?php
require_once '../includes/footer.php';
?>