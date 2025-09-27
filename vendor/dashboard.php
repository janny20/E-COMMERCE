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

// 3. Handle different statuses
if ($vendor_status === 'rejected' || $vendor_status === 'suspended') {
    // If they somehow have a session, destroy it and redirect to login
    session_destroy();
    header('Location: ../pages/login.php?error=' . $vendor_status);
    exit();
}

if ($vendor_status === 'approved') {
    // Fetch dashboard stats for approved vendors
    // Total Products
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE vendor_id = :vendor_id");
    $stmt->execute(['vendor_id' => $vendor_id]);
    $products_count = $stmt->fetchColumn();

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

    // Chart Data: Sales for the last 7 days
    $chart_labels = [];
    $sales_by_day = [];
    $date_format = 'Y-m-d';

    // Initialize last 7 days with 0 sales
    for ($i = 6; $i >= 0; $i--) {
        $date = (new DateTime())->sub(new DateInterval("P{$i}D"));
        $chart_labels[] = $date->format('D, M j'); // e.g., "Mon, Jan 15"
        $sales_by_day[$date->format($date_format)] = 0;
    }

    // Fetch actual sales data from the last 7 days
    $seven_days_ago = (new DateTime())->sub(new DateInterval('P6D'))->format($date_format . ' 00:00:00');
    $chart_sql = "SELECT DATE(created_at) as sale_date, SUM(net_earning) as daily_total
                  FROM vendor_earnings
                  WHERE vendor_id = :vendor_id AND created_at >= :start_date
                  GROUP BY DATE(created_at)
                  ORDER BY sale_date ASC";
    $chart_stmt = $db->prepare($chart_sql);
    $chart_stmt->execute(['vendor_id' => $vendor_id, 'start_date' => $seven_days_ago]);
    $daily_sales_data = $chart_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Populate the sales array with data from DB
    foreach ($daily_sales_data as $row) {
        if (isset($sales_by_day[$row['sale_date']])) {
            $sales_by_day[$row['sale_date']] = (float)$row['daily_total'];
        }
    }
    $chart_data = array_values($sales_by_day);

// Fetch recent orders for this vendor
$recent_orders_sql = "SELECT o.order_number, o.created_at, o.total_amount, up.first_name, up.last_name
                      FROM orders o
                      JOIN order_items oi ON o.id = oi.order_id
                      LEFT JOIN user_profiles up ON o.customer_id = up.user_id
                      WHERE oi.vendor_id = :vendor_id
                      GROUP BY o.id
                      ORDER BY o.created_at DESC
                      LIMIT 5";
$recent_orders_stmt = $db->prepare($recent_orders_sql);
$recent_orders_stmt->execute(['vendor_id' => $vendor_id]);
$recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);



    
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
                        <p class="stat-number"><?php echo htmlspecialchars($products_count); ?></p>
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

            <div class="content-card chart-card">
                <div class="content-card-header">
                    <h3>Sales Overview (Last 7 Days)</h3>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="quick-actions-container">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="<?php echo BASE_URL; ?>vendor/product-form.php" class="action-btn">
                        <i class="fas fa-plus action-icon"></i>
                        <span class="action-label">Add New Product</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>vendor/orders.php" class="action-btn">
                        <i class="fas fa-receipt action-icon"></i>
                        <span class="action-label">Manage Orders</span>
                    </a>
                </div>
            </div>

            <div class="dashboard-columns">
                <div class="dashboard-column">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Recent Orders</h3>
                            <a href="<?php echo BASE_URL; ?>vendor/orders.php" class="btn btn-sm btn-outline">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_orders)): ?>
                                        <tr><td colspan="4" style="text-align:center;">No recent orders.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                                <td><?php echo htmlspecialchars(trim($order['first_name'] . ' ' . $order['last_name'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td>$<?php echo money($order['total_amount']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="dashboard-column">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h3>Platform News & Updates</h3>
                        </div>
                        <p>Stay tuned for new features and announcements to help you grow your business!</p>
                        <!-- This area can be populated with dynamic content from an admin panel later -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    // --- If status is 'pending' or anything else, show a status page ---
    // This part remains unchanged
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
            <?php else: ?>
                <div class="status-icon"><i class="fas fa-exclamation-circle"></i></div>
                <h1>Account Issue</h1>
                <p>There is an issue with your vendor account. Please contact support for assistance.</p>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>pages/logout.php" class="btn btn-outline">Logout</a>
        </div>
    </div>
    <?php
}
require_once __DIR__ . '/../includes/footer.php';
?>

<!-- Include Chart.js from a CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    const salesData = <?php echo json_encode($chart_data ?? []); ?>;
    const salesLabels = <?php echo json_encode($chart_labels ?? []); ?>;

    // Custom gradient for the line chart background
    const chartGradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
    chartGradient.addColorStop(0, 'rgba(124, 58, 237, 0.3)'); // primary-color with opacity
    chartGradient.addColorStop(1, 'rgba(124, 58, 237, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Net Earnings',
                data: salesData,
                backgroundColor: chartGradient,
                borderColor: 'rgba(124, 58, 237, 1)', // primary-color
                borderWidth: 3,
                pointBackgroundColor: 'rgba(124, 58, 237, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(124, 58, 237, 1)',
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4 // Makes the line smooth
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });
});
</script>