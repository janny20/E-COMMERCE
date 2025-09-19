<?php
// landing.php (ROOT) - Landing page for non-logged-in users
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remove auto-redirect to home.php for logged-in users
// The landing page should always show first
// Only login/register pages should set session and redirect to home.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ShopSphere - Ghana's Largest Online Marketplace</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/landing.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="landing-page">
    <!-- Simple Landing Header (Different from main app header) -->
    <header class="landing-header">
        <div class="container">
            <h1 class="landing-logo">Unimall</h1>
            <nav class="landing-nav">
                <a href="pages/login.php" class="btn btn-outline">Login</a>
                <a href="pages/register.php" class="btn btn-primary">Create Account</a>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="hero-content">
                    <h2>Welcome to Ghana's Largest Online Marketplace</h2>
                    <p>Discover millions of products from thousands of vendors. Everything you need, all in one place.</p>
                    <div class="hero-actions">
                        <a href="pages/register.php" class="btn btn-primary btn-lg">Get Started - It's Free</a>
                        <a href="pages/login.php" class="btn btn-outline btn-lg">I Already Have an Account</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/images/hero-placeholder.jpg" alt="Online Shopping in Ghana">
                </div>
            </div>
        </section>
            <!-- Swiping Images Section -->
            <section class="carousel-section">
                <div class="container">
                    <div class="carousel-wrapper">
                        <div class="carousel-images">
                            <img src="https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Online Shopping">
                            <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Fashion">
                            <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Electronics">
                            <img src="https://images.unsplash.com/photo-1513708922415-9fa7e3b1c8e0?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Home & Office">
                            <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Gadgets">
                        </div>
                        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
                        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </section>
            <style>
            .carousel-section { margin: 48px 0; }
            .carousel-wrapper { position: relative; max-width: 1100px; margin: 0 auto; overflow: hidden; }
            .carousel-images { display: flex; transition: transform 0.6s cubic-bezier(.4,0,.2,1); }
            .carousel-img { min-width: 100%; height: 420px; object-fit: cover; border-radius: 18px; }
            .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.4); color: #fff; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; z-index: 2; display: flex; align-items: center; justify-content: center; }
            .carousel-btn.prev { left: 10px; }
            .carousel-btn.next { right: 10px; }
            .carousel-btn:focus { outline: none; }
            </style>
            <script>
            const carouselImages = document.querySelectorAll('.carousel-img');
            const carouselWrapper = document.querySelector('.carousel-images');
            let currentIndex = 0;
            function showImage(idx) {
                carouselWrapper.style.transform = `translateX(-${idx * 100}%)`;
            }
            document.querySelector('.carousel-btn.next').onclick = function() {
                currentIndex = (currentIndex + 1) % carouselImages.length;
                showImage(currentIndex);
            };
            document.querySelector('.carousel-btn.prev').onclick = function() {
                currentIndex = (currentIndex - 1 + carouselImages.length) % carouselImages.length;
                showImage(currentIndex);
            };
            // Auto swipe every 4 seconds
            setInterval(function() {
                currentIndex = (currentIndex + 1) % carouselImages.length;
                showImage(currentIndex);
            }, 4000);
            // Initial display
            showImage(currentIndex);
            </script>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2>Why Shop With Us?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🚚</div>
                        <h3>Free Delivery</h3>
                        <p>Enjoy free delivery on orders above GHS 200 in Accra and Kumasi.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure Payment</h3>
                        <p>Pay easily and securely with Mobile Money, Visa, or Mastercard.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">↩️</div>
                        <h3>Easy Returns</h3>
                        <p>Not satisfied? Return your items within 7 days for a full refund.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⭐</div>
                        <h3>Quality Assurance</h3>
                        <p>All products are verified for quality before reaching you.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Top Selling Products Section -->
        <section class="top-selling-section">
            <div class="container">
                <h2 class="section-title">Top Selling Products</h2>
                <div class="products-grid">
                    <div class="product-card">
                        <img src="assets/images/products/top1.jpg" alt="Product 1" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Wireless Earbuds</h3>
                            <div class="product-price">GHS 299</div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                                <span>(120)</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="assets/images/products/top2.jpg" alt="Product 2" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Smartphone X</h3>
                            <div class="product-price">GHS 1,499</div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                <span>(210)</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="assets/images/products/top3.jpg" alt="Product 3" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Fashion Sneakers</h3>
                            <div class="product-price">GHS 199</div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                                <span>(85)</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                    <div class="product-card">
                        <img src="assets/images/products/top4.jpg" alt="Product 4" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Blender Pro</h3>
                            <div class="product-price">GHS 349</div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                                <span>(60)</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Flash Sales Section -->
        <section class="flash-sales-section">
                <div class="container">
                    <h2>Flash Sales</h2>
                    <div class="categories-grid">
                        <a href="pages/register.php" class="category-card">
                            <div class="category-image">
                                <img src="assets/images/products/flash1.jpg" alt="Flash 1">
                            </div>
                            <h3>Bluetooth Speaker</h3>
                            <p>GHS 99 <span class="old-price">GHS 149</span></p>
                        </a>
                        <a href="pages/register.php" class="category-card">
                            <div class="category-image">
                                <img src="assets/images/products/flash2.jpg" alt="Flash 2">
                            </div>
                            <h3>Power Bank 10,000mAh</h3>
                            <p>GHS 79 <span class="old-price">GHS 120</span></p>
                        </a>
                        <a href="pages/register.php" class="category-card">
                            <div class="category-image">
                                <img src="assets/images/products/flash3.jpg" alt="Flash 3">
                            </div>
                            <h3>Men's Watch</h3>
                            <p>GHS 59 <span class="old-price">GHS 99</span></p>
                        </a>
                        <a href="pages/register.php" class="category-card">
                            <div class="category-image">
                                <img src="assets/images/products/flash4.jpg" alt="Flash 4">
                            </div>
                            <h3>Kitchen Set</h3>
                            <p>GHS 129 <span class="old-price">GHS 199</span></p>
                        </a>
                        <a href="pages/register.php" class="category-card">
                            <div class="category-image">
                                <img src="assets/images/products/flash5.jpg" alt="Flash 5">
                            </div>
                            <h3>Wireless Mouse</h3>
                            <p>GHS 39 <span class="old-price">GHS 65</span></p>
                        </a>
                        <a href="pages/register.php" class="category-card">
                            <div class="category-image">
                                <img src="assets/images/products/flash6.jpg" alt="Flash 6">
                            </div>
                            <h3>Ladies Handbag</h3>
                            <p>GHS 89 <span class="old-price">GHS 140</span></p>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <style>
        .flash-sales-section { margin: 40px 0; }
        .flash-sales-section .section-title { text-align: center; margin-bottom: 24px; font-size: 2rem; font-weight: 600; color: #e74c3c; }
        .flash-sales-section .old-price { color: #888; text-decoration: line-through; font-size: 0.95rem; margin-left: 6px; }
        .flash-sales-section .products-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            justify-content: center;
        }
        .flash-sales-section .product-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 18px;
            width: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.3s;
        }
        .flash-sales-section .product-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.13);
        }
        .flash-sales-section .product-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .flash-sales-section .product-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 6px;
            text-align: center;
        }
        .flash-sales-section .product-price {
            color: #e67e22;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .flash-sales-section .product-rating {
            color: #f1c40f;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        .flash-sales-section .product-rating span {
            color: #888;
            font-size: 0.95rem;
            margin-left: 4px;
        }
        .flash-sales-section .product-btn {
            background: #2d72d9;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 7px 18px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .flash-sales-section .product-btn:hover {
            background: #1a4e8a;
        }
        </style>
        <style>
        .top-selling-section { margin: 40px 0; }
        .top-selling-section .section-title { text-align: center; margin-bottom: 24px; font-size: 2rem; font-weight: 600; }
        .products-grid { display: flex; flex-wrap: wrap; gap: 24px; justify-content: center; }
        .product-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 18px; width: 220px; display: flex; flex-direction: column; align-items: center; transition: box-shadow 0.3s; }
        .product-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.13); }
        .product-image { width: 100%; height: 140px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; }
        .product-title { font-size: 1.1rem; font-weight: 500; margin-bottom: 6px; text-align: center; }
        .product-price { color: #e67e22; font-size: 1.1rem; font-weight: 600; margin-bottom: 6px; }
        .product-rating { color: #f1c40f; font-size: 1rem; margin-bottom: 8px; }
        .product-rating span { color: #888; font-size: 0.95rem; margin-left: 4px; }
        .product-btn { background: #2d72d9; color: #fff; border: none; border-radius: 6px; padding: 7px 18px; font-size: 1rem; cursor: pointer; text-decoration: none; margin-top: 8px; transition: background 0.2s; }
        .product-btn:hover { background: #1a4e8a; }
        </style>

        <!-- Popular Categories -->
        <section class="categories-section">
            <div class="container">
                <h2>Popular Categories</h2>
                <div class="categories-grid">
                    <a href="pages/register.php" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/phones.jpg" alt="Phones & Tablets">
                        </div>
                        <h3>Phones & Tablets</h3>
                        <p>Latest smartphones and tablets</p>
                    </a>
                    <a href="pages/register.php" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/fashion.jpg" alt="Fashion">
                        </div>
                        <h3>Fashion</h3>
                        <p>Clothing, shoes & accessories</p>
                    </a>
                    <a href="pages/register.php" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/electronics.jpg" alt="Electronics">
                        </div>
                        <h3>Electronics</h3>
                        <p>Gadgets and electronics</p>
                    </a>
                    <a href="pages/register.php" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/home.jpg" alt="Home & Office">
                        </div>
                        <h3>Home & Office</h3>
                        <p>Furniture and supplies</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <h2>Ready to Start Shopping?</h2>
                <p>Join thousands of satisfied customers who shop with confidence</p>
                <div class="cta-actions">
                    <a href="pages/register.php" class="btn btn-primary btn-lg">Create Free Account</a>
                    <span class="cta-note">No credit card required</span>
                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>