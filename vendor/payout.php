<?php
// vendor/earnings.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/middleware.php';
requireVendor();

$vendor_id = $_SESSION['vendor_id'];

// total earned
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM vendor_earnings WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$total = $stmt->fetchColumn();

// recent earnings
$stmt = $pdo->prepare("SELECT ve.*, oi.order_id, oi.price, oi.qty FROM vendor_earnings ve
                       JOIN order_items oi ON oi.id = ve.order_item_id
                       WHERE ve.vendor_id = ?
                       ORDER BY ve.created_at DESC LIMIT 50");
$stmt->execute([$vendor_id]);
$items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['request_withdraw'])) {
    $amount = (float)$_POST['amount'];
    if ($amount <= 0 || $amount > $total) {
        $error = "Invalid amount";
    } else {
        // create a payout request (table vendor_payouts assumed)
        $stmt = $pdo->prepare("INSERT INTO vendor_payouts (vendor_id, amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
        $stmt->execute([$vendor_id, $amount]);
        $success = "Payout requested";
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Earnings</title></head><body>
<nav><a href="dashboard.php">Dashboard</a></nav>
<h2>Total earnings: $<?=money($total)?></h2>

<?php if(!empty($error)): ?><div style="color:red"><?=htmlspecialchars($error)?></div><?php endif; ?>
<?php if(!empty($success)): ?><div style="color:green"><?=htmlspecialchars($success)?></div><?php endif; ?>

<form method="post">
  <label>Request withdrawal amount: <input type="number" step="0.01" name="amount" max="<?=htmlspecialchars($total)?>"></label>
  <button name="request_withdraw" value="1">Request</button>
</form>

<h3>Recent earnings</h3>
<table border=1 cellpadding=6>
  <tr><th>#</th><th>Order</th><th>Amount</th><th>When</th></tr>
  <?php foreach($items as $it): ?>
    <tr>
      <td><?=htmlspecialchars($it['id'])?></td>
      <td><?=htmlspecialchars($it['order_id'])?></td>
      <td>$<?=money($it['amount'])?></td>
      <td><?=htmlspecialchars($it['created_at'])?></td>
    </tr>
  <?php endforeach; ?>
</table>
</body></html>
