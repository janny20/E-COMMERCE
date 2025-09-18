<?php
// Include config
require_once '../includes/config.php';

// Get category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['q']) ? $_GET['q'] : '';

// Get products with filters
$database = new Database();
$db = $database->getConnection();

// Build query based on filters
$query = "SELECT p.*, v.business_name, c.name as category_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

$params = [];

if (!empty($category_filter)) {
    $query .= " AND c.slug = :category";
    $params[':category'] = $category_filter;
}

if (!empty($search_query)) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search_query%";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter
$category_query = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name";
$category_stmt = $db->prepare($category_query);
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Products";
if (!empty($category_filter)) {
    $page_title = ucfirst($category_filter) . " Products";
} elseif (!empty($search_query)) {
    $page_title = "Search Results for: " . htmlspecialchars($search_query);
}

// Include header
require_once '../includes/header.php';

// Add products-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/products.css">';
?>

<div class="products-page">
    <div class="container">
        <div class="products-header">
            <h1 class="products-title"><?php echo $page_title; ?></h1>
            <p class="products-meta">Showing <?php echo count($products); ?> products</p>
        </div>

        <div class="products-toolbar">
            <button class="products-filter-toggle btn btn-outline">
                <i class="fas fa-filter"></i> Filters
            </button>
            
            <div class="products-sort">
                <label>Sort by:</label>
                <select class="form-select">
                    <option value="newest">Newest First</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="rating">Highest Rated</option>
                </select>
            </div>

            <div class="products-view-options">
                <button class="view-option active" data-view="grid">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-option" data-view="list">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <div class="products-layout">
            <div class="products-sidebar">
                <div class="sidebar-header">
                    <h3>Filters</h3>
                    <button class="sidebar-close">&times;</button>
                </div>

                <div class="filter-section">
                    <h4 class="filter-section-title">Categories</h4>
                    <div class="filter-options">
                        <div class="filter-option">
                            <input type="radio" id="category-all" name="category" value="" checked>
                            <label for="category-all">All Categories</label>
                        </div>
                        <?php foreach ($categories as $category): ?>
                        <div class="filter-option">
                            <input type="radio" id="category-<?php echo $category['id']; ?>" name="category" value="<?php echo $category['slug']; ?>" <?php echo $category_filter == $category['slug'] ? 'checked' : ''; ?>>
                            <label for="category-<?php echo $category['id']; ?>"><?php echo $category['name']; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-section">
                    <h4 class="filter-section-title">Price Range</h4>
                    <div class="filter-price">
                        <input type="range" class="price-range" min="0" max="1000" step="10">
                        <div class="price-values">
                            <span>$0</span>
                            <span>$1000</span>
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button class="btn btn-primary">Apply Filters</button>
                    <button class="btn btn-outline">Reset</button>
                </div>
            </div>

            <div class="products-main">
                <?php if (!empty($products)): ?>
                    <div class="products-grid-view" id="products-view">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image-container">
                                    <img src="../assets/images/products/<?php echo !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                    <div class="product-overlay">
                                        <button class="quick-view-btn">Quick View</button>
                                        <button class="wishlist-btn">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    <?php if ($product['is_featured']): ?>
                                        <div class="product-badge">Featured</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                    </h3>
                                    <div class="product-price">
                                        $<?php echo number_format($product['price'], 2); ?>
                                        <?php if ($product['compare_price']): ?>
                                            <span class="product-old-price">$<?php echo number_format($product['compare_price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-vendor">By: <?php echo htmlspecialchars($product['business_name']); ?></div>
                                    <div class="product-category">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
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

                    <div class="pagination">
                        <a href="#" class="pagination-item disabled">&laquo;</a>
                        <a href="#" class="pagination-item active">1</a>
                        <a href="#" class="pagination-item">2</a>
                        <a href="#" class="pagination-item">3</a>
                        <a href="#" class="pagination-item">&raquo;</a>
                    </div>
                <?php else: ?>
                    <div class="products-empty">
                        <div class="products-empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="products-empty-title">No products found</h3>
                        <p class="products-empty-text">Try adjusting your search or filter criteria</p>
                        <a href="products.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const filterToggle = document.querySelector('.products-filter-toggle');
    const sidebar = document.querySelector('.products-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const sidebarClose = document.querySelector('.sidebar-close');

    filterToggle.addEventListener('click', function() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
    });

    sidebarClose.addEventListener('click', function() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });

    // View toggle
    const viewOptions = document.querySelectorAll('.view-option');
    const productsView = document.getElementById('products-view');

    viewOptions.forEach(option => {
        option.addEventListener('click', function() {
            viewOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            if (this.dataset.view === 'grid') {
                productsView.classList.remove('list-view');
                productsView.classList.add('grid-view');
            } else {
                productsView.classList.remove('grid-view');
                productsView.classList.add('list-view');
            }
        });
    });

    // Category filter
    const categoryRadios = document.querySelectorAll('input[name="category"]');
    categoryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value) {
                window.location.href = `products.php?category=${this.value}`;
            } else {
                window.location.href = 'products.php';
            }
        });
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>