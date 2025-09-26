<?php
// vendor/add-product.php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../pages/login.php');
    exit();
}

// Include config
require_once '../includes/config.php';

// Get vendor ID
$database = new Database();
$db = $database->getConnection();

$vendor_id = $_SESSION['vendor_id'] ?? null;
if (!$vendor_id) {
    echo '<div class="alert alert-danger">Vendor account not found. Please contact support.</div>';
    exit();
}

// Get categories
$query = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $compare_price = !empty($_POST['compare_price']) ? floatval($_POST['compare_price']) : null;
    $quantity = intval($_POST['quantity']);
    $category_id = intval($_POST['category_id']);
    $sku = trim($_POST['sku']);
    $status = $_POST['status'];
    $images = $_FILES['images'] ?? null;
    
    $query = "INSERT INTO products SET 
              name = :name,
              slug = :slug,
              description = :description,
              price = :price,
              compare_price = :compare_price,
              quantity = :quantity,
              category_id = :category_id,
              vendor_id = :vendor_id,
              sku = :sku,
              status = :status,
              created_at = NOW()";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindValue(':slug', generateSlug($name) . '-' . uniqid()); // Ensure slug is unique
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':compare_price', $compare_price);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':vendor_id', $vendor_id);
    $stmt->bindParam(':sku', $sku);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        $product_id = $db->lastInsertId();
        $uploaded_filenames = [];

        // Handle image uploads
        if ($images && !empty($images['name'][0])) {
            $file_count = count($images['name']);
            for ($i = 0; $i < $file_count; $i++) {
                // Re-structure the $_FILES array for uploadFile function
                $file = [
                    'name' => $images['name'][$i],
                    'type' => $images['type'][$i],
                    'tmp_name' => $images['tmp_name'][$i],
                    'error' => $images['error'][$i],
                    'size' => $images['size'][$i]
                ];
                $upload_result = uploadFile($file, PRODUCT_UPLOAD_PATH);
                if ($upload_result['success']) {
                    $uploaded_filenames[] = $upload_result['file_name'];
                }
            }
        }

        // Update product with image filenames
        $image_string = implode(',', $uploaded_filenames);
        $db->prepare("UPDATE products SET images = :images WHERE id = :id")->execute(['images' => $image_string, 'id' => $product_id]);
        
        header("Location: products.php?success=Product added successfully");
        exit();
    } else {
        $error = "Failed to add product. Please try again.";
    }
}

// Include header
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-product-form.css">

<div class="vendor-dashboard container">
    <div class="dashboard-header">
        <h1>Add New Product</h1>
        <a href="<?php echo BASE_URL; ?>vendor/products.php" class="btn btn-outline">← Back to Products</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="content-card" enctype="multipart/form-data" id="product-form">
        <div class="form-section">
            <h3>Basic Information</h3>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="4" class="form-control" required></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Product Images</h3>
            <div class="form-group">
                <label for="images">Upload Images (up to 5)</label>
                <input type="file" id="images" name="images[]" class="form-control" multiple accept="image/*">
                <div class="image-preview-container" id="image-preview-container">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Pricing</h3>
            <div class="form-group">
                <label>Price ($) *</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Compare Price ($)</label>
                <input type="number" name="compare_price" step="0.01" class="form-control">
            </div>
        </div>

        <div class="form-section">
            <h3>Inventory & Organization</h3>
            <div class="form-group">
                <label>SKU</label>
                <input type="text" name="sku" class="form-control">
            </div>
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Product</button>
            <button type="reset" class="btn btn-outline">Reset</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('image-preview-container');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            previewContainer.innerHTML = ''; // Clear previous previews
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    if (!file.type.startsWith('image/')){ return; }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const previewWrapper = document.createElement('div');
                        previewWrapper.className = 'image-preview-wrapper';

                        const img = document.createElement('img');
                        img.src = event.target.result;
                        
                        previewWrapper.appendChild(img);
                        previewContainer.appendChild(previewWrapper);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>