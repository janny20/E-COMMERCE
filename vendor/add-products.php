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

$query = "SELECT id FROM vendors WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);
$vendor_id = $vendor['id'];

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
    $compare_price = floatval($_POST['compare_price']);
    $quantity = intval($_POST['quantity']);
    $category_id = intval($_POST['category_id']);
    $sku = trim($_POST['sku']);
    $status = $_POST['status'];
    
    $query = "INSERT INTO products SET 
              name = :name,
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
        header("Location: products.php?success=Product added successfully");
        exit();
    } else {
        $error = "Failed to add product. Please try again.";
    }
}

// Include header
require_once '../includes/header.php';
?>

<div class="vendor-add-product">
    <div class="container">
        <div class="page-header">
            <h1>Add New Product</h1>
            <a href="<?php echo BASE_URL; ?>vendor/products.php" class="btn btn-outline">← Back to Products</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="product-form" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="4" required></textarea>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="form-section">
                    <h3>Pricing</h3>
                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Compare Price ($)</label>
                        <input type="number" name="compare_price" step="0.01">
                    </div>
                </div>

                <!-- Inventory -->
                <div class="form-section">
                    <h3>Inventory</h3>
                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku">
                    </div>
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" required>
                    </div>
                </div>

                <!-- Organization -->
                <div class="form-section">
                    <h3>Organization</h3>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending Review</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <button type="reset" class="btn btn-outline">Reset</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>