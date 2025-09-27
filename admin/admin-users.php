<?php
// admin-users.php
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
$page_title = "Users Management";

// Handle user actions (toggle status, delete)
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['token'])) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        die('Invalid CSRF token');
    }

    $user_id = $_GET['id'];
    $action = $_GET['action'];
    
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
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                $success = "User deleted successfully.";
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
}

// Get all users with their profiles
$query = "SELECT u.*, up.first_name, up.last_name, up.phone 
          FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          ORDER BY u.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include admin header
include_once '../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/pages/admin-users.css">
<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>Users Management</h1>
        <p>Manage customer accounts</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div>
        <div>
            <h2>All Users</h2>
        </div>
        <div>
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
                                <td data-label="Type">
                                    <span class="badge badge-<?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="user-status <?php echo isset($user['status']) ? $user['status'] : 'unknown'; ?>">
                                        <?php echo isset($user['status']) ? ucfirst($user['status']) : 'Unknown'; ?>
                                    </span>
                                </td>
                                <td data-label="Joined Date"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <a href="admin-user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">View Details</a>
                                        <a href="admin-users.php?action=toggle_status&id=<?php echo $user['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-warning">
                                            <?php echo (isset($user['status']) && $user['status'] == 'active') ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="admin-users.php?action=delete&id=<?php echo $user['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
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