<?php
// vendor/dashboard.php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../pages/login.php');
    exit();
}

// Include config
require_once '../includes/config.php';

// Get vendor data
$database = new Database();
$db = $database->getConnection();

$query = "SELECT v.*, u.email 
          FROM vendors v 
          JOIN users u ON v.user_id = u.id 
          WHERE v.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);

// Get vendor stats
$stats = [
    'total_products' => 0,
    'total_orders' => 0,
    'total_earnings' => 0,
    'pending_orders' => 0
];

// Total products
$query = "SELECT COUNT(*) as count FROM products WHERE vendor_id = :vendor_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor['id'], PDO::PARAM_INT);
$stmt->execute();
$stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total orders
$query = "SELECT COUNT(DISTINCT oi.order_id) as count 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE p.vendor_id = :vendor_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor['id'], PDO::PARAM_INT);
$stmt->execute();
$stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total earnings
$query = "SELECT SUM(oi.total) as total 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE p.vendor_id = :vendor_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor['id'], PDO::PARAM_INT);
$stmt->execute();
$stats['total_earnings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Pending orders
$query = "SELECT COUNT(DISTINCT oi.order_id) as count 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          JOIN orders o ON oi.order_id = o.id 
          WHERE p.vendor_id = :vendor_id AND o.status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor['id'], PDO::PARAM_INT);
$stmt->execute();
$stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Include header
require_once '../includes/header.php';
?>

<div class="vendor-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Vendor Dashboard</h1>
            <p class="dashboard-subtitle">Welcome back, <?php echo htmlspecialchars($vendor['business_name']); ?>!</p>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number">$<?php echo number_format($stats['total_earnings'], 2); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="products.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="action-label">Manage Products</div>
            </a>
            
            <a href="orders.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="action-label">View Orders</div>
            </a>
            
            <a href="earnings.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="action-label">Earnings Report</div>
            </a>
            
            <a href="profile.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="action-label">Vendor Profile</div>
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>