<?php
// includes/config.php - CHECK FOR SESSION ISSUES
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// REMOVE any session_start() from here if it exists
// session_start(); // <- COMMENT OUT OR REMOVE THIS LINE

// Base URL
define('BASE_URL', 'http://localhost/multi-vendor-ecommerce/');

// Database configuration
// Change port if your MySQL is not on 3306 (e.g., 'localhost:3307')
define('DB_HOST', 'localhost:3306'); // MySQL is running on port 3307
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your MySQL password if not empty

// File upload paths
define('PRODUCT_UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/multi-vendor-ecommerce/uploads/products/');
define('VENDOR_UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/multi-vendor-ecommerce/uploads/vendors/');
define('USER_UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/multi-vendor-ecommerce/uploads/users/');

// --- TROUBLESHOOTING ---
// 1. Make sure MySQL is running in XAMPP
// 2. Confirm DB credentials above match phpMyAdmin
// 3. If you changed MySQL port, update DB_HOST (e.g., 'localhost:3307')
// 4. If you use a password for root, set DB_PASS
// -----------------------

// Include database connection
require_once 'database.php';

// Include functions
require_once 'functions.php';

// Include auth class
require_once 'auth.php';

// Create auth instance
$auth = new Auth();

// Check if user is logged in (only if session is started elsewhere)
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

// Get cart count if user is logged in
$cartCount = 0;
if ($isLoggedIn) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $result['count'];
}
?>