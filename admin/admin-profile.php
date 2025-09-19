<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// ✅ Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Fetch current admin details
try {
    $query = "SELECT * FROM users WHERE id = :id AND user_type = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($email)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        try {
            $query = "UPDATE users SET username = :username, email = :email WHERE id = :id AND user_type = 'admin'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['username'] = $username; // Update session
                $success = "Profile updated successfully.";
            } else {
                $error = "Failed to update profile.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// ✅ Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            try {
                $query = "UPDATE users SET password = :password WHERE id = :id AND user_type = 'admin'";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":password", $hashed_password);
                $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $success = "Password changed successfully.";
                } else {
                    $error = "Failed to change password.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Include admin header
$page_title = "Profile";
    include_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>My Profile</h1>
        <p>Manage your account information</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="profile-wrapper">
        <!-- Profile Update -->
        <div class="card">
            <div class="card-header">
                <h2>Update Profile</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Password Change -->
        <div class="card">
            <div class="card-header">
                <h2>Change Password</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-success">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/admin-footer.php'; ?>
<?php include_once __DIR__ . '/../includes/admin-footer.php'; ?>
