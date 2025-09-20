<?php require_once __DIR__ . '/config.php'; ?>
<!-- Footer -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/footer.css?v=2">
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-brand">
                    <a href="<?php echo BASE_URL; ?>landing.php" class="footer-logo"><?php echo SITE_NAME; ?></a>
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
                        <li><a href="<?php echo BASE_URL; ?>pages/home.php">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/products.php">Products</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/register.php?type=vendor">Become a Vendor</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns Policy</a></li>
                        <li><a href="#">Privacy Policy</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<!-- Main JS File -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
