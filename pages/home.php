<?php
// pages/home.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to landing page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../landing.php');
    exit();
}

// robust pathing
require_once __DIR__ . '/../includes/config.php';

// helper for escaping
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Get first image from a comma-separated image list
 * Returns a fallback filename if none found.
 */
function first_image($images_string) {
    $fallback = 'default.jpg';
    if (empty($images_string)) return $fallback;
    $parts = array_filter(array_map('trim', explode(',', $images_string)));
    return count($parts) ? $parts[0] : $fallback;
}

$featured_products = [];
$new_products = [];
$categories = [];
$db_error = '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Featured products (limit 8)
    $sql = "SELECT p.*, v.business_name
            FROM products p
            LEFT JOIN vendors v ON p.vendor_id = v.id
            WHERE p.is_featured = 1
              AND p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT 8";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // New arrivals (limit 8) - exclude featured so we don't duplicate
    $sql = "SELECT p.*, v.business_name
            FROM products p
            LEFT JOIN vendors v ON p.vendor_id = v.id
            WHERE (p.is_featured IS NULL OR p.is_featured = 0)
              AND p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT 8";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top-level categories (show a few)
    $sql = "SELECT id, name, slug, image
            FROM categories
            WHERE parent_id IS NULL OR parent_id = 0
            ORDER BY name
            LIMIT 4";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    // DB failure — keep page graceful
    $db_error = "Database error: " . e($ex->getMessage());
}

// Include header (assumes header uses relative includes)
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <?php
<<<<<<< HEAD
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
=======
        if (!empty($_SESSION['welcome_message'])) {
            echo '<h1>' . e($_SESSION['welcome_message']) . '</h1>';
            unset($_SESSION['welcome_message']);
        } else {
            echo '<h1>Welcome back, ' . e($_SESSION['username'] ?? 'User') . '!</h1>';
        }
        ?>
        <p>Ready to continue shopping?</p>
        <a href="products.php" class="btn">Shop Now</a>

        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer'): ?>
            <a href="cart.php" class="btn btn-outline">View Cart</a>
>>>>>>> ebb47525a15ab02a6b62127f98182234ea4ee14f
        <?php endif; ?>

        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <!-- Admin Panel button removed as requested -->
        <?php endif; ?>
    </div>
</section>

<?php if ($db_error): ?>
    <div class="container">
        <div class="alert alert-error"><?php echo $db_error; ?></div>
    </div>
<?php endif; ?>

<!-- Featured Products -->
<section class="container">
    <h2 class="section-title">Featured Products</h2>

    <?php if (empty($featured_products)): ?>
        <p>No featured products at the moment.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <?php $img = first_image($product['images'] ?? ''); ?>
                    <img src="../assets/images/products/<?php echo e($img); ?>" alt="<?php echo e($product['name'] ?? 'Product'); ?>" class="product-image">
                    <div class="product-info">
                        <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-title"><?php echo e($product['name'] ?? 'Untitled'); ?></a>

                        <div class="product-price">
                            $<?php echo number_format((float)($product['price'] ?? 0), 2); ?>
                            <?php if (!empty($product['compare_price']) && is_numeric($product['compare_price'])): ?>
                                <span class="product-old-price">$<?php echo number_format((float)$product['compare_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="product-rating">
                            <?php
                            // If there's a stored rating use it, otherwise show "No ratings yet"
                            if (isset($product['rating']) && is_numeric($product['rating'])) {
                                $rating = (int)round($product['rating']);
                                for ($i = 0; $i < 5; $i++) {
                                    echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                $reviews = isset($product['review_count']) ? (int)$product['review_count'] : 0;
                                echo ' <span>(' . $reviews . ')</span>';
                            } else {
                                echo '<span class="no-rating">No ratings yet</span>';
                            }
                            ?>
                        </div>

                        <div class="product-vendor">Sold by: <?php echo e($product['business_name'] ?? 'Unknown'); ?></div>
                        <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="btn product-btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- New Arrivals -->
<section class="container">
    <h2 class="section-title">New Arrivals</h2>

    <?php if (empty($new_products)): ?>
        <p>No new arrivals right now.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($new_products as $product): ?>
                <div class="product-card">
                    <?php $img = first_image($product['images'] ?? ''); ?>
                    <img src="../assets/images/products/<?php echo e($img); ?>" alt="<?php echo e($product['name'] ?? 'Product'); ?>" class="product-image">
                    <div class="product-info">
                        <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-title"><?php echo e($product['name'] ?? 'Untitled'); ?></a>

                        <div class="product-price">
                            $<?php echo number_format((float)($product['price'] ?? 0), 2); ?>
                            <?php if (!empty($product['compare_price']) && is_numeric($product['compare_price'])): ?>
                                <span class="product-old-price">$<?php echo number_format((float)$product['compare_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="product-rating">
                            <?php
                            if (isset($product['rating']) && is_numeric($product['rating'])) {
                                $rating = (int)round($product['rating']);
                                for ($i = 0; $i < 5; $i++) {
                                    echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                $reviews = isset($product['review_count']) ? (int)$product['review_count'] : 0;
                                echo ' <span>(' . $reviews . ')</span>';
                            } else {
                                echo '<span class="no-rating">No ratings yet</span>';
                            }
                            ?>
                        </div>

                        <div class="product-vendor">Sold by: <?php echo e($product['business_name'] ?? 'Unknown'); ?></div>
                        <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="btn product-btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Categories Section -->
<section class="container">
    <h2 class="section-title">Shop by Category</h2>

    <?php if (empty($categories)): ?>
        <p>No categories found.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
                <div class="product-card">
                    <img src="../assets/images/categories/<?php echo e($category['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($category['name']); ?>" class="product-image">
                    <div class="product-info">
                        <h3 class="product-title"><?php echo e($category['name']); ?></h3>
                        <a href="products.php?category=<?php echo urlencode($category['slug']); ?>" class="btn product-btn">Browse</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
