<?php
<<<<<<< HEAD
// vendor/add-product.php
// Start session only if not already started
=======
// vendor/products.php - List all products for the vendor
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../pages/login.php');
    exit();
}

// Include config
require_once '../includes/config.php';
=======
require_once '../includes/config.php';
require_once '../includes/middleware.php';
requireVendor();
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b

// Get vendor ID
$database = new Database();
$db = $database->getConnection();

<<<<<<< HEAD
$query = "SELECT id FROM vendors WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);
if ($vendor && isset($vendor['id'])) {
    $vendor_id = $vendor['id'];
} else {
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
=======
$vendor_id = $_SESSION['vendor_id'];

// Get filter values from URL
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // Products per page
$offset = ($page - 1) * $limit;

// Fetch all products for this vendor
$products_sql = "SELECT SQL_CALC_FOUND_ROWS p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.vendor_id = :vendor_id";

$params = ['vendor_id' => $vendor_id];

if (!empty($search_query)) {
    $products_sql .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
    $params[':search'] = "%$search_query%";
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $products_sql .= " AND p.status = :status";
    $params[':status'] = $status_filter;
}

$products_sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

$products_stmt = $db->prepare($products_sql);
$products_stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$products_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$products_stmt->execute($params);
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_count = $db->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_pages = ceil($total_count / $limit);

// Handle success and error messages from URL
$success_message = '';
$error_message = '';
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_key = $_GET['error'];
    $error_messages = [
        'permission_denied' => 'You do not have permission to perform that action.',
        'delete_failed' => 'Failed to delete the product. It might be part of an existing order.',
        'not_found' => 'Product not found.',
        'duplicate_failed' => 'Failed to duplicate the product. Please try again.',
        'invalid_id' => 'Invalid product ID specified.'
    ];
    $error_message = $error_messages[$error_key] ?? 'An unknown error occurred.';
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
}

// Include header
require_once '../includes/header.php';
?>

<<<<<<< HEAD
<div class="container vendor-add-product">
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
=======
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-products-list.css">

<div class="vendor-dashboard container">
    <div class="dashboard-header">
        <h1>My Products</h1>
        <a href="<?php echo BASE_URL; ?>vendor/add-product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-error" style="margin-bottom: 1rem;"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="content-card" style="margin-bottom: 1rem;">
        <form method="GET" action="" class="filter-form">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="search-query">Search by Name/SKU</label>
                    <input type="text" id="search-query" name="q" class="form-control" placeholder="e.g., T-Shirt or SKU123" value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="filter-group">
                    <label for="status-filter">Filter by Status</label>
                    <select id="status-filter" name="status" class="form-control">
                        <option value="all" <?php echo $status_filter === 'all' || $status_filter === '' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="products.php" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="7" style="text-align:center;">You have not added any products yet.</td></tr>
                    <?php else: foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="product-info-cell">
                                    <?php $img = !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>
                                    <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-cell-image">
                                    <span><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                            <td>$<?php echo money($product['price']); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td><span class="status-badge status-<?php echo strtolower($product['status']); ?>"><?php echo ucfirst($product['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?php echo BASE_URL; ?>pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline" title="View Public Page" target="_blank"><i class="fas fa-eye"></i></a>
                                    <a href="duplicate-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline" title="Duplicate" onclick="return confirm('Are you sure you want to duplicate this product? A new draft will be created.');"><i class="fas fa-copy"></i></a>
                                    <a href="delete-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to permanently delete this product? This action cannot be undone.');"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-summary">
                Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_count); ?> of <?php echo $total_count; ?> products
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo getPageLink($page - 1); ?>" class="pagination-item">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <a href="<?php echo getPageLink($i); ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <span class="pagination-item disabled">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo getPageLink($page + 1); ?>" class="pagination-item">&raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper function to generate pagination links
function getPageLink($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
// Include footer
require_once '../includes/footer.php';
?>