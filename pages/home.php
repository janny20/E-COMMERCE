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

<?php if (isset($userType) && strtolower($userType) === 'customer'): ?>
<nav class="main-nav customer-nav">
    <div class="container">
        <ul class="nav-menu">
            <li><a href="<?php echo BASE_URL; ?>pages/home.php">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a></li>
            <li class="dropdown">
                <button type="button" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false" aria-controls="category-dropdown">
                    Categories <i class="fas fa-chevron-down"></i>
                </button>
                <ul id="category-dropdown" class="dropdown-content">
                    <?php
                    foreach ($categories as $category) {
                        echo '<li><a href="' . BASE_URL . 'pages/products.php?category=' . e($category['slug']) . '">' . e($category['name']) . '</a></li>';
                    }
                    ?>
                </ul>
            </li>
            <li><a href="#">Today's Deals</a></li>
            <li><a href="<?php echo BASE_URL; ?>pages/login.php?type=vendor">Become a Vendor</a></li>
        </ul>
    </div>
</nav>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); text-align: center; padding: 60px 20px; border-bottom: 1px solid #dee2e6;">
    <div class="container">
        <?php
        if (!empty($_SESSION['welcome_message'])) {
            echo '<h1 style="font-size: 2.5rem; color: #343a40; margin-bottom: 10px;">' . e($_SESSION['welcome_message']) . '</h1>';
            unset($_SESSION['welcome_message']);
        } else {
            echo '<h1 style="font-size: 2.5rem; color: #343a40; margin-bottom: 10px;">Welcome back, ' . e($username ?? $_SESSION['username'] ?? 'User') . '!</h1>';
        }
        ?>
        <p style="font-size: 1.2rem; color: #6c757d; margin-bottom: 30px;">Discover amazing products and great deals just for you.</p>
        <div class="hero-actions" style="display: flex; justify-content: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary" style="padding: 12px 25px; font-size: 1.1rem;">Shop Now</a>
            <?php if(isset($userType) ? strtolower($userType) === 'customer' : (isset($_SESSION['user_type']) && strtolower($_SESSION['user_type']) === 'customer')): ?>
                <a href="<?php echo BASE_URL; ?>pages/cart.php" class="btn btn-outline" style="padding: 12px 25px; font-size: 1.1rem;">View Cart</a>
            <?php endif; ?>
        </div>
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
                    <div class="product-badge">Featured</div>
                    <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-image-container">
                        <?php $img = first_image($product['images'] ?? ''); ?>
                        <img src="../assets/images/products/<?php echo e($img); ?>" alt="<?php echo e($product['name'] ?? 'Product'); ?>" class="product-image" loading="lazy" decoding="async">
                    </a>
                    <div class="product-info">
                        <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-title-link">
                            <h3 class="product-title"><?php echo e($product['name'] ?? 'Untitled'); ?></h3>
                        </a>
                        <p class="product-vendor">Sold by: <?php echo e($product['business_name'] ?? 'Unknown'); ?></p>
                        <div class="product-price">
                            $<?php echo number_format((float)($product['price'] ?? 0), 2); ?>
                            <?php if (isset($product['compare_price']) && is_numeric($product['compare_price']) && $product['compare_price'] > 0): ?>
                                <span class="product-old-price">$<?php echo number_format((float)$product['compare_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="product-rating">
                            <?php
                            // If there's a stored rating use it, otherwise show "No ratings yet"
                            if (isset($product['rating']) && is_numeric($product['rating']) && $product['rating'] > 0) {
                                $rating = (int)round($product['rating']);
                                for ($i = 0; $i < 5; $i++) {
                                    echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                $reviews = (int)($product['review_count'] ?? 0);
                                echo ' <span>(' . $reviews . ')</span>';
                            } else {
                                echo '<span class="no-rating">No ratings yet</span>';
                            }
                            ?>
                        </div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-btn add-to-cart">View Details</a>
                            <button class="product-btn wishlist"><i class="far fa-heart"></i></button>
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
                    <div class="product-badge new">New</div>
                    <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-image-container">
                        <?php $img = first_image($product['images'] ?? ''); ?>
                        <img src="../assets/images/products/<?php echo e($img); ?>" alt="<?php echo e($product['name'] ?? 'Product'); ?>" class="product-image" loading="lazy" decoding="async">
                    </a>
                    <div class="product-info">
                        <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-title-link">
                            <h3 class="product-title"><?php echo e($product['name'] ?? 'Untitled'); ?></h3>
                        </a>
                        <p class="product-vendor">Sold by: <?php echo e($product['business_name'] ?? 'Unknown'); ?></p>
                        <div class="product-price">
                            $<?php echo number_format((float)($product['price'] ?? 0), 2); ?>
                            <?php if (isset($product['compare_price']) && is_numeric($product['compare_price']) && $product['compare_price'] > 0): ?>
                                <span class="product-old-price">$<?php echo number_format((float)$product['compare_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-rating">
                            <?php
                            if (isset($product['rating']) && is_numeric($product['rating']) && $product['rating'] > 0) {
                                $rating = (int)round($product['rating']);
                                for ($i = 0; $i < 5; $i++) {
                                    echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                $reviews = (int)($product['review_count'] ?? 0);
                                echo ' <span>(' . $reviews . ')</span>';
                            } else {
                                echo '<span class="no-rating">No ratings yet</span>';
                            }
                            ?>
                        </div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo (int)$product['id']; ?>" class="product-btn add-to-cart">View Details</a>
                            <button class="product-btn wishlist"><i class="far fa-heart"></i></button>
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
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="products.php?category=<?php echo urlencode($category['slug']); ?>" class="category-card">
                    <img src="../assets/images/categories/<?php echo e($category['image'] ?? 'default.jpg'); ?>" alt="<?php echo e($category['name']); ?>" class="category-card-bg" loading="lazy" decoding="async">
                    <div class="category-card-overlay"></div>
                    <div class="category-card-content">
                        <h3><?php echo e($category['name']); ?></h3>
                        <p>Shop Now</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<style>
    .modern-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .modern-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
    .section-title { text-align: center; font-size: 2.2rem; margin-bottom: 40px; color: #333; position: relative; padding-bottom: 15px; }
    .section-title::after {
        content: ''; position: absolute; bottom: 0; left: 50%;
        transform: translateX(-50%); width: 80px; height: 4px;
        background-color: var(--primary-color); border-radius: 2px;
    }
    .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .category-card-link { text-decoration: none; }
    .category-card { position: relative; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .category-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.12); }
    .category-image { width: 100%; height: 200px; object-fit: cover; display: block; }
    .category-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding: 20px; }
    .category-name { color: white; font-size: 1.5rem; font-weight: 600; margin: 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
</style>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
