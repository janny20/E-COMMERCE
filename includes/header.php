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
    <nav class="top-nav">
        <div class="container">
            <div class="nav-left">
                <!-- Home link goes to landing page if not logged in, home.php if logged in -->
                <a href="<?php echo $isLoggedIn ? BASE_URL . 'pages/home.php' : BASE_URL . 'landing.php'; ?>" class="logo">E-Shop</a>
            </div>
            <div class="nav-center">
                <form class="search-form" action="<?php echo BASE_URL; ?>pages/search.php" method="GET">
                    <input type="text" name="q" placeholder="Search for products..." class="search-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
<<<<<<< HEAD
                    <?php if(isset($userType) && strtolower($userType) === 'vendor'): ?>
                        <a href="<?php echo BASE_URL; ?>vendor/profile.php" class="nav-icon"><i class="fas fa-user"></i> <?php echo $username; ?></a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-icon"><i class="fas fa-user"></i> <?php echo $username; ?></a>
=======
                    <?php if($userType == 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/admin-dashboard.php" class="nav-icon"><i class="fas fa-user"></i> <?php echo $username; ?></a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>pages/home.php" class="nav-icon"><i class="fas fa-user"></i> <?php echo $username; ?></a>
>>>>>>> ebb47525a15ab02a6b62127f98182234ea4ee14f
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>pages/cart.php" class="nav-icon"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cartCount; ?>)</a>
                    <?php if($userType == 'vendor'): ?>
                        <a href="<?php echo BASE_URL; ?>vendor/dashboard.php" class="nav-icon"><i class="fas fa-store"></i> Vendor Dashboard</a>
                    <?php elseif($userType == 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>pages/home.php" class="nav-icon"><i class="fas fa-cog"></i> Admin Panel</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-icon"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <!-- Login/Register links point to root directory files -->
                    <a href="<?php echo BASE_URL; ?>login.php" class="nav-icon"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="nav-icon"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <!-- Home link goes to landing page if not logged in, home.php if logged in -->
                <li><a href="<?php echo $isLoggedIn ? BASE_URL . 'pages/home.php' : BASE_URL . 'landing.php'; ?>">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a></li>
                <li class="dropdown">
                    <a href="#">Categories <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-content">
                        <?php
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        $query = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<a href="' . BASE_URL . 'pages/products.php?category=' . $row['slug'] . '">' . $row['name'] . '</a>';
                        }
                        ?>
                    </div>
                </li>
                <li><a href="#">Today's Deals</a></li>
                <!-- Become a Vendor link points to root register.php -->
                <li><a href="<?php echo BASE_URL; ?>register.php?type=vendor">Become a Vendor</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">