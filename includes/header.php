<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Vendor E-Commerce</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/favicon.svg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav" style="background-color: #232f3e; color: #fff; padding: 10px 0;">
        <div class="container">
            <div class="nav-left">
                <!-- Home link goes to landing page if not logged in, home.php if logged in -->
                <a href="<?php echo $isLoggedIn ? BASE_URL . 'pages/home.php' : BASE_URL . 'landing.php'; ?>" class="logo" style="color: #fff; text-decoration: none; font-weight: 700; font-size: 1.5rem;">UniMall</a>
            </div>
            <div class="nav-center">
                <form class="search-form" action="<?php echo BASE_URL; ?>pages/search.php" method="GET">
                    <input type="text" name="q" placeholder="Search for products..." class="search-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
<<<<<<< HEAD
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    <button type="button" class="search-close-btn" aria-label="Close search"><i class="fas fa-times"></i></button>
                </form>
            </div>
            <div class="nav-right">
                <div class="desktop-nav-links">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a>
                        <?php if(isset($userType) && strtolower($userType) === 'vendor'): ?>
                            <a href="<?php echo BASE_URL; ?>vendor/dashboard.php" class="nav-icon"><i class="fas fa-store"></i> Vendor Dashboard</a>
                        <?php elseif(isset($userType) && strtolower($userType) === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>admin/admin-dashboard.php" class="nav-icon"><i class="fas fa-cog"></i> Admin Panel</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-icon"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <!-- Cart button for guests links to login -->
                        <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link"><i class="fas fa-shopping-cart"></i> Cart (0)</a>
                        <a href="<?php echo BASE_URL; ?>login.php" class="nav-icon"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="<?php echo BASE_URL; ?>register.php" class="nav-icon"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>

                <?php if ($isLoggedIn): ?>
                    <?php
                        // Determine profile link based on user type
                        $profile_link = BASE_URL . 'pages/profile.php';
                        if (isset($userType)) {
                            if (strtolower($userType) === 'vendor') {
                                $profile_link = BASE_URL . 'vendor/profile.php';
                            } elseif (strtolower($userType) === 'admin') {
                                $profile_link = BASE_URL . 'admin/admin-dashboard.php';
                            }
                        }
                    ?>
                    <a href="<?php echo $profile_link; ?>" class="nav-icon nav-profile-link" title="My Profile">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?php echo BASE_URL . 'uploads/users/' . htmlspecialchars($_SESSION['avatar']); ?>" alt="Profile" class="header-avatar">
                        <?php else: ?>
                            <div class="header-avatar-placeholder">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </a>
=======
                    <button type="submit" class="search-btn" style="background-color: var(--primary-color);"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <?php if(isset($userType) && strtolower($userType) === 'vendor'): ?>
                        <a href="<?php echo BASE_URL; ?>vendor/profile.php" class="nav-icon" style="color: #fff;"><i class="fas fa-user"></i> <?php echo $username; ?></a>
                    <?php elseif(isset($userType) && strtolower($userType) === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/admin-dashboard.php" class="nav-icon" style="color: #fff;"><i class="fas fa-user"></i> <?php echo $username; ?></a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-icon" style="color: #fff;"><i class="fas fa-user"></i> <?php echo $username; ?></a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link" style="color: #fff;"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a>
                    <?php if(isset($userType) && strtolower($userType) === 'vendor'): ?>
                        <a href="<?php echo BASE_URL; ?>vendor/dashboard.php" class="nav-icon" style="color: #fff;"><i class="fas fa-store"></i> Vendor Dashboard</a>
                    <?php elseif(isset($userType) && strtolower($userType) === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/admin-dashboard.php" class="nav-icon" style="color: #fff;"><i class="fas fa-cog"></i> Admin Panel</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-icon" style="color: #fff;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <!-- Cart button for guests links to login -->
                    <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link" style="color: #fff;"><i class="fas fa-shopping-cart"></i> Cart (0)</a>
                    <a href="<?php echo BASE_URL; ?>pages/login.php" class="nav-icon" style="color: #fff;"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?php echo BASE_URL; ?>pages/register.php" class="nav-icon" style="color: #fff;"><i class="fas fa-user-plus"></i> Register</a>
>>>>>>> 1f7d65395f4b56b49792a2c435502977a4ad4867
                <?php endif; ?>

                <div class="mobile-toggles">
                    <button class="mobile-search-toggle" aria-label="Toggle search">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="mobile-nav-toggle" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

<<<<<<< HEAD
    <!-- Mobile Navigation -->
    <div class="mobile-nav-container" role="dialog" aria-modal="true" aria-labelledby="mobile-nav-title">
        <div class="mobile-nav-header">
            <h2 id="mobile-nav-title" class="visually-hidden">Main Menu</h2>
            <a href="<?php echo $isLoggedIn ? BASE_URL . 'pages/home.php' : BASE_URL . 'landing.php'; ?>" class="logo">UniMall</a>
            <button class="mobile-nav-close" aria-label="Close navigation">
                <i class="fas fa-times"></i>
            </button>
=======
    <!-- Main Navigation -->
    <nav class="main-nav" style="background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div class="container">
            <!-- Customer-specific nav is loaded in page files like home.php -->
>>>>>>> 1f7d65395f4b56b49792a2c435502977a4ad4867
        </div>
        <ul class="mobile-nav-menu">
            <?php if($isLoggedIn): ?>
                <li><a href="<?php echo $profile_link; ?>" class="nav-icon"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon cart-link"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a></li>
                <?php if(isset($userType) && strtolower($userType) === 'vendor'): ?>
                    <li><a href="<?php echo BASE_URL; ?>vendor/dashboard.php" class="nav-icon"><i class="fas fa-store"></i> Vendor Dashboard</a></li>
                <?php elseif(isset($userType) && strtolower($userType) === 'admin'): ?>
                    <li><a href="<?php echo BASE_URL; ?>admin/admin-dashboard.php" class="nav-icon"><i class="fas fa-cog"></i> Admin Panel</a></li>
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

    <style>
        .top-nav .nav-icon:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
    </style>

    <!-- Main Content -->
    <main class="main-content">