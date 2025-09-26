<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get user ID from query
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    die('Invalid user ID.');
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Fetch user info, ensuring 'status' is selected
$query = "SELECT u.id, u.username, u.email, u.user_type, u.status, u.created_at, 
                 up.first_name, up.last_name, up.phone 
          FROM users u 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE u.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "User Details";

// Include admin header
include_once '../includes/admin-header.php';
?>
<link rel="stylesheet" href="../assets/css/pages/admin-details.css">

<div class="admin-details-container">
    <div class="details-header">
        <h1>User Details</h1>
        <a href="admin-users.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Users List</a>
    </div>
    <?php if ($user): ?>
        <div class="details-card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h2>
                <span class="badge badge-<?php echo htmlspecialchars($user['user_type']); ?>"><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></span>
            </div>
            <div class="card-body">
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Username</span>
                        <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email Address</span>
                        <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone Number</span>
                        <span class="detail-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'N/A'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Account Status</span>
                        <span class="detail-value"><span class="status-badge status-<?php echo htmlspecialchars($user['status'] ?? 'unknown'); ?>"><?php echo ucfirst(htmlspecialchars($user['status'] ?? 'Unknown')); ?></span></span>
                    </div>
                    <div class="detail-item full-width">
                        <span class="detail-label">Member Since</span>
                        <span class="detail-value"><?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">User not found.</div>
    <?php endif; ?>
</div>

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>
