<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$vendor_id = $_SESSION['vendor_id']; // Assuming this is set on login

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
    $stmt = $db->prepare("SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi WHERE oi.vendor_id = :vendor_id");
    $stmt->execute(['vendor_id' => $vendor_id]);
    $orders_count = $stmt->fetchColumn();

// Pending Items (items in orders that are 'processing' or 'confirmed')
    $stmt = $db->prepare("SELECT COUNT(*) FROM order_items WHERE vendor_id = :vendor_id AND status IN ('processing', 'confirmed')");
    $stmt->execute(['vendor_id' => $vendor_id]);
    $pending_count = $stmt->fetchColumn();

// Total Earnings
    $stmt = $db->prepare("SELECT COALESCE(SUM(net_earning), 0) FROM vendor_earnings WHERE vendor_id = :vendor_id");
    $stmt->execute(['vendor_id' => $vendor_id]);
    $total_earnings = $stmt->fetchColumn();

$page_title = "Dashboard";
require_once __DIR__ . '/../includes/header.php'; 
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">

<div class="vendor-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Vendor Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Vendor'); ?>!</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box-open"></i></div>
                <div class="stat-info">
                    <p class="stat-number"><?php echo htmlspecialchars($total_products); ?></p>
                    <h3>Total Products</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-info">
                    <p class="stat-number"><?php echo htmlspecialchars($orders_count); ?></p>
                    <h3>Total Orders</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <p class="stat-number"><?php echo htmlspecialchars($pending_count); ?></p>
                    <h3>Pending Items</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <p class="stat-number">$<?php echo money($total_earnings); ?></p>
                    <h3>Total Earnings</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
} else {
    // --- If status is NOT 'approved', show a status page ---
    $page_title = "Account Status";
    require_once __DIR__ . '/../includes/header.php';
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
}
?>