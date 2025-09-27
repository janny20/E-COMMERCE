0,0 @@
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

<<<<<<< HEAD
// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
=======
// Get vendor ID
$database = new Database();
$db = $database->getConnection();

$vendor_id = $_SESSION['vendor_id'] ?? null;
if (!$vendor_id) {
    // This is a fallback in case the session is not set correctly.
    die("Error: Vendor ID not found in session. Please log out and log back in.");
}

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
>>>>>>> 03a6e3f92aa402ee81d8999f8d4d4eabc1c67615
}

$vendor_id = $_SESSION['vendor_id']; // Assuming vendor_id is stored in session
$db_error = '';
$products = [];
$total_products = 0;
$products_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// Filters
$search_term = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Build query for products
    $query = "SELECT p.*, c.name as category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.vendor_id = :vendor_id";
    $count_query = "SELECT COUNT(*) FROM products WHERE vendor_id = :vendor_id";

    $params = [':vendor_id' => $vendor_id];

    if (!empty($search_term)) {
        $query .= " AND p.name LIKE :search_term";
        $count_query .= " AND name LIKE :search_term";
        $params[':search_term'] = '%' . $search_term . '%';
    }

    if ($status_filter !== 'all') {
        $query .= " AND p.status = :status";
        $count_query .= " AND status = :status";
        $params[':status'] = $status_filter;
    }

    $query .= " ORDER BY p.created_at DESC LIMIT :offset, :limit";

    // Get total products for pagination
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        if ($key !== ':offset' && $key !== ':limit') {
            $count_stmt->bindValue($key, $value);
        }
    }
    $count_stmt->execute();
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $products_per_page);

    // Get products for current page
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Database error: " . htmlspecialchars($e->getMessage());
}

<<<<<<< HEAD
=======
$products_sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

$products_stmt = $db->prepare($products_sql);
$products_stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$products_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Bind the rest of the parameters from the $params array
foreach ($params as $key => &$val) {
    $products_stmt->bindParam($key, $val);
}

$products_stmt->execute();
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
}

// Include header
>>>>>>> 03a6e3f92aa402ee81d8999f8d4d4eabc1c67615
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-products.css">

<div class="vendor-products">
    <div class="container">
        <div class="products-header">
            <h1 class="products-title">My Products</h1>
            <a href="<?php echo BASE_URL; ?>vendor/product-form.php" class="btn add-product-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <?php if ($db_error): ?>
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        <?php else: ?>
            <div class="products-toolbar">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
                <div class="filter-options">
                    <label for="status-filter">Status:</label>
                    <select id="status-filter" name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-box-open"></i></div>
                    <h2>No Products Found</h2>
                    <p>It looks like you haven't added any products yet, or your search/filter returned no results.</p>
                    <a href="<?php echo BASE_URL; ?>vendor/product-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Your First Product</a>
                </div>
            <?php else: ?>
                <div class="products-table-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr data-product-id="<?php echo $product['id']; ?>">
                                    <td data-label="Image">
                                        <?php $img = !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>
                                        <img src="<?php echo BASE_URL . 'uploads/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image-thumb">
                                    </td>
                                    <td data-label="Product Name"><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td data-label="Category"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                    <td data-label="Price">$<?php echo number_format($product['price'], 2); ?></td>
                                    <td data-label="Stock"><?php echo $product['quantity']; ?></td>
                                    <td data-label="Status">
                                        <span class="status-badge status-<?php echo htmlspecialchars($product['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($product['status'])); ?>
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="action-buttons">
                                            <a href="<?php echo BASE_URL; ?>vendor/product-form.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="Edit Product"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn-icon delete-btn" data-product-id="<?php echo $product['id']; ?>" title="Delete Product"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo htmlspecialchars($search_term); ?>&status=<?php echo htmlspecialchars($status_filter); ?>" class="pagination-item">&laquo; Previous</a>
                    <?php else: ?>
                        <span class="pagination-item disabled">&laquo; Previous</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search_term); ?>&status=<?php echo htmlspecialchars($status_filter); ?>" class="pagination-item <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo htmlspecialchars($search_term); ?>&status=<?php echo htmlspecialchars($status_filter); ?>" class="pagination-item">Next &raquo;</a>
                    <?php else: ?>
                        <span class="pagination-item disabled">Next &raquo;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('product_id', productId);

                fetch('<?php echo BASE_URL; ?>ajax/vendor_product_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Remove the product row from the DOM
                        const productRow = document.querySelector(`tr[data-product-id="${productId}"]`);
                        if (productRow) {
                            productRow.remove();
                            // Optionally, reload or update pagination/counts
                            window.location.reload(); 
                        }
                    } else {
                        showNotification(data.message || 'Failed to delete product.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('A network error occurred.', 'error');
                });
            }
        });
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>