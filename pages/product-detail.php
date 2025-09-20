<?php
// Include config
require_once '../includes/config.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/products.php');
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
    header('Location: ' . BASE_URL . 'pages/products.php');
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
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/product-detail.css?v=2">';
?>

<div class="product-detail-page">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo BASE_URL; ?>pages/home.php">Home</a>
            <span>/</span>
            <a href="<?php echo BASE_URL; ?>pages/products.php">Products</a>
            <span>/</span>
            <a href="<?php echo BASE_URL; ?>pages/products.php?category=<?php echo urlencode(strtolower($product['category_name'] ?? '')); ?>"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></a>
            <span>/</span>
            <span><?php echo htmlspecialchars($product['name'] ?? 'Product'); ?></span>
        </nav>

        <div class="product-detail-card">
            <div class="product-detail">
                <div class="product-gallery">
                    <div class="product-main-image">
                        <img src="<?php echo BASE_URL; ?>assets/images/products/<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'Product'); ?>" id="main-image">
                    </div>
                    <div class="product-thumbnails">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-image="<?php echo BASE_URL; ?>assets/images/products/<?php echo htmlspecialchars($image); ?>">
                            <img src="<?php echo BASE_URL; ?>assets/images/products/<?php echo htmlspecialchars($image); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="product-info-main">
                    <div class="product-header">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name'] ?? 'N/A'); ?></h1>
                        <div class="product-rating">
                            <?php
                            $rating = rand(3, 5); // Placeholder
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                            <span>(<?php echo rand(50, 500); ?> reviews)</span>
                        </div>
                    </div>

                    <div class="product-price">
                        <span class="current-price">$<?php echo number_format((float)($product['price'] ?? 0), 2); ?></span>
                        <?php if (!empty($product['compare_price']) && is_numeric($product['compare_price']) && $product['compare_price'] > 0): ?>
                        <span class="original-price">$<?php echo number_format((float)$product['compare_price'], 2); ?></span>
                        <span class="discount"><?php echo round(((float)$product['compare_price'] - (float)$product['price']) / (float)$product['compare_price'] * 100); ?>% off</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-short-description">
                        <p><?php echo nl2br(htmlspecialchars(substr($product['description'] ?? '', 0, 150))) . '...'; ?></p>
                    </div>

                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value"><a href="<?php echo BASE_URL; ?>pages/products.php?category=<?php echo urlencode(strtolower($product['category_name'] ?? '')); ?>"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></a></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Vendor:</span>
                            <span class="meta-value"><a href="#"><?php echo htmlspecialchars($product['business_name'] ?? 'N/A'); ?></a></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">SKU:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Availability:</span>
                            <span class="meta-value <?php echo (int)($product['quantity'] ?? 0) > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php echo (int)($product['quantity'] ?? 0) > 0 ? 'In Stock (' . (int)$product['quantity'] . ' available)' : 'Out of Stock'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="product-actions">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn minus" aria-label="Decrease quantity">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo (int)($product['quantity'] ?? 1); ?>" aria-label="Quantity">
                                <button type="button" class="quantity-btn plus" aria-label="Increase quantity">+</button>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-primary add-to-cart-btn" <?php echo (int)($product['quantity'] ?? 0) > 0 ? '' : 'disabled'; ?>>
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
                        <span>Share:</span>
                        <div class="share-buttons">
                            <a href="#" class="share-btn facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="share-btn twitter" aria-label="Share on Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="share-btn pinterest" aria-label="Share on Pinterest"><i class="fab fa-pinterest"></i></a>
                            <a href="#" class="share-btn whatsapp" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="product-tabs">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="description" type="button">Full Description</button>
                <button class="tab-btn" data-tab="specifications" type="button">Specifications</button>
                <button class="tab-btn" data-tab="reviews" type="button">Reviews (<?php echo rand(50, 500); ?>)</button>
                <button class="tab-btn" data-tab="vendor" type="button">Vendor Info</button>
            </div>

            <div class="tabs-content">
                <div class="tab-pane active" id="description">
                    <div class="tab-content">
                        <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?></p>
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
                        <div class="vendor-info-tab">
                            <h3><?php echo htmlspecialchars($product['business_name'] ?? 'N/A'); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($product['business_description'] ?? 'No vendor description available.')); ?></p>
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
                            <a href="<?php echo BASE_URL; ?>vendor/profile.php?vendor_id=<?php echo (int)($product['vendor_id'] ?? 0); ?>" class="btn btn-outline">View Vendor Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($related_products)): ?>
        <section class="related-products">
            <h2 class="section-title">Related Products</h2>
            <div class="products-grid">
                <?php foreach ($related_products as $related_product): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="<?php echo BASE_URL; ?>assets/images/products/<?php echo htmlspecialchars(!empty($related_product['images']) ? explode(',', $related_product['images'])[0] : 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($related_product['name'] ?? 'Product'); ?>" class="product-image">
                        <div class="product-overlay">
                            <button class="btn btn-outline quick-view-btn">Quick View</button>
                            <button class="wishlist-icon-btn">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="product-detail.php?id=<?php echo (int)($related_product['id'] ?? 0); ?>"><?php echo htmlspecialchars($related_product['name'] ?? 'N/A'); ?></a>
                        </h3>
                        <div class="product-price">
                            $<?php echo number_format((float)($related_product['price'] ?? 0), 2); ?>
                            <?php if (!empty($related_product['compare_price']) && is_numeric($related_product['compare_price'])): ?>
                                <span class="product-old-price">$<?php echo number_format((float)$related_product['compare_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-vendor">By: <?php echo htmlspecialchars($related_product['business_name'] ?? 'N/A'); ?></div>
                        <div class="product-rating">
                            <?php
                            $rating = rand(3, 5); // Placeholder
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                            <span>(<?php echo rand(10, 300); ?>)</span>
                        </div>
                        <button class="btn btn-primary add-to-cart-btn-small">Add to Cart</button>
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
            
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

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
        if (!max || value < max) {
            quantityInput.value = value + 1;
        }
    });

    // Tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
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
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>