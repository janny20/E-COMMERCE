<?php
// includes/product-card.php
// This template assumes a $product variable is available in the current scope.

// Helper function to get the first image or a fallback
if (!function_exists('first_image')) {
    function first_image($images_string) {
        $fallback = 'default.jpg';
        if (empty($images_string)) return $fallback;
        $parts = array_filter(array_map('trim', explode(',', $images_string)));
        return count($parts) ? $parts[0] : $fallback;
    }
}

// Helper function for escaping output
if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// Use BASE_URL for robust pathing
$image_path = BASE_URL . 'assets/images/products/';
$product_detail_url = BASE_URL . 'pages/product-detail.php';
$vendor_url = BASE_URL . 'pages/vendor.php';

// The variable $product is expected to be defined in the including file.
?>
<div class="product-card">
    <?php if (!empty($product['is_featured'])): ?>
        <div class="product-badge">Featured</div>
    <?php elseif (!empty($product['is_new'])): ?>
        <div class="product-badge new">New</div>
    <?php endif; ?>
    <a href="<?php echo $product_detail_url; ?>?id=<?php echo (int)$product['id']; ?>" class="product-image-container">
        <?php $img = first_image($product['images'] ?? ''); ?>
        <img src="<?php echo $image_path . e($img); ?>" alt="<?php echo e($product['name'] ?? 'Product'); ?>" class="product-image" loading="lazy" decoding="async">
    </a>
    <div class="product-info">
        <a href="<?php echo $product_detail_url; ?>?id=<?php echo (int)$product['id']; ?>" class="product-title-link">
            <h3 class="product-title"><?php echo e($product['name'] ?? 'Untitled'); ?></h3>
        </a>
        <p class="product-vendor">
            Sold by: 
            <a href="<?php echo $vendor_url; ?>?id=<?php echo e($product['vendor_id'] ?? '#'); ?>">
                <?php echo e($product['business_name'] ?? 'Unknown'); ?>
            </a>
    </p>
    <p class="product-list-description">
        <?php
            $description = $product['description'] ?? '';
            // Truncate description for list view
            echo e(strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description);
        ?>
        </p>
        <div class="product-price">
            $<?php echo number_format((float)($product['price'] ?? 0), 2); ?>
            <?php if (!empty($product['compare_price']) && is_numeric($product['compare_price'])): ?>
                <span class="product-old-price">$<?php echo number_format((float)$product['compare_price'], 2); ?></span>
            <?php endif; ?>
        </div>
        <div class="product-rating">
            <?php
            if (isset($product['rating']) && is_numeric($product['rating']) && $product['rating'] > 0) {
                $rating = (int)round($product['rating']);
                for ($i = 0; $i < 5; $i++) { echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; }
                $reviews = isset($product['review_count']) ? (int)$product['review_count'] : 0;
                echo ' <span>(' . $reviews . ')</span>';
            } else { echo '<span class="no-rating">No ratings yet</span>'; }
            ?>
        </div>
        <div class="product-actions">
            <?php if (isset($show_move_to_cart_button) && $show_move_to_cart_button): ?>
                <button class="product-btn btn-move-to-cart" data-product-id="<?php echo (int)$product['id']; ?>"><i class="fas fa-shopping-cart"></i> Move to Cart</button>
            <?php else: ?>
                <?php if (basename($_SERVER['PHP_SELF']) !== 'product-detail.php'): ?>
                    <button class="product-btn btn-quick-view" data-product-id="<?php echo (int)$product['id']; ?>"><i class="fas fa-eye"></i> Quick View</button>
                <?php else: ?>
                    <a href="<?php echo $product_detail_url; ?>?id=<?php echo (int)$product['id']; ?>" class="product-btn">View Details</a>
                <?php endif; ?>
            <?php endif; ?>
            <?php
                $is_in_wishlist = isset($wishlist_ids) && in_array($product['id'], $wishlist_ids);
                $wishlist_title = $is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist';
            ?>
            <button class="wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" data-product-id="<?php echo (int)$product['id']; ?>" title="<?php echo $wishlist_title; ?>">
                <i class="<?php echo $is_in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
            </button>
        </div>
    </div>
</div>