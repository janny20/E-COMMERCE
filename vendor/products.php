<?php
// vendor/products.php
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

// Get products
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.vendor_id = :vendor_id 
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';
?>

<div class="vendor-products">
    <div class="container">
        <div class="products-header">
            <h1 class="products-title">Manage Products</h1>
            <a href="add-product.php" class="add-product-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <div class="products-toolbar">
            <form class="search-form">
                <input type="text" class="search-input" placeholder="Search products...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            
            <select class="filter-select">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending">Pending</option>
            </select>
        </div>

        <div class="products-table">
            <div class="table-header">
                <div>Image</div>
                <div>Product Name</div>
                <div>Price</div>
                <div>Stock</div>
                <div>Category</div>
                <div>Status</div>
                <div>Actions</div>
            </div>

            <?php foreach ($products as $product): ?>
            <div class="table-row">
                <div>
                    <img src="<?php echo BASE_URL; ?>assets/images/products/<?php echo !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                </div>
                <div><?php echo htmlspecialchars($product['name']); ?></div>
                <div>$<?php echo number_format($product['price'], 2); ?></div>
                <div><?php echo $product['quantity']; ?></div>
                <div><?php echo htmlspecialchars($product['category_name']); ?></div>
                <div>
                    <span class="status-badge status-<?php echo $product['status']; ?>">
                        <?php echo ucfirst($product['status']); ?>
                    </span>
                </div>
                <div class="action-buttons">
                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <button class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <a href="#" class="pagination-item disabled">&laquo; Previous</a>
            <a href="#" class="pagination-item active">1</a>
            <a href="#" class="pagination-item">2</a>
            <a href="#" class="pagination-item">3</a>
            <a href="#" class="pagination-item">Next &raquo;</a>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>