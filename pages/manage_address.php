0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication error. Please log in again.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$database = new Database();
$db = $database->getConnection();

try {
    switch ($action) {
        case 'add':
        case 'update':
            $address_id = isset($_POST['address_id']) ? (int)$_POST['address_id'] : null;
            $data = [
                'user_id' => $user_id,
                'label' => trim($_POST['label'] ?? ''),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'address_line_1' => trim($_POST['address_line_1'] ?? ''),
                'address_line_2' => trim($_POST['address_line_2'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'zip_code' => trim($_POST['zip_code'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'is_default' => isset($_POST['is_default']) ? 1 : 0,
            ];

            if (empty($data['full_name']) || empty($data['address_line_1']) || empty($data['city']) || empty($data['country'])) {
                throw new Exception('Please fill all required address fields.');
            }

            $db->beginTransaction();

            if ($data['is_default']) {
                $reset_default_stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $reset_default_stmt->execute([$user_id]);
            }

            if ($action === 'add') {
                $sql = "INSERT INTO user_addresses (user_id, label, full_name, address_line_1, address_line_2, city, state, zip_code, country, phone, is_default) 
                        VALUES (:user_id, :label, :full_name, :address_line_1, :address_line_2, :city, :state, :zip_code, :country, :phone, :is_default)";
                $stmt = $db->prepare($sql);
                $stmt->execute($data);
                $response['message'] = 'Address added successfully.';
            } else {
                $data['address_id'] = $address_id;
                $sql = "UPDATE user_addresses SET label=:label, full_name=:full_name, address_line_1=:address_line_1, address_line_2=:address_line_2, city=:city, state=:state, zip_code=:zip_code, country=:country, phone=:phone, is_default=:is_default 
                        WHERE id=:address_id AND user_id=:user_id";
                $stmt = $db->prepare($sql);
                $stmt->execute($data);
                $response['message'] = 'Address updated successfully.';
            }

            $db->commit();
            $response['success'] = true;
            break;

        case 'get':
            $address_id = (int)($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$address_id, $user_id]);
            $address = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($address) {
                $response['success'] = true;
                $response['data'] = $address;
            } else {
                throw new Exception('Address not found.');
            }
            break;

        case 'delete':
            $address_id = (int)($_POST['address_id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$address_id, $user_id]);
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Address deleted successfully.';
            } else {
                throw new Exception('Failed to delete address.');
            }
            break;

        case 'set_default':
            $address_id = (int)($_POST['address_id'] ?? 0);
            $db->beginTransaction();
            $reset_stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $reset_stmt->execute([$user_id]);
            $set_stmt = $db->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            $set_stmt->execute([$address_id, $user_id]);
            $db->commit();
            $response['success'] = true;
            $response['message'] = 'Default address updated.';
            break;

        default:
            throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);