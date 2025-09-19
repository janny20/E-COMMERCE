<?php require_once __DIR__ . '/config.php'; ?>
<!-- Footer -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/footer.css">
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-brand">
                    <a href="<?php echo BASE_URL; ?>/pages/index.php" class="footer-logo">
                        <i class="fas fa-store"></i> <?php echo SITE_NAME; ?>
                    </a>
                    <p class="footer-description">
                        Your trusted multi-vendor marketplace. Connecting buyers and sellers with quality products and services.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/pages/index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/products.php"><i class="fas fa-shopping-bag"></i> Products</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/pages/help.php"><i class="fas fa-question-circle"></i> Help Center</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/faq.php"><i class="fas fa-comments"></i> FAQ</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/shipping.php"><i class="fas fa-truck"></i> Shipping Info</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/returns.php"><i class="fas fa-exchange-alt"></i> Returns Policy</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/privacy.php"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p class="footer-description">Subscribe to our newsletter for updates and exclusive offers.</p>
                    <form class="newsletter-form">
                        <input type="email" class="newsletter-input" placeholder="Enter your email" required>
                        <button type="submit" class="newsletter-btn">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>