<?php
<<<<<<< HEAD
// vendor/earnings.php
// Start session only if not already started
=======
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../pages/login.php');
    exit();
}

// Include config
require_once '../includes/config.php';

// Get vendor ID
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id FROM vendors WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$vendor = $stmt->fetch(PDO::FETCH_ASSOC);
if ($vendor && isset($vendor['id'])) {
    $vendor_id = $vendor['id'];
} else {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Vendor Earnings</title>';
    echo '<link rel="stylesheet" href="/assets/css/style.css">';
    echo '</head><body>';
    echo '<div class="container" style="margin-top:2rem;">';
    echo '<div class="alert alert-danger" style="padding:2rem;text-align:center;">Vendor account not found. Please contact support.</div>';
    echo '<a href="' . BASE_URL . 'vendor/dashboard.php" class="btn btn-primary" style="margin-top:1rem;">Back to Dashboard</a>';
    echo '</div></body></html>';
    error_log('Vendor earnings page: vendor not found for user_id ' . ($_SESSION['user_id'] ?? 'N/A'));
    exit();
}

// Get earnings data
$timeframe = $_GET['timeframe'] ?? 'month';

// Calculate date range based on timeframe
$date_ranges = [
    'week' => 'DATE_SUB(NOW(), INTERVAL 7 DAY)',
    'month' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)',
    'quarter' => 'DATE_SUB(NOW(), INTERVAL 90 DAY)',
    'year' => 'DATE_SUB(NOW(), INTERVAL 365 DAY)'
];

$date_condition = $date_ranges[$timeframe] ?? $date_ranges['month'];

// Get earnings summary
$query = "SELECT 
            COUNT(DISTINCT oi.order_id) as total_orders,
            SUM(oi.total) as total_earnings,
            SUM(oi.quantity) as total_items_sold,
            AVG(oi.total) as average_order_value
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          JOIN orders o ON oi.order_id = o.id 
          WHERE p.vendor_id = :vendor_id 
          AND o.status = 'delivered'
          AND o.created_at >= $date_condition";

$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
$stmt->execute();
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent transactions
$query = "SELECT oi.*, o.order_number, o.created_at, p.name as product_name
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          JOIN orders o ON oi.order_id = o.id 
          WHERE p.vendor_id = :vendor_id 
          ORDER BY o.created_at DESC 
          LIMIT 10";

$stmt = $db->prepare($query);
$stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
require_once '../includes/header.php';
?>

<div class="vendor-earnings">
    <div class="container">
        <div class="earnings-header">
            <h1>Earnings Report</h1>
            <p>Track your sales and earnings performance</p>
        </div>

        <!-- Timeframe Selector -->
        <div class="timeframe-selector">
            <a href="?timeframe=week" class="<?php echo $timeframe == 'week' ? 'active' : ''; ?>">Week</a>
            <a href="?timeframe=month" class="<?php echo $timeframe == 'month' ? 'active' : ''; ?>">Month</a>
            <a href="?timeframe=quarter" class="<?php echo $timeframe == 'quarter' ? 'active' : ''; ?>">Quarter</a>
            <a href="?timeframe=year" class="<?php echo $timeframe == 'year' ? 'active' : ''; ?>">Year</a>
        </div>

        <!-- Earnings Summary -->
        <div class="earnings-summary">
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="summary-amount"><?php echo $summary['total_orders'] ?? 0; ?></div>
                    <div class="summary-label">Total Orders</div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="summary-amount">$<?php echo number_format($summary['total_earnings'] ?? 0, 2); ?></div>
                    <div class="summary-label">Total Earnings</div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="summary-amount"><?php echo $summary['total_items_sold'] ?? 0; ?></div>
                    <div class="summary-label">Items Sold</div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-amount">$<?php echo number_format($summary['average_order_value'] ?? 0, 2); ?></div>
                    <div class="summary-label">Avg. Order Value</div>
                </div>
            </div>
        </div>

        <!-- Earnings Chart -->
        <div class="earnings-chart">
            <h3 class="chart-title">Earnings Overview</h3>
            <div class="chart-container">
                <canvas id="earningsChart"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="transactions-list">
            <div class="transactions-header">
                <h3>Recent Transactions</h3>
                <div class="export-options">
                    <a href="#" class="export-btn">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <a href="#" class="export-btn">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>

            <div class="transaction-item header">
                <div>Product</div>
                <div>Order #</div>
                <div>Amount</div>
                <div>Date</div>
                <div>Status</div>
            </div>

            <?php foreach ($transactions as $transaction): ?>
            <div class="transaction-item">
                <div class="transaction-type">
                    <div class="type-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <div class="transaction-product"><?php echo htmlspecialchars($transaction['product_name']); ?></div>
                        <div class="transaction-quantity">Qty: <?php echo $transaction['quantity']; ?></div>
                    </div>
                </div>
                
                <div class="transaction-order">#<?php echo $transaction['order_number']; ?></div>
                
                <div class="transaction-amount amount-positive">
                    $<?php echo number_format($transaction['total'], 2); ?>
                </div>
                
                <div class="transaction-date">
                    <?php echo date('M j, Y', strtotime($transaction['created_at'])); ?>
                </div>
                
                <div class="transaction-status status-completed">
                    Completed
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($transactions)): ?>
            <div class="transaction-item empty">
                <div colspan="5" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-receipt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No transactions found</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Payout Information -->
        <div class="payout-info">
            <h3>Payout Information</h3>
            <div class="payout-grid">
                <div class="payout-card">
                    <h4>Next Payout</h4>
                    <div class="payout-amount">$<?php echo number_format(($summary['total_earnings'] ?? 0) * 0.85, 2); ?></div>
                    <p>Estimated payout after platform fees (15%)</p>
                    <div class="payout-date">Scheduled: <?php echo date('M j, Y', strtotime('+7 days')); ?></div>
                </div>
                
                <div class="payout-card">
                    <h4>Payout Method</h4>
                    <div class="payout-method">
                        <i class="fas fa-university"></i>
                        <div>
                            <div>Bank Transfer</div>
                            <div class="payout-details">**** **** **** 1234</div>
                        </div>
                    </div>
                    <a href="#" class="btn btn-outline">Update Method</a>
                </div>
            </div>
=======
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();

$vendor_id = $_SESSION['vendor_id'] ?? null;
if (!$vendor_id) {
    die('Vendor not properly logged in.');
}

$database = new Database();
$db = $database->getConnection();

// Fetch summary stats
$stats_sql = "SELECT 
                COALESCE(SUM(net_earning), 0) as total_earnings,
                COALESCE(SUM(CASE WHEN status = 'pending_clearance' THEN net_earning ELSE 0 END), 0) as pending_clearance,
                COALESCE(SUM(CASE WHEN status = 'paid_out' THEN net_earning ELSE 0 END), 0) as total_paid_out
              FROM vendor_earnings
              WHERE vendor_id = :vendor_id";
$stats_stmt = $db->prepare($stats_sql);
$stats_stmt->execute(['vendor_id' => $vendor_id]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch detailed earnings records
$earnings_sql = "SELECT 
                    ve.*, 
                    o.order_number, 
                    p.name as product_name
                 FROM vendor_earnings ve
                 JOIN orders o ON ve.order_id = o.id
                 JOIN order_items oi ON ve.order_item_id = oi.id
                 JOIN products p ON oi.product_id = p.id
                 WHERE ve.vendor_id = :vendor_id
                 ORDER BY ve.created_at DESC";
$earnings_stmt = $db->prepare($earnings_sql);
$earnings_stmt->execute(['vendor_id' => $vendor_id]);
$earnings = $earnings_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-dashboard.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/vendor-earnings.css">

<div class="vendor-dashboard container">
    <div class="dashboard-header">
        <h1>My Earnings</h1>
        <p>Track your sales, commissions, and payouts.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Net Earnings</h3>
            <p class="stat-number">$<?php echo money($stats['total_earnings']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending Clearance</h3>
            <p class="stat-number">$<?php echo money($stats['pending_clearance']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Paid Out</h3>
            <p class="stat-number">$<?php echo money($stats['total_paid_out']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Request Payout</h3>
            <button class="btn btn-primary" style="margin-top: 1rem;">Request Payout</button>
        </div>
    </div>

    <div class="content-card">
        <h3 class="content-card-header">Earnings History</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order #</th>
                        <th>Product</th>
                        <th>Item Price</th>
                        <th>Commission</th>
                        <th>Net Earning</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($earnings)): ?>
                        <tr><td colspan="7" style="text-align:center;">You have no earnings yet.</td></tr>
                    <?php else: foreach ($earnings as $earning): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($earning['created_at'])); ?></td>
                            <td>
                                <a href="order-detail.php?id=<?php echo htmlspecialchars($earning['order_id']); ?>"><?php echo htmlspecialchars($earning['order_number']); ?></a>
                            </td>
                            <td><?php echo htmlspecialchars($earning['product_name']); ?></td>
                            <td>$<?php echo money($earning['item_total_amount']); ?></td>
                            <td>-$<?php echo money($earning['commission_amount']); ?> (<?php echo $earning['commission_rate'] * 100; ?>%)</td>
                            <td><strong>$<?php echo money($earning['net_earning']); ?></strong></td>
                            <td><span class="status-badge status-<?php echo str_replace('_', '-', $earning['status']); ?>"><?php echo ucwords(str_replace('_', ' ', $earning['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
        </div>
    </div>
</div>

<<<<<<< HEAD
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Earnings Chart
    const ctx = document.getElementById('earningsChart').getContext('2d');
    const earningsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Earnings',
                data: [1200, 1900, 1500, 2100, 1800, 2500, 2200, 2800, 2400, 3000, 3200, 3500],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
=======
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
