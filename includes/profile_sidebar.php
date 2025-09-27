<?php
// This file can be included in profile.php, settings.php, etc.
// It assumes $userId is defined in the parent file.

$db_sidebar = new Database();
$conn_sidebar = $db_sidebar->getConnection();

$user_query = "SELECT u.username, u.email, u.created_at, up.first_name, up.last_name, up.avatar FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = :user_id";
$user_stmt = $conn_sidebar->prepare($user_query);
$user_stmt->execute(['user_id' => $userId]);
$sidebar_user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

$orders_count = $conn_sidebar->query("SELECT COUNT(*) FROM orders WHERE customer_id = $userId")->fetchColumn();
$reviews_count = $conn_sidebar->query("SELECT COUNT(*) FROM reviews WHERE user_id = $userId")->fetchColumn();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="profile-card">
    <div class="profile-avatar">
        <?php if (!empty($sidebar_user_data['avatar'])): ?>
            <img src="<?php echo BASE_URL . 'uploads/users/' . htmlspecialchars($sidebar_user_data['avatar']); ?>" alt="User Avatar" class="avatar-image">
        <?php else: ?>
            <div class="avatar-placeholder">
                <?php echo strtoupper(substr($sidebar_user_data['username'] ?? 'U', 0, 1)); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="profile-info">
        <h2><?php echo htmlspecialchars(trim($sidebar_user_data['first_name'] . ' ' . $sidebar_user_data['last_name'])) ?: htmlspecialchars($sidebar_user_data['username']); ?></h2>
        <p class="profile-email"><?php echo htmlspecialchars($sidebar_user_data['email']); ?></p>
    </div>
</div>

<nav class="profile-nav">
    <a href="<?php echo BASE_URL; ?>pages/profile.php" class="nav-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>
        <span>Profile Information</span>
    </a>
    <a href="<?php echo BASE_URL; ?>pages/shipping-address.php" class="nav-item <?php echo $current_page === 'shipping-address.php' ? 'active' : ''; ?>">
        <i class="fas fa-map-marker-alt"></i>
        <span>Shipping Addresses</span>
    </a>
    <a href="<?php echo BASE_URL; ?>pages/orders.php" class="nav-item <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
        <i class="fas fa-shopping-bag"></i>
        <span>My Orders (<?php echo $orders_count; ?>)</span>
    </a>
    <a href="<?php echo BASE_URL; ?>pages/my-reviews.php" class="nav-item <?php echo $current_page === 'my-reviews.php' ? 'active' : ''; ?>">
        <i class="fas fa-star"></i>
        <span>My Reviews (<?php echo $reviews_count; ?>)</span>
    </a>
    <a href="<?php echo BASE_URL; ?>pages/wishlist.php" class="nav-item <?php echo $current_page === 'wishlist.php' ? 'active' : ''; ?>">
        <i class="fas fa-heart"></i>
        <span>Wishlist</span>
    </a>
    <a href="<?php echo BASE_URL; ?>pages/settings.php" class="nav-item <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
    </a>
    <a href="<?php echo BASE_URL; ?>pages/logout.php" class="nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</nav>