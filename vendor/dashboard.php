<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// 1. Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../pages/login.php?error=auth');
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Get the vendor's current status from the database
$database = new Database();
$db = $database->getConnection();

$query = "SELECT status FROM vendors WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);

$vendor_status = $vendor ? $vendor['status'] : 'not_found';

// --- If status is 'approved', show the dashboard ---
if ($vendor_status === 'approved') {
    // Fetch dashboard stats for approved vendors
    // Total Products
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE vendor_id = (SELECT id FROM vendors WHERE user_id = :user_id)");
    $stmt->execute(['user_id' => $user_id]);
    $total_products = $stmt->fetchColumn();

    // Total Orders (for this vendor)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.vendor_id = (SELECT id FROM vendors WHERE user_id = :user_id)");
    $stmt->execute(['user_id' => $user_id]);
    $total_orders = $stmt->fetchColumn();

    // Total Sales (for this vendor)
    $stmt = $db->prepare("SELECT SUM(oi.total) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.vendor_id = (SELECT id FROM vendors WHERE user_id = :user_id)");
    $stmt->execute(['user_id' => $user_id]);
    $total_sales = $stmt->fetchColumn() ?? 0;

    require_once '../includes/header.php';
    echo '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/pages/vendor-dashboard.css">';
    ?>
    <div class="vendor-dashboard container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Here's a summary of your store's activity.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Sales</h3>
                <p>$<?php echo number_format($total_sales, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><?php echo $total_orders; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?php echo $total_products; ?></p>
            </div>
        </div>
        <div class="quick-links">
            <a href="products.php" class="btn btn-primary">Manage Products</a>
            <a href="orders.php" class="btn btn-primary">View Orders</a>
            <a href="profile.php" class="btn btn-outline">Edit Profile</a>
        </div>
    </div>
    <?php
    require_once '../includes/footer.php';
    exit();
}

// --- If status is 'pending' or anything else, show a status page ---
require_once '../includes/header.php';
?>
<style>
    .status-page { text-align: center; padding: 80px 20px; }
    .status-page h1 { font-size: 2.5rem; margin-bottom: 1rem; }
    .status-page p { font-size: 1.2rem; color: #666; margin-bottom: 2rem; }
    .status-icon { font-size: 5rem; color: var(--primary-color); margin-bottom: 2rem; }
</style>
<div class="container">
    <div class="status-page">
        <?php if ($vendor_status === 'pending'): ?>
            <div class="status-icon"><i class="fas fa-hourglass-half"></i></div>
            <h1>Application Pending</h1>
            <p>Your vendor application is currently under review. We will notify you by email once a decision has been made. Thank you for your patience.</p>
        <?php elseif ($vendor_status === 'rejected'): ?>
            <div class="status-icon" style="color: var(--danger-color);"><i class="fas fa-times-circle"></i></div>
            <h1>Application Rejected</h1>
            <p>We regret to inform you that your vendor application has been rejected. Please contact support if you believe this is an error.</p>
        <?php elseif ($vendor_status === 'suspended'): ?>
            <div class="status-icon" style="color: var(--warning-color);"><i class="fas fa-ban"></i></div>
            <h1>Account Suspended</h1>
            <p>Your vendor account has been suspended. You are unable to access your dashboard or sell products. Please contact support for more information.</p>
        <?php else: ?>
            <div class="status-icon"><i class="fas fa-exclamation-circle"></i></div>
            <h1>Account Issue</h1>
            <p>There is an issue with your vendor account. Please contact support for assistance.</p>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>pages/logout.php" class="btn btn-outline">Logout</a>
    </div>
</div>
<?php
require_once '../includes/footer.php';
?>