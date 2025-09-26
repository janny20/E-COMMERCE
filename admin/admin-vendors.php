<?php
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

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Set page title
$page_title = "Vendor Management";

// Handle vendor actions (approve, reject, suspend) with CSRF protection
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        $vendor_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $action = htmlspecialchars($_GET['action'] ?? '');

        $valid_statuses = ['approved', 'rejected', 'suspended', 'delete'];

        if (in_array($action, $valid_statuses)) {
            if ($action === 'delete') {
                // Soft delete: Mark the vendor's user account as 'deleted'.
                // The ON DELETE CASCADE in the DB is for hard deletes, so we handle this manually.
                try {
                    $db->beginTransaction();

                    // Find the user_id associated with the vendor_id
                    $user_id_query = "SELECT user_id FROM vendors WHERE id = :vendor_id";
                    $user_stmt = $db->prepare($user_id_query);
                    $user_stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
                    $user_stmt->execute();
                    $user_id = $user_stmt->fetchColumn();
                    
                    if ($user_id) {
                        // 1. Update the user's status to 'deleted'
                        $update_user_query = "UPDATE users SET status = 'deleted' WHERE id = :user_id";
                        $update_stmt = $db->prepare($update_user_query);
                        $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $update_stmt->execute();

                        // 2. Also update the vendor status to prevent re-application issues
                        $update_vendor_query = "UPDATE vendors SET status = 'rejected' WHERE id = :vendor_id";
                        $update_vendor_stmt = $db->prepare($update_vendor_query);
                        $update_vendor_stmt->bindParam(':vendor_id', $vendor_id, PDO::PARAM_INT);
                        $update_vendor_stmt->execute();

                        $db->commit();
                        $success = "Vendor account has been deactivated successfully.";
                    }
                } catch (PDOException $e) {
                    $error = "Error deleting vendor: " . $e->getMessage();
                }
            } else {
                // Handle status changes
                try {
                    $db->beginTransaction();

                    // 1. Update vendor status
                    $vendor_query = "UPDATE vendors SET status = :status WHERE id = :id";
                    $vendor_stmt = $db->prepare($vendor_query);
                    $vendor_stmt->bindParam(':status', $action);
                    $vendor_stmt->bindParam(':id', $vendor_id);
                    $vendor_stmt->execute();

                    // 2. If approving, also ensure the user's type is 'vendor'
                    if ($action === 'approved') {
                        $user_query = "UPDATE users u JOIN vendors v ON u.id = v.user_id SET u.user_type = 'vendor' WHERE v.id = :vendor_id";
                        $user_stmt = $db->prepare($user_query);
                        $user_stmt->bindParam(':vendor_id', $vendor_id);
                        $user_stmt->execute();
                    } elseif ($action === 'suspended' || $action === 'rejected') {
                        // If suspending or rejecting, it's good practice to revert user_type to customer
                        // This prevents them from seeing vendor-specific UI elements if they are still logged in.
                        $user_query = "UPDATE users u JOIN vendors v ON u.id = v.user_id SET u.user_type = 'customer' WHERE v.id = :vendor_id";
                        $user_stmt = $db->prepare($user_query);
                        $user_stmt->bindParam(':vendor_id', $vendor_id);
                        $user_stmt->execute();
                    }

                    $db->commit();
                    $success = "Vendor status updated to '" . htmlspecialchars($action) . "' successfully.";
                } catch (PDOException $e) {
                    $db->rollBack();
                    $error = "Error updating vendor status: " . $e->getMessage();
                }
            }
        } else {
            $error = "Invalid action specified.";
        }
    } else {
        $error = "Invalid security token. Action blocked.";
    }
}

// Get filter parameter
$status_filter = isset($_GET['status']) ? filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) : '';

// Build query with filters
$query = "SELECT v.*, u.username, u.email, u.created_at as joined_date 
          FROM vendors v 
          JOIN users u ON v.user_id = u.id 
          WHERE 1=1"; // Using 1=1 is a common trick to make appending AND clauses easier
$params = [];

if (!empty($status_filter) && $status_filter != 'all') {
    $query .= " AND v.status = :status";
    $params[':status'] = $status_filter;
}

// Exclude vendors whose associated user account has been deleted
$query .= " AND u.status != 'deleted'";

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

<div class="admin-vendors-container">
    <div class="admin-vendors-header">
        <h1>Vendor Management</h1>
        <p>Approve, manage, and view all vendors.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Filters</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-controls">
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
                    
                    <div class="form-group filter-buttons">
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
            <?php if (!empty($vendors)): ?>
                <table class="vendors-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td data-label="ID"><?php echo $vendor['id']; ?></td>
                                <td data-label="Business Name">
                                    <div class="vendor-info">
                                        <img src="<?php echo BASE_URL . 'uploads/vendors/' . (!empty($vendor['business_logo']) ? htmlspecialchars($vendor['business_logo']) : 'default_logo.png'); ?>" alt="<?php echo htmlspecialchars($vendor['business_name']); ?>" class="vendor-logo">
                                        <div>
                                            <strong><?php echo htmlspecialchars($vendor['business_name']); ?></strong>
                                            <div class="text-muted"><?php echo htmlspecialchars($vendor['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <span class="status-badge status-<?php echo htmlspecialchars($vendor['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($vendor['status'])); ?>
                                    </span>
                                </td>
                                <td data-label="Joined Date"><?php echo date('M j, Y', strtotime($vendor['joined_date'])); ?></td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($vendor['status'] == 'pending'): ?>
                                            <a href="admin-vendors.php?action=approved&id=<?php echo $vendor['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</a>
                                            <a href="admin-vendors.php?action=rejected&id=<?php echo $vendor['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-warning btn-sm"><i class="fas fa-times"></i> Reject</a>
                                        <?php elseif ($vendor['status'] == 'approved'): ?>
                                            <a href="admin-vendors.php?action=suspended&id=<?php echo $vendor['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-warning btn-sm"><i class="fas fa-ban"></i> Suspend</a>                                        
                                        <?php elseif ($vendor['status'] == 'suspended'): ?>
                                            <a href="admin-vendors.php?action=approved&id=<?php echo $vendor['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i> Unsuspend</a>
                                        <?php elseif ($vendor['status'] == 'rejected'): ?>
                                            <a href="admin-vendors.php?action=approved&id=<?php echo $vendor['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i> Re-Approve</a>
                                        <?php endif; ?>
                                        <a href="admin-vendor-details.php?id=<?php echo $vendor['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Details</a>
                                        <a href="admin-vendors.php?action=delete&id=<?php echo $vendor['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this vendor? This action is permanent.')"><i class="fas fa-trash"></i> Delete</a>
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

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>