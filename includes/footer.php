<?php require_once __DIR__ . '/config.php'; ?>
<!-- Footer -->
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
                    <h3>Shop</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>pages/products.php">All Products</a></li>
                        <li><a href="#">Today's Deals</a></li>
                        <li><a href="#">New Arrivals</a></li>
                        <li><a href="#">Featured Products</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/register.php?type=vendor">Become a Vendor</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>My Account</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>pages/about.php">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/profile.php">My Profile</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/orders.php">My Orders</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/wishlist.php">Wishlist</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/cart.php">Shopping Cart</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/contact.php">Contact Us</a></li>
                        <li><a href="#">Shipping Information</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
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
<!-- Back to Top Button -->
<a href="#" class="back-to-top-btn" title="Back to Top"><i class="fas fa-arrow-up"></i></a>
<!-- Cookie Consent Banner -->
<div class="cookie-consent-banner" id="cookie-consent-banner">
    <div class="cookie-content">
        <p>We use cookies to improve your experience on our site. By continuing to use our site, you agree to our <a href="<?php echo BASE_URL; ?>pages/privacy.php">cookie policy</a>.</p>
    </div>
    <div class="cookie-actions">
        <a href="#" id="cookie-settings-link" class="cookie-settings-link">Cookie Settings</a>
        <button class="btn btn-outline" id="cookie-decline-btn">Decline</button>
        <button class="btn btn-primary" id="cookie-accept-btn">Accept</button>
    </div>
</div>
<!-- Cookie Settings Modal -->
<div class="modal" id="cookie-settings-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cookie Settings</h3>
            <button class="modal-close" id="cookie-settings-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Manage your cookie preferences. You can enable or disable different types of cookies below. Note that strictly necessary cookies cannot be disabled.</p>
            <div class="cookie-setting-item">
                <div class="cookie-setting-info">
                    <h4>Strictly Necessary Cookies</h4>
                    <p>These cookies are essential for the website to function and cannot be switched off.</p>
                </div>
                <div class="toggle-switch">
                    <input type="checkbox" id="cookie-necessary" class="toggle-input" checked disabled>
                    <label for="cookie-necessary" class="toggle-label"></label>
                </div>
            </div>
            <div class="cookie-setting-item">
                <div class="cookie-setting-info">
                    <h4>Performance & Analytics Cookies</h4>
                    <p>These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site.</p>
                </div>
                <div class="toggle-switch">
                    <input type="checkbox" id="cookie-performance" class="toggle-input" checked>
                    <label for="cookie-performance" class="toggle-label"></label>
                </div>
            </div>
            <div class="cookie-setting-item">
                <div class="cookie-setting-info">
                    <h4>Targeting & Advertising Cookies</h4>
                    <p>These cookies may be set through our site by our advertising partners to build a profile of your interests.</p>
                </div>
                <div class="toggle-switch">
                    <input type="checkbox" id="cookie-targeting" class="toggle-input">
                    <label for="cookie-targeting" class="toggle-label"></label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" id="cookie-save-settings">Save My Preferences</button>
        </div>
    </div>
</div>
<!-- Main JS File -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
