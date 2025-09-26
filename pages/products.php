<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Then include config
require_once '../includes/config.php';

// Get category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Get wishlist IDs for the current user to show correct heart icon status
$wishlist_ids = [];

// Get products with filters
$database = new Database();
$db = $database->getConnection();

// Get current category details for the header
$current_category = null;
if (!empty($category_filter)) {
    $cat_stmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug");
    $cat_stmt->execute([':slug' => $category_filter]);
    $current_category = $cat_stmt->fetch(PDO::FETCH_ASSOC);
}

// Build query based on filters
$query = "SELECT SQL_CALC_FOUND_ROWS p.*, v.business_name, c.name as category_name
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

if ($min_price !== null) {
    $query .= " AND p.price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price !== null) {
    $query .= " AND p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Add sorting
switch ($sort_by) {
    case 'price-low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price-high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'rating':
        $query .= " ORDER BY p.rating DESC, p.review_count DESC";
        break;
    default: // 'newest'
        $query .= " ORDER BY p.created_at DESC";
        break;
}

$query .= " LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$total_count = $db->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_pages = ceil($total_count / $limit);

if ($isLoggedIn) {
    $wishlist_ids = getWishlistProductIds($db, $userId);
}

// Get all categories for filter
$category_query = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name";
$category_stmt = $db->prepare($category_query);
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Products";
if ($current_category) {
    $page_title = $current_category['name'];
} elseif (!empty($search_query)) {
    $page_title = "Search Results for: " . htmlspecialchars($search_query);
}

// Include header

require_once '../includes/header.php';
// Add products-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/products.css">';
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/home.css">'; // For product card styles
?>

<div class="products-page">
    <div class="container">
        <div class="products-header" <?php if ($current_category && !empty($current_category['image'])): ?> style="background-image: url('<?php echo BASE_URL . 'assets/images/categories/' . htmlspecialchars($current_category['image']); ?>');" <?php endif; ?>>
            <div class="products-header-overlay"></div>
            <div class="products-header-content">
                <h1 class="products-title"><?php echo $page_title; ?></h1>
                <?php if ($current_category && !empty($current_category['description'])): ?>
                    <p class="products-description"><?php echo htmlspecialchars($current_category['description']); ?></p>
                <?php endif; ?>
                <p class="products-meta">
                    Showing <?php echo count($products); ?> of <?php echo $total_count; ?> product<?php echo ($total_count != 1) ? 's' : ''; ?>
                </p>
            </div>
        </div>

        <div class="products-toolbar">
            <button class="products-filter-toggle btn btn-outline">
                <i class="fas fa-filter"></i> Filters
            </button>
            
            <div class="products-sort">
                <label>Sort by:</label>
                <select id="sort-select" class="form-select">
                    <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price-low" <?php echo $sort_by === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price-high" <?php echo $sort_by === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
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
                    <h4 class="filter-section-title">
                        <span>Categories</span>
                        <button class="filter-toggle-btn"><i class="fas fa-chevron-down"></i></button>
                    </h4>
                    <div class="filter-options" style="max-height: 500px;">
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
                    <h4 class="filter-section-title">
                        <span>Price Range</span>
                        <button class="filter-toggle-btn"><i class="fas fa-chevron-down"></i></button>
                    </h4>
                    <div class="filter-options">
                        <div class="price-inputs">
                            <input type="number" id="min_price" name="min_price" placeholder="Min" value="<?php echo $min_price !== null ? htmlspecialchars($min_price) : ''; ?>" min="0">
                            <span>-</span>
                            <input type="number" id="max_price" name="max_price" placeholder="Max" value="<?php echo $max_price !== null ? htmlspecialchars($max_price) : ''; ?>" min="0">
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button class="btn btn-primary" id="apply-filters-btn">Apply Filters</button>
                    <button class="btn btn-outline" id="reset-filters-btn">Reset</button>
                </div>
            </div>

            <div class="products-main">
                <?php if (!empty($products)): ?>
                    <div class="products-grid-view" id="products-view">
                        <div class="products-grid">
                            <?php foreach ($products as $product) {
                                include '../includes/product-card.php';
                            } ?>
                        </div>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?php echo getPageLink($page - 1); ?>" class="pagination-item">&laquo;</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <a href="<?php echo getPageLink($i); ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                    <span class="pagination-item disabled">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="<?php echo getPageLink($page + 1); ?>" class="pagination-item">&raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="products-empty">
                        <div class="products-empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="products-empty-title">No products found</h3>
                        <p class="products-empty-text">Try adjusting your search or filter criteria</p>
                        <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay"></div>

<?php
// Helper function to generate pagination links for this page
function getPageLink($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}
?>

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

    // Filter accordion
    document.querySelectorAll('.filter-section-title').forEach(title => {
        title.addEventListener('click', function() {
            this.parentElement.classList.toggle('open');
        });
    });

    // View toggle
    const viewOptions = document.querySelectorAll('.view-option');
    const productsGrid = document.querySelector('.products-grid');

    viewOptions.forEach(option => {
        option.addEventListener('click', function() {
            viewOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            if (productsGrid) {
                productsGrid.className = 'products-grid ' + this.dataset.view + '-view';
            }
        });
    });

    // Category filter
    const categoryRadios = document.querySelectorAll('input[name="category"]');
    categoryRadios.forEach(radio => {
        radio.addEventListener('change', applyProductFilters);
    });

    // Sort filter
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', applyProductFilters);
    }

    // Filter buttons
    const applyBtn = document.getElementById('apply-filters-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', applyProductFilters);
    }

    const resetBtn = document.getElementById('reset-filters-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            window.location.href = 'products.php';
        });
    }

    function applyProductFilters() {
        const params = new URLSearchParams(window.location.search);
        const category = document.querySelector('input[name="category"]:checked').value;
        const sortBy = document.getElementById('sort-select').value;
        const minPrice = document.getElementById('min_price').value;
        const maxPrice = document.getElementById('max_price').value;

        if (category) params.set('category', category); else params.delete('category');
        if (sortBy !== 'newest') params.set('sort', sortBy); else params.delete('sort');
        if (minPrice) params.set('min_price', minPrice); else params.delete('min_price');
        if (maxPrice) params.set('max_price', maxPrice); else params.delete('max_price');

        params.delete('page'); // Reset to first page when filters change
        window.location.href = `products.php?${params.toString()}`;
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>