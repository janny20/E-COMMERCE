<?php
// includes/middleware.php
session_start();

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isVendor(): bool {
    return isLoggedIn() && !empty($_SESSION['user_type']) && $_SESSION['user_type'] === 'vendor';
}

function requireVendor() {
    if (!isVendor()) {
        // If the user is not a vendor, redirect them to the main home page.
        // This is a safe fallback for any non-vendor user.
        header('Location: ' . BASE_URL . 'pages/home.php');
        exit;
    }
}

// helper to pretty print money
function money($amount) {
    return number_format((float)$amount, 2);
}

// slug helper
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}
