<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/login.php?redirect=wishlist.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$wishlist_products = [];
$db_error = '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch products from the user's wishlist
    $sql = "SELECT p.*, v.business_name
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN vendors v ON p.vendor_id = v.id
            WHERE w.user_id = :user_id AND p.status = 'active'
            ORDER BY w.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $wishlist_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Database error: " . htmlspecialchars($e->getMessage());
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/wishlist.css">

<main class="wishlist-page">
    <div class="container">
        <div class="wishlist-header">
            <h1>My Wishlist</h1>
            <p>You have <?php echo count($wishlist_products); ?> item(s) saved for later.</p>
        </div>
        
        <?php if ($db_error): ?>
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        <?php elseif (empty($wishlist_products)): ?>
            <div class="empty-wishlist">
                <div class="empty-wishlist-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h2>Your Wishlist is Empty</h2>
                <p>Looks like you haven't added any items yet. Add items you love to your wishlist to save them for later.</p>
                <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary">Discover Products</a>
            </div>
        <?php else: ?>
            <div class="wishlist-toolbar">
                <div class="bulk-actions">
                    <label for="select-all" class="checkbox-label" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="select-all">
                        Select All
                    </label>
                    <button class="btn btn-sm btn-outline" id="bulk-add-to-cart" disabled><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                    <button class="btn btn-sm btn-outline-danger" id="bulk-remove" disabled><i class="fas fa-trash"></i> Remove</button>
                </div>
                <div class="sort-options">
                    <label for="sort-wishlist">Sort by:</label>
                    <select id="sort-wishlist" class="form-select form-select-sm">
                        <option value="date-desc">Date Added (Newest)</option>
                        <option value="date-asc">Date Added (Oldest)</option>
                        <option value="price-asc">Price (Low to High)</option>
                        <option value="price-desc">Price (High to Low)</option>
                    </select>
                </div>
            </div>

            <div class="wishlist-items-container">
                <div id="wishlist-items">
                    <?php foreach ($wishlist_products as $product): ?>
                        <div class="wishlist-item" data-product-id="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>" data-date-added="<?php echo strtotime($product['created_at']); ?>">
                            <div class="item-select">
                                <input type="checkbox" class="item-checkbox" value="<?php echo $product['id']; ?>">
                            </div>
                            <a href="<?php echo BASE_URL; ?>pages/product-detail.php?id=<?php echo $product['id']; ?>" class="item-product">
                                <div class="product-image">
                                    <?php $img = !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>
                                    <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="product-details">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-vendor">Sold by: <?php echo htmlspecialchars($product['business_name']); ?></p>
                                </div>
                            </a>
                            <div class="item-status">
                                <?php if ($product['quantity'] > 0): ?>
                                    <span class="stock-status in-stock">In Stock</span>
                                <?php else: ?>
                                    <span class="stock-status out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="item-actions">
                                <button class="btn btn-sm btn-primary btn-move-to-cart" data-product-id="<?php echo $product['id']; ?>" title="Move to Cart" <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button class="btn-icon remove-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>" title="Remove from Wishlist"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Page-specific JavaScript -->
<script src="<?php echo BASE_URL; ?>assets/js/pages/wishlist.js"></script>

<?php
require_once '../includes/footer.php';
?>