<?php
// admin-dashboard.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get statistics for dashboard
$stats = [];
try {
    // Total users
    $stats['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    // Total products
    $stats['total_products'] = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    // Total orders
    $stats['total_orders'] = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    // Total vendors
    $stats['total_vendors'] = $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'approved'")->fetchColumn();
    // Pending vendors
    $stats['pending_vendors'] = $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'pending'")->fetchColumn();
    // Total sales
    $stats['total_sales'] = $db->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn() ?? 0;

    // Recent orders
    $query = "SELECT o.*, u.username 
              FROM orders o 
              JOIN users u ON o.customer_id = u.id 
              ORDER BY o.created_at DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pending vendors
    $query = "SELECT v.*, u.username, u.email 
              FROM vendors v 
              JOIN users u ON v.user_id = u.id 
              WHERE v.status = 'pending' 
              ORDER BY v.created_at DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pending_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Set page title
$page_title = "Dashboard";

// Include admin header
echo '<link rel="stylesheet" href="../assets/css/pages/admin-dashboard.css">';
include_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Here's a quick overview of your platform's activity.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <section class="dashboard-section">
        <h2 class="section-title">Platform Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['total_sales'], 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
                <a href="admin-reports.php" class="stat-link"><i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p>Total Orders</p>
                </div>
                <a href="admin-orders.php" class="stat-link"><i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
                <a href="admin-users.php" class="stat-link">View All</a>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-store"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_vendors']; ?></h3>
                    <p>Approved Vendors</p>
                </div>
                <a href="admin-vendors.php" class="stat-link"><i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-columns">
            <div class="dashboard-column">
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Orders</h2>
                        <a href="admin-orders.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($recent_orders) && !empty($recent_orders)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                                            <td><?php echo $order['username']; ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center">No recent orders found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="dashboard-column">
                <div class="card">
                    <div class="card-header">
                        <h2>Pending Vendor Approvals (<?php echo $stats['pending_vendors']; ?>)</h2>
                        <a href="admin-vendors.php?status=pending" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($pending_vendors) && !empty($pending_vendors)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Business Name</th>
                                        <th>Applied On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_vendors as $vendor): ?>
                                        <tr id="vendor-row-<?php echo $vendor['id']; ?>">
                                            <td><strong><?php echo htmlspecialchars($vendor['business_name']); ?></strong></td>
                                            <td><?php echo date('M j, Y', strtotime($vendor['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="admin-vendors.php?action=approve&id=<?php echo $vendor['id']; ?>" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="admin-vendors.php?action=reject&id=<?php echo $vendor['id']; ?>" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center">No pending vendor approvals.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Include admin footer
include_once __DIR__ . '/../includes/admin-footer.php';
?>