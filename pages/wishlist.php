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

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/products.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/home.css"> <!-- For product card styles -->

<main class="wishlist-page products-page">
    <div class="container">
        <div class="products-header">
            <h1 class="products-title">My Wishlist</h1>
            <p class="products-meta">You have <?php echo count($wishlist_products); ?> item(s) in your wishlist.</p>
        </div>
        
        <?php if ($db_error): ?>
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        <?php elseif (empty($wishlist_products)): ?>
            <div class="products-empty">
                <div class="products-empty-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h3 class="products-empty-title">Your Wishlist is Empty</h3>
                <p class="products-empty-text">Add items you love to your wishlist to save them for later.</p>
                <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary">Discover Products</a>
            </div>
        <?php else: ?>
            <div class="products-main" style="width: 100%;">
                <div class="products-grid-view" id="products-view">
                    <div class="products-grid">
                        <?php 
                        $show_move_to_cart_button = true; // Flag for product card
                        foreach ($wishlist_products as $product): 
                        ?>
                            <?php include '../includes/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>