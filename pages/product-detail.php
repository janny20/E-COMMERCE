<?php
<<<<<<< HEAD
// Include config
require_once '../includes/config.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);

// Get product details
$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, v.business_name, v.business_description, c.name as category_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.id = :product_id AND p.status = 'active'";

$stmt = $db->prepare($query);
$stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get product images
$images = !empty($product['images']) ? explode(',', $product['images']) : ['default.jpg'];

// Get related products
$related_query = "SELECT p.*, v.business_name 
                 FROM products p 
                 JOIN vendors v ON p.vendor_id = v.id 
                 WHERE p.category_id = :category_id 
                 AND p.id != :product_id 
                 AND p.status = 'active' 
                 ORDER BY RAND() 
                 LIMIT 4";

$related_stmt = $db->prepare($related_query);
$related_stmt->bindParam(':category_id', $product['category_id'], PDO::PARAM_INT);
$related_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
$related_stmt->execute();
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';

// Add product-detail-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/product-detail.css">';
?>

<div class="product-detail-page">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo BASE_URL; ?>">Home</a>
            <span>/</span>
            <a href="<?php echo BASE_URL; ?>pages/products.php">Products</a>
            <span>/</span>
            <a href="<?php echo BASE_URL; ?>pages/products.php?category=<?php echo strtolower($product['category_name']); ?>"><?php echo $product['category_name']; ?></a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div class="product-detail">
            <div class="product-gallery">
                <div class="product-main-image">
                    <img src="../assets/images/products/<?php echo $images[0]; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="main-image">
                </div>
                <div class="product-thumbnails">
                    <?php foreach ($images as $index => $image): ?>
                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-image="../assets/images/products/<?php echo $image; ?>">
                        <img src="../assets/images/products/<?php echo $image; ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="product-info">
                <div class="product-header">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-rating">
                        <?php
                        $rating = rand(3, 5);
                        for ($i = 0; $i < 5; $i++) {
                            if ($i < $rating) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                        <span>(<?php echo rand(50, 500); ?> reviews)</span>
                    </div>
                </div>

                <div class="product-price">
                    <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php if ($product['compare_price']): ?>
                    <span class="original-price">$<?php echo number_format($product['compare_price'], 2); ?></span>
                    <span class="discount"><?php echo round(($product['compare_price'] - $product['price']) / $product['compare_price'] * 100); ?>% off</span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Category:</span>
                        <span class="meta-value"><?php echo $product['category_name']; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Vendor:</span>
                        <span class="meta-value"><?php echo $product['business_name']; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">SKU:</span>
                        <span class="meta-value"><?php echo $product['sku'] ?: 'N/A'; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Availability:</span>
                        <span class="meta-value <?php echo $product['quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo $product['quantity'] > 0 ? 'In Stock (' . $product['quantity'] . ' available)' : 'Out of Stock'; ?>
                        </span>
                    </div>
                </div>

                <div class="product-actions">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn minus">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                            <button type="button" class="quantity-btn plus">+</button>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary add-to-cart-btn" <?php echo $product['quantity'] > 0 ? '' : 'disabled'; ?>>
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                        <button class="btn btn-outline wishlist-btn">
                            <i class="far fa-heart"></i>
                            Add to Wishlist
                        </button>
                    </div>
                </div>

                <div class="product-share">
                    <span>Share this product:</span>
                    <div class="share-buttons">
                        <a href="#" class="share-btn facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="share-btn pinterest"><i class="fab fa-pinterest"></i></a>
                        <a href="#" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-tabs">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="description">Description</button>
                <button class="tab-btn" data-tab="specifications">Specifications</button>
                <button class="tab-btn" data-tab="reviews">Reviews (<?php echo rand(50, 500); ?>)</button>
                <button class="tab-btn" data-tab="vendor">Vendor Info</button>
            </div>

            <div class="tabs-content">
                <div class="tab-pane active" id="description">
                    <div class="tab-content">
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    </div>
                </div>

                <div class="tab-pane" id="specifications">
                    <div class="tab-content">
                        <div class="specs-table">
                            <div class="spec-row">
                                <div class="spec-name">Weight</div>
                                <div class="spec-value">1.5 kg</div>
                            </div>
                            <div class="spec-row">
                                <div class="spec-name">Dimensions</div>
                                <div class="spec-value">20 × 15 × 10 cm</div>
                            </div>
                            <div class="spec-row">
                                <div class="spec-name">Color</div>
                                <div class="spec-value">Black, White, Blue</div>
                            </div>
                            <div class="spec-row">
                                <div class="spec-name">Material</div>
                                <div class="spec-value">Plastic, Metal</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="reviews">
                    <div class="tab-content">
                        <div class="reviews-summary">
                            <div class="average-rating">
                                <div class="rating-number">4.8</div>
                                <div class="rating-stars">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-count">Based on <?php echo rand(50, 500); ?> reviews</div>
                            </div>
                            <div class="rating-bars">
                                <div class="rating-bar">
                                    <span class="bar-label">5 stars</span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: 75%;"></div>
                                    </div>
                                    <span class="bar-percentage">75%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="bar-label">4 stars</span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: 15%;"></div>
                                    </div>
                                    <span class="bar-percentage">15%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="bar-label">3 stars</span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: 7%;"></div>
                                    </div>
                                    <span class="bar-percentage">7%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="bar-label">2 stars</span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: 2%;"></div>
                                    </div>
                                    <span class="bar-percentage">2%</span>
                                </div>
                                <div class="rating-bar">
                                    <span class="bar-label">1 star</span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: 1%;"></div>
                                    </div>
                                    <span class="bar-percentage">1%</span>
                                </div>
                            </div>
                        </div>

                        <div class="reviews-list">
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer">John Doe</div>
                                    <div class="review-date">October 15, 2023</div>
                                    <div class="review-rating">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>Excellent product! The quality is amazing and it works perfectly. Would definitely recommend to others.</p>
                                </div>
                            </div>

                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer">Jane Smith</div>
                                    <div class="review-date">October 10, 2023</div>
                                    <div class="review-rating">
                                        <?php for ($i = 0; $i < 4; $i++): ?>
                                        <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        <i class="far fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>Good product overall, but the delivery took longer than expected. The product itself is great quality.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="vendor">
                    <div class="tab-content">
                        <div class="vendor-info">
                            <h3><?php echo $product['business_name']; ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($product['business_description'])); ?></p>
                            <div class="vendor-stats">
                                <div class="stat">
                                    <div class="stat-number">4.9</div>
                                    <div class="stat-label">Seller Rating</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number"><?php echo rand(100, 5000); ?></div>
                                    <div class="stat-label">Products Sold</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number"><?php echo rand(95, 100); ?>%</div>
                                    <div class="stat-label">Positive Reviews</div>
                                </div>
                            </div>
                            <a href="<?php echo BASE_URL; ?>vendor/profile.php?vendor_id=<?php echo $product['vendor_id']; ?>" class="btn btn-outline">View Vendor Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($related_products)): ?>
        <section class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php foreach ($related_products as $related_product): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="../assets/images/products/<?php echo !empty($related_product['images']) ? explode(',', $related_product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($related_product['name']); ?>" class="product-image" loading="lazy" decoding="async">
                        <div class="product-overlay">
                            <button class="quick-view-btn">Quick View</button>
                            <button class="wishlist-btn">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="product-detail.php?id=<?php echo $related_product['id']; ?>"><?php echo htmlspecialchars($related_product['name']); ?></a>
                        </h3>
                        <div class="product-price">
                            $<?php echo number_format($related_product['price'], 2); ?>
                            <?php if ($related_product['compare_price']): ?>
                                <span class="product-old-price">$<?php echo number_format($related_product['compare_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-vendor">By: <?php echo htmlspecialchars($related_product['business_name']); ?></div>
                        <div class="product-rating">
                            <?php
                            $rating = rand(3, 5);
                            for ($i = 0; $i < 5; $i++) {
                                if ($i < $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                            <span>(<?php echo rand(10, 300); ?>)</span>
                        </div>
                        <button class="add-to-cart-btn">Add to Cart</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery
    const mainImage = document.getElementById('main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const imageUrl = this.getAttribute('data-image');
            mainImage.src = imageUrl;
            
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';

// Helper functions
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function get_all_images($images_string) {
    $fallback = ['default.jpg'];
    if (empty($images_string)) return $fallback;
    $parts = array_filter(array_map('trim', explode(',', $images_string)));
    return count($parts) ? $parts : $fallback;
}

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$reviews = [];
$related_products = [];
$avg_rating = 0;
$review_count = 0;
$db_error = '';

if (!$product_id) {
    // Optionally, redirect to a 404 page
    header("Location: products.php");
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch main product details
    $sql = "SELECT p.*, v.business_name, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN vendors v ON p.vendor_id = v.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id AND p.status = 'active'";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Fetch reviews for the product
        $review_sql = "SELECT r.*, u.username
                       FROM reviews r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.product_id = :product_id AND r.is_approved = 1
                       ORDER BY r.created_at DESC";
        $review_stmt = $db->prepare($review_sql);
        $review_stmt->execute(['product_id' => $product_id]);
        $reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate average rating and count
        if (!empty($reviews)) {
            $total_rating = 0;
            foreach ($reviews as $review) {
                $total_rating += $review['rating'];
            }
            $review_count = count($reviews);
            $avg_rating = round($total_rating / $review_count, 1);
        }

        // Fetch related products from the same category
        $related_sql = "SELECT p.*, v.business_name
                        FROM products p
                        LEFT JOIN vendors v ON p.vendor_id = v.id
                        WHERE p.category_id = :category_id AND p.id != :product_id AND p.status = 'active'
                        ORDER BY RAND()
                        LIMIT 4";
        $related_stmt = $db->prepare($related_sql);
        $related_stmt->execute(['category_id' => $product['category_id'], 'product_id' => $product_id]);
        $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $ex) {
    $db_error = "Database error: " . e($ex->getMessage());
    // In a real app, you'd log this error.
}

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/product-detail.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/home.css"> <!-- For product card styles -->

<main class="product-detail-page">
    <div class="container">
        <?php if ($db_error): ?>
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        <?php elseif (!$product): ?>
            <div class="alert alert-error">
                <h2>Product Not Found</h2>
                <p>The product you are looking for does not exist or is no longer available.</p>
                <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn">Back to Products</a>
            </div>
        <?php else: 
            $images = get_all_images($product['images']);
            $first_image = $images[0];
        ?>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>pages/home.php">Home</a>
                <span>/</span>
                <a href="<?php echo BASE_URL; ?>pages/products.php?category=<?php echo e($product['category_slug']); ?>"><?php echo e($product['category_name']); ?></a>
                <span>/</span>
                <span><?php echo e($product['name']); ?></span>
            </div>

            <section class="product-main-grid">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image-wrapper">
                        <img src="<?php echo BASE_URL; ?>assets/images/products/<?php echo e($first_image); ?>" alt="<?php echo e($product['name']); ?>" id="mainProductImage">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach ($images as $index => $img): ?>
                        <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" data-image="<?php echo BASE_URL; ?>assets/images/products/<?php echo e($img); ?>">
                            <img src="<?php echo BASE_URL; ?>assets/images/products/<?php echo e($img); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Details -->
                <div class="product-details-content">
                    <h1><?php echo e($product['name']); ?></h1>
                    <div class="product-meta">
                        <div class="product-vendor">Sold by: <a href="<?php echo BASE_URL; ?>pages/vendor.php?id=<?php echo e($product['vendor_id']); ?>"><?php echo e($product['business_name']); ?></a></div>
                        <div class="product-rating-summary">
                            <?php 
                                $avg_rating = !empty($product['rating']) ? round((float)$product['rating'], 1) : 0;
                                $review_count = !empty($product['review_count']) ? (int)$product['review_count'] : 0;
                            ?>
                            <i class="fas fa-star"></i> <?php echo e($avg_rating); ?> (<?php echo e($review_count); ?> reviews)
                        </div>
                    </div>

                    <div class="product-price-box">
                        <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if (!empty($product['compare_price'])): ?>
                            <span class="old-price">$<?php echo number_format($product['compare_price'], 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="short-description"><?php echo e($product['short_description'] ?? 'No description available.'); ?></p>

                    <div class="stock-status <?php echo $product['quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $product['quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </div>

                    <?php if ($product['quantity'] > 0): ?>
                    <form id="add-to-cart-form" method="POST" action="<?php echo BASE_URL; ?>ajax/add_to_cart.php">
                        <div class="product-actions">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn minus">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo e($product['quantity']); ?>" class="quantity-input">
                                <button type="button" class="quantity-btn plus">+</button>
                            </div>
                            <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                            <button type="submit" class="btn btn-primary btn-add-to-cart"><i class="fas fa-shopping-cart"></i> Add to Cart</button>
                        </div>
                    </form>
                    <?php endif; ?>
                    <?php
                        $is_in_wishlist = isset($wishlist_ids) && in_array($product['id'], $wishlist_ids);
                    ?>
                    <button class="btn-wishlist <?php echo $is_in_wishlist ? 'active' : ''; ?>" data-product-id="<?php echo e($product['id']); ?>" title="Add to Wishlist">
                        <i class="<?php echo $is_in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                </div>
            </section>

            <!-- Product Info Tabs -->
            <section class="product-info-tabs">
                <nav class="tab-nav">
                    <div class="tab-nav-item active" data-tab="description">Description</div>
                    <div class="tab-nav-item" data-tab="specifications">Specifications</div>
                    <div class="tab-nav-item" data-tab="reviews">Reviews (<?php echo e(count($reviews)); ?>)</div>
                </nav>
                <div class="tab-content">
                    <div class="tab-pane active" id="description">
                        <p><?php echo nl2br(e($product['description'] ?? 'No detailed description available.')); ?></p>
                    </div>
                    <div class="tab-pane" id="specifications">
                        <p><?php echo nl2br(e($product['specifications'] ?? 'No specifications provided.')); ?></p>
                    </div>
                    <div class="tab-pane" id="reviews">
                        <?php if (empty($reviews)): ?>
                            <p>There are no reviews for this product yet. Be the first to leave a review!</p>
                        <?php else: ?>
                            <div class="review-list">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <span class="review-author"><?php echo e($review['username']); ?></span>
                                        <span class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="<?php echo $i < $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="review-comment"><?php echo e($review['comment']); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Review Form -->
                        <div class="review-form">
                            <h3>Write a Review</h3>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="<?php echo BASE_URL; ?>ajax/submit_review.php" method="POST">
                                    <div class="form-group">
                                        <label for="rating">Your Rating</label>
                                        <!-- Basic star rating input -->
                                        <select name="rating" id="rating" class="form-control" required>
                                            <option value="">Select a rating</option>
                                            <option value="5">5 Stars - Excellent</option>
                                            <option value="4">4 Stars - Good</option>
                                            <option value="3">3 Stars - Average</option>
                                            <option value="2">2 Stars - Fair</option>
                                            <option value="1">1 Star - Poor</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Your Review</label>
                                        <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
                                    </div>
                                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            <?php else: ?>
                                <p>You must be <a href="<?php echo BASE_URL; ?>pages/login.php?redirect=product-detail.php?id=<?php echo $product_id; ?>">logged in</a> to post a review.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
            <section class="related-products content-section">
                <h2 class="section-title">You Might Also Like</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related_product): ?>
                        <?php include __DIR__ . '/../includes/product-card.php'; // Re-use product card template ?>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</main>

<style>
    .recently-viewed-products {
        margin-top: var(--spacing-xxl);
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image Gallery
    const mainImage = document.getElementById('mainProductImage');
    const thumbnails = document.querySelectorAll('.thumbnail-item');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            mainImage.src = this.dataset.image;
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

<<<<<<< HEAD
    // Quantity controls
    const quantityInput = document.getElementById('quantity');
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');

    minusBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
        }
    });

    plusBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        const max = parseInt(quantityInput.getAttribute('max'));
        if (value < max) {
            quantityInput.value = value + 1;
        }
    });

    // Tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Add to cart
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    addToCartBtn.addEventListener('click', function() {
        const quantity = parseInt(quantityInput.value);
        const productId = <?php echo $product_id; ?>;
        
        // Simulate add to cart
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        this.disabled = true;
        
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                this.disabled = false;
            }, 2000);
        }, 1000);
=======
    // Quantity Selector
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.querySelector('.quantity-input');

    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', () => {
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', () => {
            let currentValue = parseInt(quantityInput.value);
            let max = parseInt(quantityInput.max);
            if (currentValue < max) {
                quantityInput.value = currentValue + 1;
            }
        });
    }

    // Tabs
    const tabNavItems = document.querySelectorAll('.tab-nav-item');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabNavItems.forEach(item => {
        item.addEventListener('click', function() {
            const tabId = this.dataset.tab;

            tabNavItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');

            tabPanes.forEach(pane => {
                if (pane.id === tabId) {
                    pane.classList.add('active');
                } else {
                    pane.classList.remove('active');
                }
            });
        });
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    });
});
</script>

<?php
<<<<<<< HEAD
// Include footer
require_once '../includes/footer.php';
=======
require_once __DIR__ . '/../includes/footer.php';
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
?>