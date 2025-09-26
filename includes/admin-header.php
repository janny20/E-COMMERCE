<?php
// Admin header file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/pages/admin-dashboard.css">
    <link rel="stylesheet" href="../../assets/css/pages/admin-profile.css">
    <link rel="stylesheet" href="../../assets/css/pages/admin-header.css">
    <link rel="stylesheet" href="../../assets/css/pages/admin-login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <div class="sidebar-overlay"></div>
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 style="color:#fff !important;">Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">
                        <a href="admin-dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-products.php' ? 'active' : ''; ?>">
                        <a href="admin-products.php" class="nav-link"><i class="fas fa-box"></i><span>Products</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-categories.php' ? 'active' : ''; ?>">
                        <a href="admin-categories.php" class="nav-link"><i class="fas fa-tags"></i><span>Categories</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-orders.php' ? 'active' : ''; ?>">
                        <a href="admin-orders.php" class="nav-link"><i class="fas fa-shopping-cart"></i><span>Orders</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-vendors.php' ? 'active' : ''; ?>">
                        <a href="admin-vendors.php" class="nav-link"><i class="fas fa-store"></i><span>Vendors</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-users.php' ? 'active' : ''; ?>">
                        <a href="admin-users.php" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-reports.php' ? 'active' : ''; ?>">
                        <a href="admin-reports.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-settings.php' ? 'active' : ''; ?>">
                        <a href="admin-settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-profile.php' ? 'active' : ''; ?>">
                        <a href="admin-profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a>
                    </li>
                    <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-logout.php' ? 'active' : ''; ?>">
                        <a href="admin-logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="admin-main">
            <!-- Top navigation -->
            <header class="admin-topnav">
                <div class="topnav-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>
                <div class="topnav-right">
                    <div class="admin-user">
                        <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </header>