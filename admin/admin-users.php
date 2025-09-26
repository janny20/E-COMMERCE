<link rel="stylesheet" href="../assets/css/pages/admin-users.css">
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

<<<<<<< HEAD
// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

=======
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
// Set page title
$page_title = "Users Management";

// Handle user actions (toggle status, delete)
<<<<<<< HEAD
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['token'])) {
    $user_id = $_GET['id'];
    $action = $_GET['action'];
    $token = $_GET['token'];

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Invalid CSRF token. Action blocked.";
        // Stop further execution for this block
    } else {
=======
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $action = $_GET['action'];
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    
    if ($action == 'delete') {
        // Check if user has orders before deleting
        $query = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['order_count'] > 0) {
            $error = "Cannot delete user with orders. Please delete orders first.";
        } else {
<<<<<<< HEAD
            $query = "UPDATE users SET status = 'deleted' WHERE id = :id";
=======
            $query = "DELETE FROM users WHERE id = :id";
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
<<<<<<< HEAD
                $success = "User account has been deactivated successfully.";
=======
                $success = "User deleted successfully.";
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
            } else {
                $error = "Error deleting user.";
            }
        }
    } elseif ($action == 'toggle_status') {
        // Get current status
        $query = "SELECT status FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $new_status = $user['status'] == 'active' ? 'inactive' : 'active';
        
        $query = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            $success = "User status updated successfully.";
        } else {
            $error = "Error updating user status.";
        }
    }
<<<<<<< HEAD
    }
}

// Get all users with their profiles
$query = "SELECT u.id, u.username, u.email, u.user_type, u.status, u.created_at, 
                 up.first_name, up.last_name, up.phone 
          FROM users u
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE u.status != 'deleted'
=======
}

// Get all users with their profiles
$query = "SELECT u.*, up.first_name, up.last_name, up.phone 
          FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
          ORDER BY u.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header
include_once '../includes/admin-header.php';
?>

<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>Users Management</h1>
        <p>Manage customer accounts</p>
    </div>

    <?php if (isset($error)): ?>
<<<<<<< HEAD
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
=======
        <div class="alert alert-error"><?php echo $error; ?></div>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

<<<<<<< HEAD
    <div class="card">
        <div class="card-header">
            <h2>All Users</h2>
        </div>
        <div class="card-body">
=======
    <div>
        <div>
            <h2>All Users</h2>
        </div>
        <div>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
            <?php if (!empty($users)): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td data-label="User ID"><?php echo $user['id']; ?></td>
                                <td data-label="Name">
                                    <?php 
                                    $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                    echo !empty($full_name) ? htmlspecialchars($full_name) : htmlspecialchars($user['username']);
                                    ?>
                                </td>
                                <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td data-label="Phone"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'N/A'; ?></td>
<<<<<<< HEAD
                                <td data-label="Type" class="user-type-cell">
=======
                                <td data-label="Type">
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                                    <span class="badge badge-<?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td data-label="Status">
<<<<<<< HEAD
                                    <span class="status-badge status-<?php echo htmlspecialchars($user['status'] ?? 'unknown'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['status'] ?? 'Unknown')); ?>
=======
                                    <span class="user-status <?php echo isset($user['status']) ? $user['status'] : 'unknown'; ?>">
                                        <?php echo isset($user['status']) ? ucfirst($user['status']) : 'Unknown'; ?>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                                    </span>
                                </td>
                                <td data-label="Joined Date"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
<<<<<<< HEAD
                                        <a href="admin-user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-info">View Details</a>
                                        <?php if ($user['user_type'] !== 'admin'): ?>
                                            <a href="admin-users.php?action=toggle_status&id=<?php echo $user['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-warning">
                                                <?php echo (isset($user['status']) && $user['status'] == 'active') ? 'Deactivate' : 'Activate'; ?>
                                            </a>
                                            <a href="admin-users.php?action=delete&id=<?php echo $user['id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</a>
                                        <?php endif; ?>
=======
                                        <a href="admin-user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">View Details</a>
                                        <a href="admin-users.php?action=toggle_status&id=<?php echo $user['id']; ?>" class="btn btn-edit">
                                            <?php echo (isset($user['status']) && $user['status'] == 'active') ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="admin-users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
>>>>>>> fb15e7a04685f9c6a2c15a53b4d13a3a8944dd6b
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>