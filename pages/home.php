<?php
// pages/home.php - ACCESS CONTROL
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to landing page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../landing.php');
    exit();
}

// Include config
require_once '../includes/config.php';

// Get featured products
$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, v.business_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          WHERE p.is_featured = 1 AND p.status = 'active'
          ORDER BY p.created_at DESC 
          LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get new arrivals
$query = "SELECT p.*, v.business_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          WHERE p.status = 'active'
          ORDER BY p.created_at DESC 
          LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <?php
        if (isset($_SESSION['welcome_message'])) {
            // New registration
            echo '<h1>' . htmlspecialchars($_SESSION['welcome_message']) . '</h1>';
            unset($_SESSION['welcome_message']);
        } else {
            // Regular login
            echo '<h1>Welcome back, ' . htmlspecialchars($username ?? $_SESSION['username'] ?? 'User') . '!</h1>';
        }
        ?>
        <p>Ready to continue shopping?</p>
        <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn">Shop Now</a>
        <?php if(isset($userType) ? strtolower($userType) === 'customer' : (isset($_SESSION['user_type']) && strtolower($_SESSION['user_type']) === 'customer')): ?>
            <a href="<?php echo BASE_URL; ?>pages/cart.php" class="btn btn-outline">View Cart</a>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Products -->
<section class="container">
    <h2 class="section-title">Featured Products</h2>
    <div class="products-grid">
        <?php foreach($featured_products as $product): ?>
            <div class="product-card">
                <img src="../assets/images/products/<?php echo !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                <div class="product-info">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-title"><?php echo $product['name']; ?></a>
                    <div class="product-price">
                        $<?php echo $product['price']; ?>
                        <?php if($product['compare_price']): ?>
                            <span class="product-old-price">$<?php echo $product['compare_price']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <?php
                        $rating = rand(3, 5);
                        for($i = 0; $i < 5; $i++) {
                            if($i < $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                        <span>(<?php echo rand(10, 100); ?>)</span>
                    </div>
                    <div class="product-vendor">Sold by: <?php echo $product['business_name']; ?></div>
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn product-btn">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- New Arrivals -->
<section class="container">
    <h2 class="section-title">New Arrivals</h2>
    <div class="products-grid">
        <?php foreach($new_products as $product): ?>
            <div class="product-card">
                <img src="../assets/images/products/<?php echo !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                <div class="product-info">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-title"><?php echo $product['name']; ?></a>
                    <div class="product-price">
                        $<?php echo $product['price']; ?>
                        <?php if($product['compare_price']): ?>
                            <span class="product-old-price">$<?php echo $product['compare_price']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-rating">
                        <?php
                        $rating = rand(3, 5);
                        for($i = 0; $i < 5; $i++) {
                            if($i < $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                        <span>(<?php echo rand(10, 100); ?>)</span>
                    </div>
                    <div class="product-vendor">Sold by: <?php echo $product['business_name']; ?></div>
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn product-btn">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Categories Section -->
<section class="container">
    <h2 class="section-title">Shop by Category</h2>
    <div class="products-grid">
        <?php
        $query = "SELECT id, name, slug, image FROM categories WHERE parent_id IS NULL ORDER BY name LIMIT 4";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($categories as $category): 
        ?>
            <div class="product-card">
                <img src="../assets/images/categories/<?php echo !empty($category['image']) ? $category['image'] : 'default.jpg'; ?>" alt="<?php echo $category['name']; ?>" class="product-image">
                <div class="product-info">
                    <h3 class="product-title"><?php echo $category['name']; ?></h3>
                    <a href="products.php?category=<?php echo $category['slug']; ?>" class="btn product-btn">Browse</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php
// Include footer
require_once '../includes/footer.php';
?>