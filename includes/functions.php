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
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error code: " . $file['error']);
        return ["success" => false, "message" => "An unexpected error occurred during upload."];
    }

    if ($file['size'] === 0) {
        return ["success" => false, "message" => "The uploaded file is empty and cannot be processed."];
    }

    if ($file["size"] > 5000000) { // 5MB limit
        return ["success" => false, "message" => "Sorry, your file is too large (max 5MB)."];
    }

    // Basic check if it's an image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "File is not a valid image."];
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($imageFileType, $allowed_types)) {
        return ["success" => false, "message" => "Sorry, only " . implode(', ', $allowed_types) . " files are allowed."];
    }

    // Generate a unique filename
    $file_name = bin2hex(random_bytes(16)) . '.' . $imageFileType;
    $target_file = rtrim($target_dir, '/') . '/' . $file_name;

    // Check if target directory exists and is writable
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            error_log("Failed to create directory: " . $target_dir);
            return ["success" => false, "message" => "Server error: Failed to create upload directory."];
        }
    }
    if (!is_writable($target_dir)) {
        error_log("Upload directory is not writable: " . $target_dir);
        return ["success" => false, "message" => "Server configuration error: Upload directory is not writable."];
    }

    // Move the uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "file_name" => $file_name];
    } else {
        error_log("move_uploaded_file failed for target '$target_file'.");
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

// Function to get total number of items in cart
function getCartTotalItems($db, $user_id) {
    if (!$user_id) {
        return 0;
    }
    try {
        $query = "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error fetching cart item count: " . $e->getMessage());
        return 0;
    }
}

// Function to get wishlist product IDs for a user
function getWishlistProductIds($db, $user_id) {
    if (!$user_id) {
        return [];
    }
    try {
        $sql = "SELECT product_id FROM wishlist WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        // Use PDO::FETCH_COLUMN to get a flat array of product IDs
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching wishlist IDs: " . $e->getMessage());
        return [];
    }
}

// Function to generate a tracking URL
function getTrackingUrl($carrier, $tracking_number) {
    if (empty($carrier) || empty($tracking_number)) {
        return '#';
    }
    $carrier = strtolower($carrier);
    $encoded_tracking = urlencode($tracking_number);

    switch ($carrier) {
        case 'ups':
            return "https://www.ups.com/track?loc=en_US&tracknum={$encoded_tracking}";
        case 'fedex':
            return "https://www.fedex.com/fedextrack/?trknbr={$encoded_tracking}";
        case 'usps':
            return "https://tools.usps.com/go/TrackConfirmAction?tLabels={$encoded_tracking}";
        case 'dhl':
            return "https://www.dhl.com/en/express/tracking.html?AWB={$encoded_tracking}";
        default:
            return "https://www.google.com/search?q={$encoded_tracking}";
    }
}

// Function to get detailed cart data including totals, tax, shipping, and discounts
function getCartData($db, $user_id) {
    $query = "SELECT c.product_id, c.quantity, p.price
              FROM cart c
              JOIN products p ON c.product_id = p.id
              WHERE c.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Apply coupon if one is active in the session
    $discount = 0;
    $coupon_code = null;
    $active_coupon = $_SESSION['cart_coupon'] ?? null;
    
    if ($active_coupon) {
        if ($active_coupon['type'] === 'percentage') {
            $discount = $subtotal * ($active_coupon['value'] / 100);
        } elseif ($active_coupon['type'] === 'fixed') {
            $discount = $active_coupon['value'];
        }
        $discount = min($subtotal, $discount);
        $coupon_code = $active_coupon['code'];
    }
    
    $subtotal_after_discount = $subtotal - $discount;
    $free_shipping_threshold = 50.00;
    $shipping_cost = ($subtotal_after_discount > $free_shipping_threshold || $subtotal_after_discount == 0) ? 0 : 5.99;
    $amount_needed = max(0, $free_shipping_threshold - $subtotal_after_discount);
    $tax_amount = $subtotal_after_discount * 0.08;
    $total_amount = $subtotal_after_discount + $shipping_cost + $tax_amount;

    return [ 'subtotal_raw' => $subtotal, 'subtotal' => number_format($subtotal, 2), 'discount' => number_format($discount, 2), 'coupon_code' => $coupon_code, 'shipping' => number_format($shipping_cost, 2), 'tax' => number_format($tax_amount, 2), 'total' => number_format($total_amount, 2), 'amountNeededForFreeShipping' => $amount_needed, 'freeShippingThreshold' => $free_shipping_threshold, 'cartCount' => getCartTotalItems($db, $user_id) ];
}

// Function to log order status changes
function logOrderStatus($db, $order_id, $status, $notes = '') {
    try {
        $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (:order_id, :status, :notes)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'order_id' => $order_id,
            'status' => $status,
            'notes' => $notes
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log order status: " . $e->getMessage());
    }
}
?>