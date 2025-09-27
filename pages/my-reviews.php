0,0 @@
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header('Location: ' . BASE_URL . 'pages/login.php?redirect=my-reviews.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$reviews = [];
$db_error = '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch all reviews for the current user, along with product details
    $sql = "SELECT r.*, p.name as product_name, p.images as product_images, p.slug as product_slug
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            WHERE r.user_id = :user_id
            ORDER BY r.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Database error: " . htmlspecialchars($e->getMessage());
}

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/my-reviews.css">

<main class="my-reviews-page">
    <div class="container">
        <div class="reviews-header">
            <h1>My Reviews</h1>
            <p>You have submitted <?php echo count($reviews); ?> review(s).</p>
        </div>
        
        <?php if ($db_error): ?>
            <div class="alert alert-error"><?php echo $db_error; ?></div>
        <?php elseif (empty($reviews)): ?>
            <div class="empty-reviews">
                <div class="empty-icon">
                    <i class="far fa-star"></i>
                </div>
                <h2>You Haven't Written Any Reviews Yet</h2>
                <p>Share your thoughts on products you've purchased to help other shoppers.</p>
                <a href="<?php echo BASE_URL; ?>pages/orders.php" class="btn btn-primary">View Your Orders to Review</a>
            </div>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-product">
                            <a href="<?php echo BASE_URL . 'pages/product-detail.php?id=' . $review['product_id']; ?>" class="product-link">
                                <?php $img = !empty($review['product_images']) ? explode(',', $review['product_images'])[0] : 'default.jpg'; ?>
                                <img src="<?php echo BASE_URL . 'assets/images/products/' . htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($review['product_name']); ?>" class="product-image">
                                <span class="product-name"><?php echo htmlspecialchars($review['product_name']); ?></span>
                            </a>
                        </div>
                        <div class="review-content">
                            <div class="review-meta">
                                <div class="star-rating">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="<?php echo $i < $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="review-date">Reviewed on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                        <div class="review-status">
                            <span class="status-badge status-<?php echo strtolower(htmlspecialchars($review['status'])); ?>">
                                <?php echo ucfirst(htmlspecialchars($review['status'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once '../includes/footer.php';
?>