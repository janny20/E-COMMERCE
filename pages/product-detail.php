<?php
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
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

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
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>