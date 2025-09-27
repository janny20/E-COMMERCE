<?php
// Start session only if not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once 'config.php';

// --- Global Variables ---
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Guest';
$userType = $_SESSION['user_type'] ?? 'guest';
$cartCount = 0;

// --- Page Type Detection ---
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
$isVendorPage = strpos($_SERVER['REQUEST_URI'], '/vendor/') !== false;
$isCustomerFacing = !$isAdminPage && !$isVendorPage;

// --- Database Dependent Variables ---
$nav_categories = []; // For customer nav

// Only connect to DB if necessary
if ($isLoggedIn || $isCustomerFacing) {
    try {
        $db_header = new Database();
        $conn_header = $db_header->getConnection();

        if ($isLoggedIn && $userType === 'customer') {
            $cartCount = getCartTotalItems($conn_header, $userId);
        }

        if ($isCustomerFacing) {
            $categories_query = "SELECT name, slug FROM categories WHERE parent_id IS NULL ORDER BY name";
            $categories_stmt = $conn_header->prepare($categories_query);
            $categories_stmt->execute();
            $nav_categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // Gracefully handle DB connection errors in the header
        error_log("Header DB Error: " . $e->getMessage());
    }
}

// --- URL Generation ---
$home_link = BASE_URL . 'landing.php';
if ($isLoggedIn) {
    switch ($userType) {
        case 'admin': $home_link = BASE_URL . 'admin/admin-dashboard.php'; break;
        // For vendors and customers, the logo should link to the main homepage.
        // Vendors can access their dashboard via the navigation links.
        case 'vendor':
        case 'customer':
        default: 
            $home_link = BASE_URL . 'pages/home.php'; break;
    }
}

$profile_link = BASE_URL . 'pages/login.php';
if ($isLoggedIn) {
    switch ($userType) {
        case 'admin': $profile_link = BASE_URL . 'admin/admin-profile.php'; break;
        case 'vendor': $profile_link = BASE_URL . 'vendor/profile.php'; break;
        default: $profile_link = BASE_URL . 'pages/profile.php'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Vendor E-Commerce</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/images/favicon.svg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS -->
    <!-- Base Styles (Order is important) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/base/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/base/reset.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/base/typography.css">

    <!-- Global Styles (formerly contained @imports) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <!-- Components -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/buttons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/cards.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/forms.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/modals.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/navigation.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/wishlist.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/preloader.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/footer.css">

    <!-- Layout -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout/grid.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout/sidebar.css">
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js" defer></script>
</head>
<body class="<?php echo $isAdminPage ? 'admin-body' : ''; ?>">
    <!-- Page Preloader -->
    <div id="preloader">
        <div class="spinner"></div>
    </div>

    <?php if ($isAdminPage): ?>
    <!-- Admin Specific Header -->
    <div class="admin-topnav">
        <div class="topnav-left">
            <button class="sidebar-toggle" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h1><?php echo $page_title ?? 'Admin Panel'; ?></h1>
        </div>
        <div class="topnav-right">
            <a href="<?php echo BASE_URL; ?>" target="_blank" class="btn btn-sm btn-outline" title="View Live Site"><i class="fas fa-eye"></i> View Site</a>
            <div class="admin-user">
                <a href="<?php echo $profile_link; ?>" class="nav-profile-link">
                    <div class="user-avatar">
                        <?php echo !empty($username) ? strtoupper(substr($username, 0, 1)) : '?'; ?>
                    </div>
                    <span><?php echo htmlspecialchars($username); ?></span>
                </a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="container">
            <div class="nav-left">
                <!-- Home link goes to landing page if not logged in, home.php if logged in -->
                <a href="<?php echo $home_link; ?>" class="logo">UniMall</a>
            </div>
            <div class="nav-center">
                <form class="search-form" action="<?php echo BASE_URL; ?>pages/search.php" method="GET">
                    <input type="text" name="q" placeholder="Search for products..." class="search-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit" class="search-btn" aria-label="Search"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="nav-right">
                <div class="desktop-nav-links">
                    <?php if ($isLoggedIn): ?>
                        <?php if($userType === 'customer'): ?>
                            <a href="<?php echo BASE_URL; ?>pages/wishlist.php" class="nav-icon" title="My Wishlist"><i class="far fa-heart"></i></a>
                            <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link" title="Shopping Cart"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a>
                        <?php elseif($userType === 'vendor'): ?>
                            <a href="<?php echo BASE_URL; ?>vendor/dashboard.php" class="nav-icon" title="Vendor Dashboard"><i class="fas fa-store"></i> Vendor Dashboard</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Cart button for guests links to login -->
                        <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link" title="Shopping Cart"><i class="fas fa-shopping-cart"></i> Cart (0)</a>
                        <a href="<?php echo BASE_URL; ?>pages/login.php" class="btn btn-sm btn-outline">Login</a>
                        <a href="<?php echo BASE_URL; ?>pages/register.php" class="btn btn-sm btn-primary">Register</a>
                    <?php endif; ?>
                </div>

                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $profile_link; ?>" class="nav-icon nav-profile-link" title="My Profile">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <?php
                                $avatar_path = $userType === 'vendor'
                                    ? BASE_URL . 'uploads/vendors/' . htmlspecialchars($_SESSION['avatar'])
                                    : BASE_URL . 'uploads/users/' . htmlspecialchars($_SESSION['avatar']);
                            ?>
                            <img src="<?php echo $avatar_path; ?>" alt="Profile Avatar" class="header-avatar">
                        <?php else: ?>
                            <div class="header-avatar-placeholder">
                                <?php echo !empty($username) && $username !== 'Guest' ? strtoupper(substr($username, 0, 1)) : '?'; ?>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <div class="mobile-toggles">
                    <button class="mobile-search-toggle" aria-label="Toggle search">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="mobile-nav-toggle" aria-label="Toggle navigation" aria-controls="mobile-nav-menu" aria-expanded="false">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <?php if ($isCustomerFacing): ?>
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li class="<?php echo (basename($_SERVER['PHP_SELF']) === 'home.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pages/home.php">Home</a>
                </li>
                <li class="<?php echo (basename($_SERVER['PHP_SELF']) === 'products.php' && empty($_GET['category'])) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a>
                </li>
                <li class="dropdown <?php echo (!empty($_GET['category'])) ? 'active' : ''; ?>">
                    <a href="#">Categories <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-content">
                        <?php foreach ($nav_categories as $category): ?>
                            <a href="<?php echo BASE_URL . 'pages/products.php?category=' . htmlspecialchars($category['slug']); ?>"
                               class="<?php echo (($_GET['category'] ?? '') === $category['slug']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li><a href="#">Today's Deals</a></li>
                <li><a href="<?php echo BASE_URL; ?>pages/register.php?type=vendor" class="vendor-link">Become a Vendor</a></li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <?php if ($isVendorPage): ?>
    <nav class="vendor-nav">
        <div class="container">
            <ul class="nav-menu">
                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'vendor/dashboard.php') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>vendor/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'vendor/products.php') !== false || strpos($_SERVER['REQUEST_URI'], 'vendor/product-form.php') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>vendor/products.php"><i class="fas fa-box-open"></i> Products</a>
                </li>
                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'vendor/orders.php') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>vendor/orders.php"><i class="fas fa-receipt"></i> Orders</a>
                </li>
                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'vendor/earnings.php') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>vendor/earnings.php"><i class="fas fa-dollar-sign"></i> Earnings</a>
                </li>
                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'vendor/profile.php') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>vendor/profile.php"><i class="fas fa-user-cog"></i> Store Profile</a>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Mobile Navigation -->
    <div class="mobile-nav-container" id="mobile-nav-menu">
        <div class="mobile-nav-header">
            <a href="<?php echo $home_link; ?>" class="logo">UniMall</a>
            <button class="mobile-nav-close" aria-label="Close navigation"><i class="fas fa-times"></i></button>
        </div>
        <ul class="mobile-nav-menu">
            <?php if($isLoggedIn): ?>
                <li><a href="<?php echo $profile_link; ?>" class="nav-icon"><i class="fas fa-user"></i> Profile</a></li>
                <?php if($userType === 'customer'): ?>
                    <li><a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a></li>
                    <li><a href="<?php echo BASE_URL; ?>pages/orders.php" class="nav-icon"><i class="fas fa-box"></i> My Orders</a></li>
                    <li><a href="<?php echo BASE_URL; ?>pages/wishlist.php" class="nav-icon"><i class="fas fa-heart"></i> Wishlist</a></li>
                <?php elseif($userType === 'vendor'): ?>
                    <li><a href="<?php echo BASE_URL; ?>vendor/dashboard.php" class="nav-icon"><i class="fas fa-store"></i> Vendor Dashboard</a></li>
                <?php endif; ?>
                <hr>
                <li><a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a></li>
                <li><a href="#">Today's Deals</a></li>
                <hr>
                <li><a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-icon"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>pages/login.php" class="nav-icon"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>pages/register.php" class="nav-icon"><i class="fas fa-user-plus"></i> Register</a></li>
                <hr>
                <li><a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a></li>
                <li><a href="#">Today's Deals</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="nav-overlay"></div>

    <!-- Main Content -->
    <main class="main-content">