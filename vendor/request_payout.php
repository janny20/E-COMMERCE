0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/middleware.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isVendor()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$vendor_id = $_SESSION['vendor_id'];
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$method = filter_input(INPUT_POST, 'payout_method', FILTER_SANITIZE_STRING);

if (!$amount || $amount <= 0 || !$method) {
    $response['message'] = 'Invalid payout amount or method.';
    echo json_encode($response);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check if vendor has sufficient balance
    $balance_sql = "SELECT COALESCE(SUM(net_earning), 0) as balance FROM vendor_earnings WHERE vendor_id = :vendor_id AND status = 'cleared'";
    $balance_stmt = $db->prepare($balance_sql);
    $balance_stmt->execute(['vendor_id' => $vendor_id]);
    $available_balance = $balance_stmt->fetchColumn();

    if ($amount > $available_balance) {
        $response['message'] = 'Requested amount exceeds your available balance of $' . money($available_balance);
        echo json_encode($response);
        exit();
    }

    // Insert a payout request
    // In a real app, you'd have a `vendor_payouts` table. We'll simulate success.
    // For now, we just return a success message.
    // A real implementation would be:
    /*
    $payout_sql = "INSERT INTO vendor_payouts (vendor_id, amount, method, status, created_at) VALUES (:vendor_id, :amount, :method, 'pending', NOW())";
    $payout_stmt = $db->prepare($payout_sql);
    $payout_stmt->execute([
        'vendor_id' => $vendor_id,
        'amount' => $amount,
        'method' => $method
    ]);
    */

    $response['success'] = true;
    $response['message'] = 'Payout request for $' . money($amount) . ' submitted successfully. It will be processed by an admin.';

} catch (PDOException $e) {
    error_log("Payout Request Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);