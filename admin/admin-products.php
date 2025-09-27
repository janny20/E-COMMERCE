<?php
// admin-products.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Set page title
$page_title = "Products Management";

// Handle product actions (delete, toggle status)
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['token'])) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        die('Invalid CSRF token');
    }

    $product_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        
        $success = "Product deleted successfully.";
    } elseif ($action == 'toggle_status') {
        // Get current status
        $query = "SELECT status FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $new_status = $product['status'] == 'active' ? 'inactive' : 'active';
        
        $query = "UPDATE products SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        
        $success = "Product status updated successfully.";
    }
}

// Get all products with vendor information
$query = "SELECT p.*, v.business_name, c.name as category_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header
include_once '../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/pages/admin-users.css">
<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>Products Management</h1>
        <p>Manage all products in the marketplace</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>All Products</h2>
            <a href="admin-product-add.php" class="btn btn-primary">Add New Product</a>
        </div>
        <div class="card-body">
            <?php if (!empty($products)): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Vendor</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($product['images'])): 
                                            $images = explode(',', $product['images']);
                                        ?>
                                            <img src="../../assets/images/products/<?php echo $images[0]; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumb">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <div class="text-muted">SKU: <?php echo $product['sku'] ?: 'N/A'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['quantity']; ?></td>
                                <td>
                                    <span class="user-status <?php echo $product['status']; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin-product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-edit btn-sm">Edit</a>
                                        <a href="admin-products.php?action=toggle_status&id=<?php echo $product['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-warning btn-sm">
                                            <?php echo $product['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="admin-products.php?action=delete&id=<?php echo $product['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>