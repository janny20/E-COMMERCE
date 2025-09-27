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

$ajaxUserId = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Handle AJAX password change
if (isset($_POST['change_password_ajax'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $response['message'] = 'Please fill in all password fields.';
    } elseif (strlen($new_password) < 6) {
        $response['message'] = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match.';
    } else {
        $pass_query = "SELECT password FROM users WHERE id = :user_id";
        $pass_stmt = $db->prepare($pass_query);
        $pass_stmt->bindParam(':user_id', $ajaxUserId, PDO::PARAM_INT);
        $pass_stmt->execute();
        $user_pass_data = $pass_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_pass_data && password_verify($current_password, $user_pass_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_query = "UPDATE users SET password = :password WHERE id = :user_id";
            $update_pass_stmt = $db->prepare($update_pass_query);
            $update_pass_stmt->bindParam(':password', $hashed_password);
            $update_pass_stmt->bindParam(':user_id', $ajaxUserId, PDO::PARAM_INT);

            if ($update_pass_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Password updated successfully!';
            } else {
                $response['message'] = 'Failed to update password. Please try again.';
            }
        } else {
            $response['message'] = 'Incorrect current password.';
        }
    }
}

// Handle AJAX preferences update
if (isset($_POST['update_preferences_ajax'])) {
    $setting = $_POST['setting'] ?? '';
    $value = ($_POST['value'] ?? 'false') === 'true' ? 1 : 0;

    $allowed_settings = ['email_notifications', 'sms_notifications', 'marketing_communications'];

    if (in_array($setting, $allowed_settings)) {
        $check_profile_query = "SELECT id FROM user_profiles WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_profile_query);
        $check_stmt->bindParam(':user_id', $ajaxUserId, PDO::PARAM_INT);
        $check_stmt->execute();
        $profile_exists = $check_stmt->fetch();

        if ($profile_exists) {
            $query = "UPDATE user_profiles SET `$setting` = :value WHERE user_id = :user_id";
        } else {
            $query = "INSERT INTO user_profiles (user_id, `$setting`) VALUES (:user_id, :value)";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':value', $value, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $ajaxUserId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Preference updated.';
        } else {
            $response['message'] = 'Failed to update preference.';
        }
    } else {
        $response['message'] = 'Invalid preference setting.';
    }
}

echo json_encode($response);