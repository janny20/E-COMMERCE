<?php
// Include config
require_once '../includes/config.php';

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
<<<<<<< HEAD
=======
$min_rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get search results
$database = new Database();
$db = $database->getConnection();

// Build query
<<<<<<< HEAD
$query = "SELECT SQL_CALC_FOUND_ROWS p.*, v.business_name, c.name as category_name 
=======
$query = "SELECT SQL_CALC_FOUND_ROWS p.*, v.business_name, c.name as category_name
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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

<<<<<<< HEAD
=======
if ($min_rating > 0) {
    $where_conditions[] = "p.rating >= :min_rating";
    $params[':min_rating'] = $min_rating;
}

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
<<<<<<< HEAD
        $query .= " ORDER BY p.rating DESC";
        break;
    default:
=======
        $query .= " ORDER BY p.rating DESC, p.review_count DESC";
        break;
    default: // relevance
        if (!empty($search_query)) {
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
        $query .= " ORDER BY 
                  (CASE 
                   WHEN p.name LIKE :search_exact THEN 1
                   WHEN p.description LIKE :search_exact THEN 2
                   ELSE 3
                   END)";
<<<<<<< HEAD
        $params[':search_exact'] = "$search_query%";
=======
            $params[':search_exact'] = "$search_query%";
        }
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
?>

<<<<<<< HEAD
=======
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/home.css"> <!-- For product card styles -->

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
                <div class="sidebar-header">
                    <h3>Filters</h3>
                    <button class="sidebar-close">&times;</button>
                </div>
                <div class="filter-section">
                    <div class="filter-group">
                        <h4>Categories</h4>
                        <div class="filter-options">
                            <div class="filter-option">
<<<<<<< HEAD
                                <input type="radio" id="category-all" name="category" value="" <?php echo empty($category_filter) ? 'checked' : ''; ?> onchange="updateFilters()">
=======
                                <input type="radio" id="category-all" name="category" value="" <?php echo empty($category_filter) ? 'checked' : ''; ?>>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                                <label for="category-all">All Categories</label>
                            </div>
                            <?php foreach ($categories as $category): ?>
                            <div class="filter-option">
<<<<<<< HEAD
                                <input type="radio" id="category-<?php echo $category['id']; ?>" name="category" value="<?php echo $category['slug']; ?>" <?php echo $category_filter === $category['slug'] ? 'checked' : ''; ?> onchange="updateFilters()">
=======
                                <input type="radio" id="category-<?php echo $category['id']; ?>" name="category" value="<?php echo $category['slug']; ?>" <?php echo $category_filter === $category['slug'] ? 'checked' : ''; ?>>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
<<<<<<< HEAD
                            <button class="btn btn-sm" onclick="updateFilters()">Apply</button>
=======
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Rating</h4>
                        <div class="filter-options">
<<<<<<< HEAD
                            <?php for ($i = 4; $i >= 1; $i--): ?>
                            <div class="filter-option">
                                <input type="checkbox" id="rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>">
                                <label for="rating-<?php echo $i; ?>">
                                    <?php for ($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star<?php echo $j < $i ? '' : '-o'; ?>"></i>
=======
                            <div class="filter-option">
                                <input type="radio" id="rating-any" name="rating" value="0" <?php echo $min_rating == 0 ? 'checked' : ''; ?>>
                                <label for="rating-any">Any Rating</label>
                            </div>
                            <?php for ($i = 4; $i >= 1; $i--): ?>
                            <div class="filter-option">
                                <input type="radio" id="rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $min_rating == $i ? 'checked' : ''; ?>>
                                <label for="rating-<?php echo $i; ?>">
                                    <?php for ($j = 0; $j < 5; $j++): ?>
                                    <i class="fas fa-star" style="color: <?php echo $j < $i ? '#ffc107' : '#e0e0e0'; ?>"></i>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                                    <?php endfor; ?>
                                    <span>& Up</span>
                                </label>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="filter-actions">
<<<<<<< HEAD
                        <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
=======
                        <button class="btn btn-primary" onclick="applyFilters()">Apply</button>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        <button class="btn btn-outline" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>

            <div class="search-main">
                <div class="search-toolbar">
                    <button class="filter-toggle-btn btn btn-outline">
                        <i class="fas fa-filter"></i> Filters
                    </button>
                    <div class="sort-options">
                        <label for="sort-select">Sort by:</label>
<<<<<<< HEAD
                        <select id="sort-select" onchange="updateSort()">
=======
                        <select id="sort-select" onchange="applyFilters()">
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
<<<<<<< HEAD
                    <div class="search-results grid-view">
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <div class="product-image-container">
                                        <img src="../assets/images/products/<?php echo !empty($product['images']) ? explode(',', $product['images'])[0] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image" loading="lazy" decoding="async">
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
=======
                    <div class="products-grid-view" id="products-view">
                        <div class="products-grid grid-view">
                            <?php foreach ($products as $product) {
                                // Wishlist check for the product card
                                $is_in_wishlist = isset($wishlist_ids) && in_array($product['id'], $wishlist_ids);
                                include '../includes/product-card.php';
                            } ?>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?php echo getPageLink($page - 1); ?>" class="pagination-item">&laquo; Previous</a>
<<<<<<< HEAD
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

=======
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <a href="<?php echo getPageLink($i); ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                    <span class="pagination-item disabled">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
                        <a href="<?php echo BASE_URL; ?>pages/products.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="sidebar-overlay"></div>

<<<<<<< HEAD
=======
<!-- Quick View Modal -->
<div class="modal quick-view-modal" id="quickViewModal">
    <div class="modal-content">
        <button class="modal-close" id="quickViewClose">&times;</button>
        <div class="quick-view-gallery">
            <div class="quick-view-main-image">
                <img src="" alt="Product Image" id="quickViewMainImage">
            </div>
            <div class="quick-view-thumbnails" id="quickViewThumbnails">
                <!-- Thumbnails will be populated by JS -->
            </div>
        </div>
        <div class="quick-view-details">
            <h2 id="quickViewProductName"></h2>
            <p class="quick-view-vendor">Sold by: <a href="#" id="quickViewVendorLink"></a></p>
            <div class="quick-view-price" id="quickViewPrice"></div>
            <div class="quick-view-description" id="quickViewDescription"></div>
            <div class="quick-view-actions">
                <form id="quickViewAddToCartForm">
                    <input type="hidden" name="product_id" id="quickViewProductId">
                    <div class="form-group" style="display: flex; gap: 1rem; align-items: center;">
                        <label for="quickViewQuantity">Quantity:</label>
                        <input type="number" name="quantity" id="quickViewQuantity" value="1" min="1" class="form-control" style="width: 80px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Add to Cart</button>
                </form>
                <a href="#" class="btn btn-outline" id="quickViewFullDetailsLink" style="text-align: center;">View Full Details</a>
            </div>
        </div>
    </div>
</div>

>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
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
    // Sidebar toggle
    const filterToggle = document.querySelector('.filter-toggle-btn');
    const sidebar = document.querySelector('.search-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const sidebarClose = document.querySelector('.sidebar-close');

    if (filterToggle && sidebar && overlay && sidebarClose) {
        filterToggle.addEventListener('click', function() {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        });

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        }

        sidebarClose.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    }

    // View toggle
    const viewOptions = document.querySelectorAll('.view-option');
<<<<<<< HEAD
    const searchResults = document.querySelector('.search-results');
=======
    const productsGrid = document.querySelector('.products-grid');
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    
    viewOptions.forEach(option => {
        option.addEventListener('click', function() {
            viewOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
<<<<<<< HEAD
            
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
=======
            if (productsGrid) {
                productsGrid.className = 'products-grid ' + this.dataset.view + '-view';
            }
        });
    });

    // Quick View Modal
    const quickViewModal = document.getElementById('quickViewModal');
    if (quickViewModal) {
        const closeBtn = document.getElementById('quickViewClose');
        
        function openQuickView(productId) {
            quickViewModal.classList.add('show');
            // Add loading state
            quickViewModal.querySelector('.modal-content').classList.add('loading');

            fetch(`${BASE_URL}ajax/get_product_details.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateQuickView(data.product);
                    } else {
                        showNotification(data.message, 'error');
                        closeQuickView();
                    }
                })
                .catch(err => {
                    showNotification('Error fetching product details.', 'error');
                    closeQuickView();
                })
                .finally(() => {
                    quickViewModal.querySelector('.modal-content').classList.remove('loading');
                });
        }

        function closeQuickView() {
            quickViewModal.classList.remove('show');
        }

        function populateQuickView(product) {
            document.getElementById('quickViewProductName').textContent = product.name;
            document.getElementById('quickViewVendorLink').textContent = product.business_name;
            document.getElementById('quickViewVendorLink').href = `${BASE_URL}pages/vendor.php?id=${product.vendor_id}`;
            document.getElementById('quickViewPrice').textContent = '$' + product.price_formatted;
            document.getElementById('quickViewDescription').innerHTML = product.description;
            document.getElementById('quickViewProductId').value = product.id;
            document.getElementById('quickViewFullDetailsLink').href = `${BASE_URL}pages/product-detail.php?id=${product.id}`;
            
            const mainImage = document.getElementById('quickViewMainImage');
            const thumbnailsContainer = document.getElementById('quickViewThumbnails');
            
            mainImage.src = `${BASE_URL}assets/images/products/${product.images[0]}`;
            thumbnailsContainer.innerHTML = '';
            product.images.forEach((img, index) => {
                const thumbDiv = document.createElement('div');
                thumbDiv.className = 'quick-view-thumb' + (index === 0 ? ' active' : '');
                thumbDiv.innerHTML = `<img src="${BASE_URL}assets/images/products/${img}" alt="Thumbnail">`;
                thumbDiv.addEventListener('click', function() {
                    mainImage.src = `${BASE_URL}assets/images/products/${img}`;
                    document.querySelectorAll('.quick-view-thumb').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
                thumbnailsContainer.appendChild(thumbDiv);
            });
        }

        document.body.addEventListener('click', function(e) {
            const quickViewBtn = e.target.closest('.btn-quick-view');
            if (quickViewBtn) {
                e.preventDefault();
                openQuickView(quickViewBtn.dataset.productId);
            }
        });

        closeBtn.addEventListener('click', closeQuickView);
        quickViewModal.addEventListener('click', function(e) {
            if (e.target === quickViewModal) {
                closeQuickView();
            }
        });
    }
});

function applyFilters() {
    const baseUrl = 'search.php';
    const params = new URLSearchParams();
    
    // Use a hidden input or JS variable for the search query if it's not in the URL
    const searchInput = document.querySelector('.search-input');
    const query = searchInput ? searchInput.value : "<?php echo urlencode($search_query); ?>";
    if (query) {
        params.append('q', query);
    }

    const category = document.querySelector('input[name="category"]:checked').value;
    if (category) params.append('category', category);

    const minPrice = document.getElementById('min_price').value;
    if (minPrice) params.append('min_price', minPrice);

    const maxPrice = document.getElementById('max_price').value;
    if (maxPrice) params.append('max_price', maxPrice);

    const rating = document.querySelector('input[name="rating"]:checked').value;
    if (rating && rating !== '0') params.append('rating', rating);

    const sortBy = document.getElementById('sort-select').value;
    if (sortBy !== 'relevance') params.append('sort', sortBy);

    window.location.href = `${baseUrl}?${params.toString()}`;
}

function resetFilters() {
    const searchInput = document.querySelector('.search-input');
    const query = searchInput ? searchInput.value : "<?php echo urlencode($search_query); ?>";
    if (query) {
        window.location.href = `search.php?q=${encodeURIComponent(query)}`;
    } else {
        window.location.href = 'search.php';
    }
}
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
