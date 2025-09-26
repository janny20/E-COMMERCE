<?php
echo "<h1>Testing XAMPP Setup</h1>";
echo "<p>If you can see this page, PHP is working correctly.</p>";

// Test database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=multi_vendor_db", "root", "");
    echo "<p style='color: green;'>Database connection successful!</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database connection failed: " . $e->getMessage() . "</p>";
}

// Test if important directories exist
$dirs = ['admin', 'vendor', 'pages', 'includes', 'assets'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>Directory '$dir' exists</p>";
    } else {
        echo "<p style='color: red;'>Directory '$dir' is missing</p>";
    }
}

echo "<p><a href='pages/'>Go to Home Page</a></p>";
?>