<?php
// Include config
require_once '../includes/config.php';

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get search results
$database = new Database();
$db = $database->getConnection();

// Build query
$query = "SELECT SQL_CALC_FOUND_ROWS p.*, v.business_name, c.name as category_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

$params = [];
$where_conditions = [];

if (!empty($search_query)) {
    $where_conditions[] = "(p.name LIKE :search OR p.description LIKE :search OR v.business_name LIKE :search)";
    $params[':search'] = "%$search_query%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "c.slug = :category";
    $params[':category'] = $category_filter;
}

if ($min_price > 0) {
    $where_conditions[] = "p.price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price < 10000) {
    $where_conditions[] = "p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

if (!empty($where_conditions)) {
    $query .= " AND " . implode(" AND ", $where_conditions);
}

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    case 'rating':
        $query .= " ORDER BY p.rating DESC";
        break;
    default:
        $query .= " ORDER BY 
                  (CASE 
                   WHEN p.name LIKE :search_exact THEN 1
                   WHEN p.description LIKE :search_exact THEN 2
                   ELSE 3
                   END)";
        $params[':search_exact'] = "$search_query%";
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

// Get total count
$total_count = $db->query("SELECT FOUND_ROWS()")->fetchColumn();

// Get categories for filter
$categories_query = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate pagination
$total_pages = ceil($total_count / $limit);

// Include header
require_once '../includes/header.php';

// Add search-specific CSS
echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/search.css">';
?>

<div class="search-page">
    <div class="container">
        <div class="search-header">
            <h1>Search Results</h1>
            
            <div class="search-info">
                <?php if (!empty($search_query)): ?>
                    <p>Search results for: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></p>
                <?php endif; ?>
                
                <?php if (!empty($category_filter)): ?>
                    <p>Category: <strong><?php echo htmlspecialchars(ucfirst($category_filter)); ?></strong></p>
                <?php endif; ?>
                
                <p class="results-count"><?php echo $total_count; ?> result<?php echo $total_count != 1 ? 's' : ''; ?> found</p>
            </div>
        </div>

        <div class="search-content">
            <div class="search-sidebar">
                <div class="filter-section">
                    <h3>Filters</h3>
                    
                    <div class="filter-group">
                        <h4>Categories</h4>
                        <div class="filter-options">
                            <div class="filter-option">
                                <input type="radio" id="category-all" name="category" value="" <?php echo empty($category_filter) ? 'checked' : ''; ?> onchange="updateFilters()">
                                <label for="category-all">All Categories</label>
                            </div>
                            <?php foreach ($categories as $category): ?>
                            <div class="filter-option">
                                <input type="radio" id="category-<?php echo $category['id']; ?>" name="category" value="<?php echo $category['slug']; ?>" <?php echo $category_filter === $category['slug'] ? 'checked' : ''; ?> onchange="updateFilters()">
                                <label for="category-<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Price Range</h4>
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" id="min_price" name="min_price" placeholder="Min" value="<?php echo $min_price; ?>" min="0" max="10000">
                                <span>-</span>
                                <input type="number" id="max_price" name="max_price" placeholder="Max" value="<?php echo $max_price != 10000 ? $max_price : ''; ?>" min="0" max="10000">
                            </div>
                            <button class="btn btn-sm" onclick="updateFilters()">Apply</button>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Rating</h4>
                        <div class="filter-options">
                            <?php for ($i = 4; $i >= 1; $i--): ?>
                            <div class="filter-option">
                                <input type="checkbox" id="rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>">
                                <label for="rating-<?php echo $i; ?>">
                                    <?php for ($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star<?php echo $j < $i ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                    <span>& Up</span>
                                </label>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                        <button class="btn btn-outline" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>

            <div class="search-main">
                <div class="search-toolbar">
                    <div class="sort-options">
                        <label for="sort-select">Sort by:</label>
                        <select id="sort-select" onchange="updateSort()">
                            <option value="relevance" <?php echo $sort_by === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                            <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>

                    <div class="view-options">
                        <span>View:</span>
                        <button class="view-option active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-option" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <?php if (!empty($products)): ?>
                    <div class="search-results grid-view">
                        <div class="products-grid">
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
                                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
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

                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?php echo getPageLink($page - 1); ?>" class="pagination-item">&laquo; Previous</a>
                            <?php else: ?>
                                <span class="pagination-item disabled">&laquo; Previous</span>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= min($total_pages, 5); $i++): ?>
                                <a href="<?php echo getPageLink($i); ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if ($total_pages > 5): ?>
                                <span class="pagination-item">...</span>
                                <a href="<?php echo getPageLink($total_pages); ?>" class="pagination-item"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="<?php echo getPageLink($page + 1); ?>" class="pagination-item">Next &raquo;</a>
                            <?php else: ?>
                                <span class="pagination-item disabled">Next &raquo;</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h2>No products found</h2>
                        <p>Try adjusting your search criteria or filters</p>
                        <div class="suggestions">
                            <p>Suggestions:</p>
                            <ul>
                                <li>Check your spelling</li>
                                <li>Try more general keywords</li>
                                <li>Try different keywords</li>
                                <li>Reduce the number of filters</li>
                            </ul>
                        </div>
                        <a href="products.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to generate pagination links
function getPageLink($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return 'search.php?' . http_build_query($params);
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle
    const viewOptions = document.querySelectorAll('.view-option');
    const searchResults = document.querySelector('.search-results');
    
    viewOptions.forEach(option => {
        option.addEventListener('click', function() {
            viewOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            if (this.dataset.view === 'grid') {
                searchResults.classList.remove('list-view');
                searchResults.classList.add('grid-view');
            } else {
                searchResults.classList.remove('grid-view');
                searchResults.classList.add('list-view');
            }
        });
    });
});

function updateFilters() {
    // This function would collect all filter values and update the URL
    const category = document.querySelector('input[name="category"]:checked').value;
    const minPrice = document.getElementById('min_price').value;
    const maxPrice = document.getElementById('max_price').value;
    
    let url = `search.php?q=<?php echo urlencode($search_query); ?>`;
    
    if (category) url += `&category=${category}`;
    if (minPrice) url += `&min_price=${minPrice}`;
    if (maxPrice) url += `&max_price=${maxPrice}`;
    
    window.location.href = url;
}

function updateSort() {
    const sortBy = document.getElementById('sort-select').value;
    let url = `search.php?q=<?php echo urlencode($search_query); ?>&sort=${sortBy}`;
    
    <?php if (!empty($category_filter)): ?>
    url += `&category=<?php echo $category_filter; ?>`;
    <?php endif; ?>
    
    <?php if ($min_price > 0): ?>
    url += `&min_price=<?php echo $min_price; ?>`;
    <?php endif; ?>
    
    <?php if ($max_price < 10000): ?>
    url += `&max_price=<?php echo $max_price; ?>`;
    <?php endif; ?>
    
    window.location.href = url;
}

function resetFilters() {
    window.location.href = `search.php?q=<?php echo urlencode($search_query); ?>`;
}

function applyFilters() {