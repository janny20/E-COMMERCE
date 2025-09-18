<?php
// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the config file to see if it loads
echo "<h2>Testing config.php</h2>";
try {
    include_once 'includes/config.php';
    echo "<p style='color: green;'>✅ config.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ config.php failed: " . $e->getMessage() . "</p>";
}

// Test database connection
echo "<h2>Testing Database Connection</h2>";
try {
    $db = new PDO("mysql:host=localhost;dbname=multi_vendor_db", "root", "");
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test if we can access the pages index
echo "<h2>Testing Pages Index</h2>";
if (file_exists('pages/index.php')) {
    echo "<p style='color: green;'>✅ pages/index.php exists</p>";
    
    // Try to include it to see if it has errors
    try {
        // Start output buffering to catch any errors
        ob_start();
        include 'pages/index.php';
        $output = ob_get_clean();
        echo "<p style='color: green;'>✅ pages/index.php loaded without fatal errors</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ pages/index.php has errors: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ pages/index.php does not exist</p>";
}

echo "<h2>Next Steps:</h2>";
echo "<p><a href='pages/index.php'>Try accessing pages/index.php directly</a></p>";
echo "<p><a href='./'>Try home page again</a></p>";
?>

<?php
// debug_session.php
session_start();
echo "<h1>Session Debug</h1>";
echo "<pre>";
echo "Session Status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "POST Data:\n";
print_r($_POST);
echo "</pre>";
?>