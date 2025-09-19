<?php
// vendor/settings.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();
// simple page for now - expand as needed
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Vendor Settings</title>
	<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav><a href="dashboard.php">Dashboard</a></nav>
<h2>Settings</h2>
// simple page for now - expand as needed
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container vendor-settings">
	<nav><a href="<?php echo BASE_URL; ?>vendor/dashboard.php">Dashboard</a></nav>
    <h2>Settings</h2>
    <p>Venue for settings such as shipping info, payout account, tax details, etc.</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
