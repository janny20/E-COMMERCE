<?php
// pages/home.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// robust pathing
require_once __DIR__ . '/../includes/config.php';

// Redirect to landing page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'landing.php');
    exit();
}

// If a vendor or admin lands here, redirect them to their respective dashboards
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'vendor') {
        header('Location: ' . BASE_URL . 'vendor/dashboard.php');
        exit();
    } elseif ($_SESSION['user_type'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin/admin-dashboard.php');
        exit();
    }
}

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

<!-- Link to the specific stylesheet for this page -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/home.css">

<main class="home-page">
    <!-- Hero Section -->
    <section class="hero" style="background: url('<?php echo BASE_URL; ?>assets/images/hero-bg.jpg') no-repeat center center; background-size: cover;">
        <div class="container">
            <?php
            if (!empty($_SESSION['welcome_message'])) {
                echo '<h1>' . e($_SESSION['welcome_message']) . '</h1>';
                unset($_SESSION['welcome_message']);
            } else {
                echo '<h1>Welcome back, ' . e($username ?? $_SESSION['username'] ?? 'User') . '!</h1>';
            }
            ?>
            <p>Discover amazing products from the best vendors.</p>
            <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn">Shop Now</a>
            <a href="<?php echo BASE_URL; ?>pages/cart.php" class="btn btn-outline">View Cart</a>
        </div>
    </section>

    <?php if ($db_error): ?>
        <div class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        </div>
    <?php endif; ?>

    <!-- Featured Products -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <?php if (empty($featured_products)): ?>
                <p class="text-center">No featured products at the moment. Check back soon!</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($featured_products as $product): ?>
                        <?php include __DIR__ . '/../includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="content-section bg-light">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <?php if (empty($categories)): ?>
                <p class="text-center">No categories found.</p>
            <?php else: ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo urlencode($category['slug']); ?>" class="category-card">
                            <?php 
                                $cat_img = !empty($category['image']) 
                                    ? BASE_URL . 'assets/images/categories/' . e($category['image'])
                                    : BASE_URL . 'assets/images/placeholder-category.jpg';
                            ?>
                            <img src="<?php echo $cat_img; ?>" alt="<?php echo e($category['name']); ?>" class="category-card-bg" loading="lazy" decoding="async">
                            <div class="category-card-overlay"></div>
                            <div class="category-card-content">
                                <h3><?php echo e($category['name']); ?></h3>
                                <p>Explore Now</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <?php if (empty($new_products)): ?>
                <p class="text-center">No new arrivals right now. Check back soon!</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($new_products as $product): ?>
                        <?php 
                            $product['is_new'] = true;
                            include __DIR__ . '/../includes/product-card.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>
                <div class="section-footer">
                    <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-outline">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="home-cta-section">
        <div class="container">
            <h2>Find Everything You Need</h2>
            <p>Explore thousands of products from trusted vendors across the country.</p>
            <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn">Explore All Products</a>
        </div>
    </section>
</main>

<?php
// Include footer
require_once __DIR__ . '/../includes/footer.php';
