<?php
// pages/vendor.php - Public Vendor Store Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';

$vendor_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$vendor_id) {
    header("Location: " . BASE_URL . "pages/products.php");
    exit();
}

$vendor = null;
$products = [];
$db_error = '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch vendor details
    $vendor_sql = "SELECT * FROM vendors WHERE id = :id AND status = 'approved'";
    $vendor_stmt = $db->prepare($vendor_sql);
    $vendor_stmt->execute(['id' => $vendor_id]);
    $vendor = $vendor_stmt->fetch(PDO::FETCH_ASSOC);

    if ($vendor) {
        // Fetch all active products for this vendor
        $product_sql = "SELECT * FROM products WHERE vendor_id = :vendor_id AND status = 'active' ORDER BY created_at DESC";
        $product_stmt = $db->prepare($product_sql);
        $product_stmt->execute(['vendor_id' => $vendor_id]);
        $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $db_error = "Database error: " . htmlspecialchars($e->getMessage());
    // In a real app, you'd log this error.
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-store.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/home.css"> <!-- For product card styles -->

<main class="vendor-store-page">
    <?php if ($db_error): ?>
        <div class="container"><div class="alert alert-error"><?php echo $db_error; ?></div></div>
    <?php elseif (!$vendor): ?>
        <div class="container">
            <div class="alert alert-error" style="text-align:center; padding: 4rem;">
                <h2>Vendor Not Found</h2>
                <p>The store you are looking for does not exist or is not currently active.</p>
                <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary" style="margin-top: 1rem;">Browse All Products</a>
            </div>
        </div>
    <?php else: 
        $banner_image = !empty($vendor['banner_image']) ? BASE_URL . 'uploads/vendors/' . htmlspecialchars($vendor['banner_image']) : BASE_URL . 'uploads/vendors/default-banner.png';
        $logo_image = !empty($vendor['business_logo']) ? BASE_URL . 'uploads/vendors/' . htmlspecialchars($vendor['business_logo']) : BASE_URL . 'uploads/vendors/default-logo.png';
    ?>
        <header class="store-header" style="background-image: url('<?php echo $banner_image; ?>');">
            <div class="store-header-overlay"></div>
            <div class="container">
                <div class="store-info">
                    <div class="store-logo">
                        <img src="<?php echo $logo_image; ?>" alt="<?php echo htmlspecialchars($vendor['business_name']); ?> Logo">
                    </div>
                    <div class="store-details">
                        <h1 class="store-name"><?php echo htmlspecialchars($vendor['business_name']); ?></h1>
                        <p class="store-description"><?php echo htmlspecialchars($vendor['business_description']); ?></p>
                        <div class="store-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($vendor['business_address'] ?: 'Location not specified'); ?></span>
                            <span><i class="fas fa-clock"></i> Joined <?php echo date('M Y', strtotime($vendor['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container store-content">
            <h2 class="section-title">Products from <?php echo htmlspecialchars($vendor['business_name']); ?></h2>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info" style="text-align:center; padding: 2rem;">
                    <p>This vendor has not listed any products yet. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/../includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>