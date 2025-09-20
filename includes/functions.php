<?php
// Function to generate slug from text
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// Function to handle file uploads
function uploadFile($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    // Check for upload errors first
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // It's better to return a generic error for security, but for debugging, this is fine.
        return ["success" => false, "message" => "File upload error code: " . $file['error']];
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }

    // Check file size (5MB limit)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "Sorry, your file is too large (max 5MB)."];
    }

    // Allow certain file formats
    if(!in_array($imageFileType, $allowed_types)) {
        return ["success" => false, "message" => "Sorry, only " . implode(', ', $allowed_types) . " files are allowed."];
    }

    // Generate a unique filename to prevent overwrites and security issues
    $file_name = bin2hex(random_bytes(16)) . '.' . $imageFileType;
    $target_file = rtrim($target_dir, '/') . '/' . $file_name;

    // Check if target directory exists, if not, create it
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            error_log("Failed to create directory: " . $target_dir);
            return ["success" => false, "message" => "Server error: Failed to create upload directory."];
        }
    }

    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "file_name" => $file_name];
    } else {
        $error = error_get_last();
        error_log("move_uploaded_file failed for target '$target_file': " . ($error['message'] ?? 'Unknown error'));
        return ["success" => false, "message" => "Sorry, there was a server error uploading your file."];
    }
}

// Function to format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Function to get category name by ID
function getCategoryName($category_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT name FROM categories WHERE id = :category_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['name'];
    }
    
    return 'Uncategorized';
}

// Function to get vendor name by ID
function getVendorName($vendor_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT business_name FROM vendors WHERE id = :vendor_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":vendor_id", $vendor_id);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['business_name'];
    }
    
    return 'Unknown Vendor';
}

// Function to generate order number
function generateOrderNumber() {
    return 'ORD' . strtoupper(uniqid());
}

// Function to get cart total
function getCartTotal($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(c.quantity * p.price) as total 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}
?>