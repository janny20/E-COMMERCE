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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <!-- Simple Landing Header (Different from main app header) -->
    <header class="landing-header">
        <div class="container">
            <h1 class="landing-logo" data-aos="fade-down">Unimall</h1>
            <nav class="landing-nav" data-aos="fade-down" data-aos-delay="100">
                <a href="pages/login.php" class="btn btn-outline">Login</a>
                <a href="pages/register.php" class="btn btn-primary">Create Account</a>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="hero-content" data-aos="fade-right" data-aos-duration="800">
                    <h2>Welcome to Ghana's Largest Online Marketplace</h2>
                    <p>Discover millions of products from thousands of vendors. Everything you need, all in one place.</p>
                    <div class="hero-actions">
                        <a href="pages/register.php" class="btn btn-primary btn-lg pulse-animation">Get Started - It's Free</a>
                        <a href="pages/login.php" class="btn btn-outline btn-lg">I Already Have an Account</a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-number" data-count="10000">0</span>
                            <span class="stat-label">Active Vendors</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number" data-count="500000">0</span>
                            <span class="stat-label">Products</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number" data-count="1000000">0</span>
                            <span class="stat-label">Happy Customers</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image" data-aos="fade-left" data-aos-duration="800">
                    <img src="assets/images/hero-placeholder.jpg" alt="Online Shopping in Ghana">
                </div>
            </div>
        </section>

        <!-- Swiping Images Section -->
        <section class="carousel-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">Trending Now</h2>
                <div class="carousel-wrapper" data-aos="fade-up" data-aos-delay="200">
                    <div class="carousel-images">
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Online Shopping">
                            <div class="carousel-overlay">
                                <h3>Summer Collection</h3>
                                <p>Up to 40% off</p>
                                <a href="pages/register.php" class="btn btn-outline">Shop Now</a>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Fashion">
                            <div class="carousel-overlay">
                                <h3>New Arrivals</h3>
                                <p>Fresh styles just in</p>
                                <a href="pages/register.php" class="btn btn-outline">Explore</a>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Electronics">
                            <div class="carousel-overlay">
                                <h3>Tech Deals</h3>
                                <p>Latest gadgets</p>
                                <a href="pages/register.php" class="btn btn-outline">Discover</a>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1513708922415-9fa7e3b1c8e0?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Home & Office">
                            <div class="carousel-overlay">
                                <h3>Home Essentials</h3>
                                <p>Transform your space</p>
                                <a href="pages/register.php" class="btn btn-outline">Shop Now</a>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=600&q=80" class="carousel-img" alt="Gadgets">
                            <div class="carousel-overlay">
                                <h3>Smart Gadgets</h3>
                                <p>Tech for everyday life</p>
                                <a href="pages/register.php" class="btn btn-outline">Browse</a>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
                    <div class="carousel-dots"></div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">Why Shop With Us?</h2>
                <div class="features-grid">
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Free Delivery</h3>
                        <p>Enjoy free delivery on orders above GHS 200 in Accra and Kumasi.</p>
                    </div>
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3>Secure Payment</h3>
                        <p>Pay easily and securely with Mobile Money, Visa, or Mastercard.</p>
                    </div>
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="feature-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3>Easy Returns</h3>
                        <p>Not satisfied? Return your items within 7 days for a full refund.</p>
                    </div>
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3>Quality Assurance</h3>
                        <p>All products are verified for quality before reaching you.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Top Selling Products Section -->
        <section class="top-selling-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">Top Selling Products</h2>
                <div class="products-grid">
                    <div class="product-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="product-badge">Bestseller</div>
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
                    <div class="product-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="product-badge">Trending</div>
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
                    <div class="product-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="product-badge">Popular</div>
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
                    <div class="product-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="product-badge">Sale</div>
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
                <div class="flash-header" data-aos="fade-up">
                    <h2 class="section-title">Flash Sales</h2>
                    <div class="countdown-timer" id="countdown">
                        <span class="time-box">
                            <span class="time-value" id="hours">24</span>
                            <span class="time-label">HRS</span>
                        </span>
                        <span class="time-separator">:</span>
                        <span class="time-box">
                            <span class="time-value" id="minutes">00</span>
                            <span class="time-label">MINS</span>
                        </span>
                        <span class="time-separator">:</span>
                        <span class="time-box">
                            <span class="time-value" id="seconds">00</span>
                            <span class="time-label">SECS</span>
                        </span>
                    </div>
                </div>
                <div class="products-grid">
                    <div class="product-card flash-product" data-aos="fade-up" data-aos-delay="100">
                        <div class="flash-badge">-33%</div>
                        <img src="assets/images/products/flash1.jpg" alt="Flash 1" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Bluetooth Speaker</h3>
                            <div class="price-container">
                                <span class="product-price">GHS 99</span>
                                <span class="old-price">GHS 149</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                    <div class="product-card flash-product" data-aos="fade-up" data-aos-delay="200">
                        <div class="flash-badge">-34%</div>
                        <img src="assets/images/products/flash2.jpg" alt="Flash 2" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Power Bank 10,000mAh</h3>
                            <div class="price-container">
                                <span class="product-price">GHS 79</span>
                                <span class="old-price">GHS 120</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                    <div class="product-card flash-product" data-aos="fade-up" data-aos-delay="300">
                        <div class="flash-badge">-40%</div>
                        <img src="assets/images/products/flash3.jpg" alt="Flash 3" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Men's Watch</h3>
                            <div class="price-container">
                                <span class="product-price">GHS 59</span>
                                <span class="old-price">GHS 99</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                    <div class="product-card flash-product" data-aos="fade-up" data-aos-delay="400">
                        <div class="flash-badge">-35%</div>
                        <img src="assets/images/products/flash4.jpg" alt="Flash 4" class="product-image">
                        <div class="product-info">
                            <h3 class="product-title">Kitchen Set</h3>
                            <div class="price-container">
                                <span class="product-price">GHS 129</span>
                                <span class="old-price">GHS 199</span>
                            </div>
                            <a href="pages/register.php" class="btn product-btn">Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Popular Categories -->
        <section class="categories-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">Popular Categories</h2>
                <div class="categories-grid">
                    <a href="pages/register.php" class="category-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="category-image">
                            <img src="assets/images/phones.jpg" alt="Phones & Tablets">
                            <div class="category-overlay"></div>
                        </div>
                        <h3>Phones & Tablets</h3>
                        <p>Latest smartphones and tablets</p>
                    </a>
                    <a href="pages/register.php" class="category-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="category-image">
                            <img src="assets/images/fashion.jpg" alt="Fashion">
                            <div class="category-overlay"></div>
                        </div>
                        <h3>Fashion</h3>
                        <p>Clothing, shoes & accessories</p>
                    </a>
                    <a href="pages/register.php" class="category-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="category-image">
                            <img src="assets/images/electronics.jpg" alt="Electronics">
                            <div class="category-overlay"></div>
                        </div>
                        <h3>Electronics</h3>
                        <p>Gadgets and electronics</p>
                    </a>
                    <a href="pages/register.php" class="category-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="category-image">
                            <img src="assets/images/home.jpg" alt="Home & Office">
                            <div class="category-overlay"></div>
                        </div>
                        <h3>Home & Office</h3>
                        <p>Furniture and supplies</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">What Our Customers Say</h2>
                <div class="testimonials-container" data-aos="fade-up">
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p>"The delivery was super fast and the product quality exceeded my expectations. Will definitely shop again!"</p>
                            <div class="testimonial-author">
                                <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Customer">
                                <div>
                                    <h4>Ama Mensah</h4>
                                    <p>Accra</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <p>"The variety of products is amazing. I found everything I needed in one place at great prices."</p>
                            <div class="testimonial-author">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Customer">
                                <div>
                                    <h4>Kwame Adjei</h4>
                                    <p>Kumasi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p>"Customer service was exceptional when I had an issue with my order. They resolved it quickly and professionally."</p>
                            <div class="testimonial-author">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Customer">
                                <div>
                                    <h4>Abena Sarpong</h4>
                                    <p>Tema</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Vendor Spotlight Section -->
        <section class="vendors-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">Featured Vendors</h2>
                <div class="vendors-grid">
                    <div class="vendor-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="vendor-image">
                            <img src="https://images.unsplash.com/photo-1560472355-536de3962603?auto=format&fit=crop&w=400&q=80" alt="TechGadgets">
                        </div>
                        <div class="vendor-info">
                            <h3>TechGadgets</h3>
                            <div class="vendor-rating">
                                <i class="fas fa-star"></i>
                                <span>4.8 (245 reviews)</span>
                            </div>
                            <p>Latest tech gadgets and accessories</p>
                            <a href="pages/register.php" class="vendor-link">Visit Store <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="vendor-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="vendor-image">
                            <img src="https://images.unsplash.com/photo-1566206091558-7f218b696731?auto=format&fit=crop&w=400&q=80" alt="FashionHub">
                        </div>
                        <div class="vendor-info">
                            <h3>FashionHub</h3>
                            <div class="vendor-rating">
                                <i class="fas fa-star"></i>
                                <span>4.7 (189 reviews)</span>
                            </div>
                            <p>Trendy clothing and accessories</p>
                            <a href="pages/register.php" class="vendor-link">Visit Store <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="vendor-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="vendor-image">
                            <img src="https://images.unsplash.com/photo-1586023492125-27a3d5a2b2bc?auto=format&fit=crop&w=400&q=80" alt="HomeEssentials">
                        </div>
                        <div class="vendor-info">
                            <h3>HomeEssentials</h3>
                            <div class="vendor-rating">
                                <i class="fas fa-star"></i>
                                <span>4.9 (312 reviews)</span>
                            </div>
                            <p>Everything for your home</p>
                            <a href="pages/register.php" class="vendor-link">Visit Store <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container" data-aos="fade-up">
                <h2>Ready to Start Shopping?</h2>
                <p>Join thousands of satisfied customers who shop with confidence</p>
                <div class="cta-actions">
                    <a href="pages/register.php" class="btn btn-primary btn-lg pulse-animation">Create Free Account</a>
                    <span class="cta-note">No credit card required</span>
                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- JavaScript Files -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/landing.js"></script>
    <script>
        // Initialize AOS (Animate On Scroll)
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 100
            });
        });
    </script>
</body>
</html>