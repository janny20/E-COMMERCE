<?php
// Handle AJAX POST for real-time settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_name'])) {
	$site_name = trim($_POST['site_name']);
	$admin_email = trim($_POST['admin_email']);
	$currency = $_POST['currency'];
	$maintenance = $_POST['maintenance'];
	require_once '../includes/database.php';
	$database = new Database();
	$db = $database->getConnection();
	try {
		$db->exec("CREATE TABLE IF NOT EXISTS settings (
			id INT NOT NULL PRIMARY KEY,
			site_name VARCHAR(255),
			admin_email VARCHAR(255),
			currency VARCHAR(10),
			maintenance VARCHAR(10)
		)");
		$sql = "REPLACE INTO settings (id, site_name, admin_email, currency, maintenance) VALUES (1, :site_name, :admin_email, :currency, :maintenance)";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':site_name', $site_name);
		$stmt->bindParam(':admin_email', $admin_email);
		$stmt->bindParam(':currency', $currency);
		$stmt->bindParam(':maintenance', $maintenance);
		$success = $stmt->execute();
		header('Content-Type: application/json');
		echo json_encode(['success' => $success]);
	} catch (PDOException $e) {
		header('Content-Type: application/json');
		echo json_encode(['success' => false, 'error' => $e->getMessage()]);
	}
	exit();
}
?>
<link rel="stylesheet" href="../assets/css/pages/admin-settings.css">
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
	header('Location: login.php');
	exit();
}

$page_title = "Admin Settings";
include_once '../includes/admin-header.php';
?>
<div class="admin-settings-container">
	<div class="admin-settings-header">
		<h1>Settings</h1>
		<p>Update platform configuration and preferences.</p>
	</div>
	<?php
	// Fetch settings from database
	$site_name = 'Multi-Vendor Ecommerce';
	$admin_email = 'admin@example.com';
	$currency = 'GHS';
	$maintenance = 'off';
	try {
		require_once '../includes/database.php';
		$database = new Database();
		$db = $database->getConnection();
		$stmt = $db->query('SELECT * FROM settings WHERE id = 1');
		$settings = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($settings) {
			$site_name = $settings['site_name'];
			$admin_email = $settings['admin_email'];
			$currency = $settings['currency'];
			$maintenance = $settings['maintenance'];
		}
	} catch (Exception $e) {}
	?>
	<form class="settings-form" id="settingsForm" method="post" action="">
		<div class="form-group">
			<label for="site_name">Site Name</label>
			<input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
		</div>
		<div class="form-group">
			<label for="admin_email">Admin Email</label>
			<input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_email); ?>" required>
		</div>
		<div class="form-group">
			<label for="currency">Currency</label>
			<select id="currency" name="currency">
				<option value="GHS" <?php if($currency=='GHS')echo 'selected';?>>GHS (₵)</option>
				<option value="USD" <?php if($currency=='USD')echo 'selected';?>>USD ($)</option>
				<option value="EUR" <?php if($currency=='EUR')echo 'selected';?>>EUR (€)</option>
			</select>
		</div>
		<div class="form-group">
			<label for="maintenance">Maintenance Mode</label>
			<select id="maintenance" name="maintenance">
				<option value="off" <?php if($maintenance=='off')echo 'selected';?>>Off</option>
				<option value="on" <?php if($maintenance=='on')echo 'selected';?>>On</option>
			</select>
		</div>
		<button type="submit" class="btn btn-primary">Save Settings</button>
		<div id="settingsMsg" style="margin-top:16px;"></div>
	</form>
	<script>
	document.getElementById('settingsForm').addEventListener('submit', function(e) {
		e.preventDefault();
		var form = e.target;
		var data = new FormData(form);
		fetch('admin-settings.php', {
			method: 'POST',
			body: data
		})
		.then(res => res.json())
		.then(resp => {
			document.getElementById('settingsMsg').innerHTML = resp.success ? '<span style="color:green;">Settings saved!</span>' : '<span style="color:red;">'+resp.error+'</span>';
		})
		.catch(() => {
			document.getElementById('settingsMsg').innerHTML = '<span style="color:red;">Failed to save settings.</span>';
		});
	});
	</script>
</div>
<?php include_once '../includes/admin-footer.php'; ?>