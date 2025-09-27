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

// Get user ID from query
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id <= 0) {
    die('Invalid user ID.');
}

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Fetch user info
$query = "SELECT u.*, up.first_name, up.last_name, up.phone FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Include admin header
include_once '../includes/admin-header.php';
?>

<div class="admin-users-container">
    <div class="admin-users-header">
        <h1>User Details</h1>
        <a href="admin-users.php" class="btn btn-edit">Back to Users</a>
    </div>
    <?php if ($user): ?>
        <div class="card" style="max-width:600px;margin:auto;">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'N/A'; ?></p>
            <p><strong>User Type:</strong> <?php echo ucfirst($user['user_type']); ?></p>
            <p><strong>Status:</strong> <span class="user-status <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></p>
            <p><strong>Joined:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
        </div>
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>
</div>

<?php
// Include admin footer
include_once '../includes/admin-footer.php';
?>
