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

// Set page title
$page_title = "Users Management";

// Handle user actions (toggle status, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
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
include_once 'includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Users Management</h1>
        <p>Manage customer accounts</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>All Users</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <table class="data-table">
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
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <?php 
                                    $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                    echo !empty($full_name) ? htmlspecialchars($full_name) : htmlspecialchars($user['username']);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm">View Details</a>
                                        <a href="users.php?action=toggle_status&id=<?php echo $user['id']; ?>" class="btn btn-sm <?php echo $user['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>">
                                            <?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
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
include_once 'includes/admin-footer.php';
?>