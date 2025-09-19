<?php
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
    $query = "SELECT COUNT(*) as total_users FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Total products
    $query = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Total orders
    $query = "SELECT COUNT(*) as total_orders FROM orders";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

    // Total vendors
    $query = "SELECT COUNT(*) as total_vendors FROM vendors WHERE status = 'approved'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_vendors'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_vendors'];

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
        <h1>Admin Dashboard</h1>
        <p>Welcome back, <?php echo $_SESSION['username']; ?>!</p>
        <a href="admin-profile.php" class="btn btn-admin-profile" style="margin-top:10px;display:inline-block;">
            <i class="fas fa-user"></i> View Admin Profile
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Total Users</p>
            </div>
            <a href="admin-users.php" class="stat-link">View All</a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Total Products</p>
            </div>
            <a href="admin-products.php" class="stat-link">View All</a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total_orders']; ?></h3>
                <p>Total Orders</p>
            </div>
            <a href="admin-orders.php" class="stat-link">View All</a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total_vendors']; ?></h3>
                <p>Approved Vendors</p>
            </div>
            <a href="admin-vendors.php" class="stat-link">View All</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-column">
            <div class="card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="admin-orders.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_orders)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
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
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
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
                    <h2>Pending Vendor Approvals</h2>
                    <a href="admin-vendors.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pending_vendors)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Business Name</th>
                                    <th>Owner</th>
                                    <th>Email</th>
                                    <th>Applied On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_vendors as $vendor): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($vendor['business_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($vendor['username']); ?></td>
                                        <td><?php echo htmlspecialchars($vendor['email']); ?></td>
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
</div>

<?php
// Include admin footer
include_once __DIR__ . '/../includes/admin-footer.php';
?>