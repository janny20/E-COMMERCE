<link rel="stylesheet" href="../assets/css/pages/admin-users.css">
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$page_title = "Admin Panel Landing";
include_once '../includes/admin-header.php';
?>
<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>Welcome to the Admin Panel</h1>
        <p>Use the sidebar to manage products, vendors, users, orders, and more.</p>
    </div>
    <div class="dashboard-content">
        <div class="card" style="max-width:500px;margin:auto;">
            <div class="card-header">
                <h2>Quick Links</h2>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:16px;">
                <a href="admin-products.php?action=add" class="btn btn-edit">Add Product</a>
                <a href="admin-products.php" class="btn btn-edit">View Products</a>
                <a href="admin-users.php" class="btn btn-edit">Users</a>
                <a href="admin-vendors.php" class="btn btn-edit">Vendors</a>
                <a href="admin-orders.php" class="btn btn-edit">Orders</a>
                <a href="admin-categories.php" class="btn btn-edit">Categories</a>
                <a href="admin-reports.php" class="btn btn-edit">Reports</a>
                <a href="admin-settings.php" class="btn btn-edit">Settings</a>
            </div>
        </div>
    </div>
</div>
<?php include_once '../includes/admin-footer.php'; ?>
