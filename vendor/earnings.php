<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                COALESCE(SUM(net_earning), 0) as total_earnings
              FROM vendor_earnings
              WHERE vendor_id = :vendor_id";
// For now, pending and paid out are placeholders until a payout status system is built.
$stats = ['pending_clearance' => 0, 'total_paid_out' => 0];
$stats_stmt = $db->prepare($stats_sql);
$stats_stmt->execute(['vendor_id' => $vendor_id]);
$db_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

if ($db_stats) {
    $stats = array_merge($stats, $db_stats);
}

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

$page_title = "My Earnings";
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
            <button class="btn btn-primary" id="requestPayoutBtn" style="margin-top: 1rem;">Request Payout</button>
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
                            <td>
                                <span class="status-badge status-cleared">Cleared</span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payout Modal -->
<div class="modal" id="payoutModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Request Payout</h3>
            <button class="modal-close" id="payoutModalClose">&times;</button>
        </div>
        <div class="modal-body">
            <div id="payout-modal-messages"></div>
            <form id="payoutForm" method="POST" action="<?php echo BASE_URL; ?>ajax/request_payout.php">
                <div class="form-group">
                    <label for="payout_amount">Amount to Withdraw</label>
                    <input type="number" id="payout_amount" name="amount" class="form-control" step="0.01" min="1.00" max="<?php echo $stats['total_earnings']; ?>" required>
                    <small>Available for withdrawal: $<?php echo money($stats['total_earnings']); ?></small>
                </div>
                <div class="form-group">
                    <label for="payout_method">Payout Method</label>
                    <select id="payout_method" name="payout_method" class="form-control" required>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                    </select>
                    <small>Ensure your payout details are up-to-date in your profile settings.</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const payoutModal = document.getElementById('payoutModal');
    if (!payoutModal) return;

    const openBtn = document.getElementById('requestPayoutBtn');
    const closeBtn = document.getElementById('payoutModalClose');
    const form = document.getElementById('payoutForm');

    const openModal = () => payoutModal.classList.add('show');
    const closeModal = () => payoutModal.classList.remove('show');

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    payoutModal.addEventListener('click', (e) => {
        if (e.target === payoutModal) closeModal();
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading"></span> Submitting...';

        fetch(form.action, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                showNotification(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeModal();
                    setTimeout(() => window.location.reload(), 1500);
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Request';
            });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>