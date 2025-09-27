<link rel="stylesheet" href="../assets/css/pages/admin-reports.css">
<?php
session_set_cookie_params([
	'lifetime' => 60 * 60 * 24 * 30,
	'path' => '/',
	'domain' => '',
	'secure' => false,
	'httponly' => true,
	'samesite' => 'Lax'
]);
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
	header('Location: login.php');
	exit();
}

$page_title = "Admin Reports";
include_once '../includes/admin-header.php';
?>
<div class="admin-reports-container">
	<div class="admin-reports-header">
		<h1>Reports & Analytics</h1>
		<p>View sales, orders, vendor performance, and user activity. Use filters to customize your report view.</p>
	</div>
	<div class="reports-filters">
		<form method="get" class="filters-form">
			<label for="report-type">Report Type:</label>
			<select id="report-type" name="type">
				<option value="sales">Sales</option>
				<option value="orders">Orders</option>
				<option value="vendors">Vendor Performance</option>
				<option value="users">User Activity</option>
			</select>
			<label for="date-from">From:</label>
			<input type="date" id="date-from" name="from">
			<label for="date-to">To:</label>
			<input type="date" id="date-to" name="to">
			<button type="submit" class="btn btn-primary">Filter</button>
		</form>
	</div>
	<?php
	$database = new Database();
	$db = $database->getConnection();

	// Get filter values
	$type = isset($_GET['type']) ? $_GET['type'] : 'sales';
	$from = isset($_GET['from']) ? $_GET['from'] : null;
	$to = isset($_GET['to']) ? $_GET['to'] : null;

	// Build date filter
	$dateFilter = '';
	if ($from && $to) {
		$dateFilter = " AND o.created_at BETWEEN :from AND :to ";
	}

	// Summary queries
	$totalSales = 0;
	$totalOrders = 0;
	$activeVendors = 0;
	$newUsers = 0;

	// Total sales
	$sql = "SELECT SUM(total_amount) as total FROM orders o WHERE o.status IN ('confirmed','processing','shipped','delivered')" . ($dateFilter ? $dateFilter : '');
	$stmt = $db->prepare($sql);
	if ($dateFilter) {
		$stmt->bindParam(':from', $from);
		$stmt->bindParam(':to', $to);
	}
	$stmt->execute();
	$totalSales = $stmt->fetchColumn();

	// Total orders
	$sql = "SELECT COUNT(*) FROM orders o WHERE 1" . ($dateFilter ? $dateFilter : '');
	$stmt = $db->prepare($sql);
	if ($dateFilter) {
		$stmt->bindParam(':from', $from);
		$stmt->bindParam(':to', $to);
	}
	$stmt->execute();
	$totalOrders = $stmt->fetchColumn();

	// Active vendors
	$sql = "SELECT COUNT(*) FROM vendors WHERE status = 'approved'";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$activeVendors = $stmt->fetchColumn();

	// New users (last 30 days)
	$sql = "SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$newUsers = $stmt->fetchColumn();

	?>
	<div class="reports-summary">
		<div class="summary-card">
			<h3>Total Sales</h3>
		<p class="summary-value">₵<?php echo number_format($totalSales !== null ? (float)$totalSales : 0, 2); ?></p>
		</div>
		<div class="summary-card">
			<h3>Total Orders</h3>
			<p class="summary-value"><?php echo $totalOrders; ?></p>
		</div>
		<div class="summary-card">
			<h3>Active Vendors</h3>
			<p class="summary-value"><?php echo $activeVendors; ?></p>
		</div>
		<div class="summary-card">
			<h3>New Users</h3>
			<p class="summary-value"><?php echo $newUsers; ?></p>
		</div>
	</div>
	<?php
	// Table data
	if ($type === 'sales') {
		$sql = "SELECT o.created_at, o.order_number, u.username, v.business_name, o.total_amount, o.status
				FROM orders o
				LEFT JOIN users u ON o.customer_id = u.id
				LEFT JOIN order_items oi ON oi.order_id = o.id
				LEFT JOIN vendors v ON oi.vendor_id = v.id
				WHERE 1" . ($dateFilter ? $dateFilter : '') . "
				ORDER BY o.created_at DESC LIMIT 10";
		$stmt = $db->prepare($sql);
		if ($dateFilter) {
			$stmt->bindParam(':from', $from);
			$stmt->bindParam(':to', $to);
		}
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		?>
		<div class="reports-table-section">
			<h2>Recent Sales</h2>
			<table class="reports-table">
				<thead>
					<tr>
						<th>Date</th>
						<th>Order ID</th>
						<th>Customer</th>
						<th>Vendor</th>
						<th>Amount</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $row): ?>
					<tr>
						<td data-label="Date"><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
						<td data-label="Order ID">#<?php echo htmlspecialchars($row['order_number']); ?></td>
						<td data-label="Customer"><?php echo htmlspecialchars($row['username']); ?></td>
						<td data-label="Vendor"><?php echo htmlspecialchars($row['business_name']); ?></td>
						<td data-label="Amount">₵<?php echo number_format($row['total_amount'],2); ?></td>
						<td data-label="Status"><span class="status <?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
					</tr>
					<?php endforeach; ?>
					<?php if (empty($rows)): ?>
					<tr><td colspan="6" class="text-center">No sales found.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
	?>
</div>
<?php include_once '../includes/admin-footer.php'; ?>