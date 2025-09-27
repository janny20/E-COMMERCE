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

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Set page title
$page_title = "Vendors Management";

// Handle vendor actions (approve, reject, suspend)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $vendor_id = $_GET['id'];
    $action = $_GET['action'];
    $valid_statuses = ['approved', 'rejected', 'suspended'];
    if (in_array($action, $valid_statuses)) {
        $query = "UPDATE vendors SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $action);
        $stmt->bindParam(':id', $vendor_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $_SESSION['vendor_status_message'] = "Vendor status updated successfully.";
        } else {
            $_SESSION['vendor_status_message'] = "Error updating vendor status.";
        }

        // Redirect back to the referring page (dashboard or vendors list)
        $redirect_url = 'admin-vendors.php';
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin-dashboard.php') !== false) {
            $redirect_url = 'admin-dashboard.php';
        }
        header('Location: ' . $redirect_url);
        exit();
    } else {
        $_SESSION['vendor_status_message'] = "Invalid action.";
        header('Location: admin-vendors.php');
        exit();
    }
}

// Get filter parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters
$query = "SELECT v.*, u.username, u.email, u.created_at as joined_date 
          FROM vendors v 
          JOIN users u ON v.user_id = u.id 
          WHERE 1=1";
$params = [];

if (!empty($status_filter) && $status_filter != 'all') {
    $query .= " AND v.status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY v.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header
include_once '../includes/admin-header.php';
?>

<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>Vendors Management</h1>
        <p>Manage vendor accounts and approvals</p>
    </div>

    <?php if (!empty($_SESSION['vendor_status_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['vendor_status_message']; unset($_SESSION['vendor_status_message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Vendor Filters</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="suspended" <?php echo $status_filter == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="admin-vendors.php" class="btn btn-outline">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>All Vendors</h2>
        </div>
        <div class="card-body">
            <div style="overflow-x: auto;">
                <?php if (!empty($vendors)): ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $vendor): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($vendor['business_name']); ?></strong>
                                        <?php if (!empty($vendor['business_logo'])): ?>
                                            <div class="vendor-logo">
                                                <img src="../../assets/images/vendors/<?php echo $vendor['business_logo']; ?>" alt="<?php echo htmlspecialchars($vendor['business_name']); ?>" width="50">
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vendor['username']); ?></td>
                                    <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                                    <td>
                                        <span class="user-status <?php echo $vendor['status']; ?>">
                                            <?php echo ucfirst($vendor['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($vendor['joined_date'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin-vendor-details.php?id=<?php echo $vendor['id']; ?>" class="btn btn-edit">View Details</a>
                                            <a href="admin-vendors.php?action=approved&id=<?php echo $vendor['id']; ?>" class="btn btn-success">Approve</a>
                                            <a href="admin-vendors.php?action=rejected&id=<?php echo $vendor['id']; ?>" class="btn btn-danger">Reject</a>
                                            <a href="admin-vendors.php?action=suspended&id=<?php echo $vendor['id']; ?>" class="btn btn-warning">Suspend</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">No vendors found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>