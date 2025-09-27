<?php
// vendor/products.php - List all products for the vendor
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/middleware.php';
requireVendor();

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
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $products_sql .= " AND p.status = :status";
    $params[':status'] = $status_filter;
}

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
require_once '../includes/header.php';
?>

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
// Include footer
require_once '../includes/footer.php';
?>