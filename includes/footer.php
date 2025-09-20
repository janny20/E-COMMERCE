<?php require_once __DIR__ . '/config.php'; ?>
<!-- Footer -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/footer.css?v=2">
<footer class="footer" style="background-color: #232f3e; color: #f1f1f1; padding: 60px 0 20px 0; font-size: 0.95rem;">
    <div class="container">
        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-brand">
                    <a href="<?php echo BASE_URL; ?>landing.php" class="footer-logo" style="color: #fff; font-size: 2rem; font-weight: 700; margin-bottom: 15px; display: inline-block;"><?php echo SITE_NAME; ?></a>
                    <p class="footer-description">
                        Your trusted multi-vendor marketplace. Connecting buyers and sellers with quality products and services.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="Facebook" style="color: #fff; background-color: rgba(255,255,255,0.1); width: 40px; height: 40px; display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; margin-right: 10px; transition: background-color 0.3s;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter" style="color: #fff; background-color: rgba(255,255,255,0.1); width: 40px; height: 40px; display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; margin-right: 10px; transition: background-color 0.3s;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram" style="color: #fff; background-color: rgba(255,255,255,0.1); width: 40px; height: 40px; display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; margin-right: 10px; transition: background-color 0.3s;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn" style="color: #fff; background-color: rgba(255,255,255,0.1); width: 40px; height: 40px; display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; margin-right: 10px; transition: background-color 0.3s;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3 style="color: #fff; margin-bottom: 20px; font-size: 1.2rem;">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>pages/home.php">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/products.php">Products</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/register.php?type=vendor">Become a Vendor</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3 style="color: #fff; margin-bottom: 20px; font-size: 1.2rem;">Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns Policy</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3 style="color: #fff; margin-bottom: 20px; font-size: 1.2rem;">Newsletter</h3>
                    <p class="footer-description">Subscribe to our newsletter for updates and exclusive offers.</p>
                    <form class="newsletter-form" style="display: flex; margin-top: 15px;">
                        <input type="email" class="newsletter-input" placeholder="Enter your email" required style="flex: 1; padding: 12px; border: 1px solid #445262; background: #2e3c4b; color: #fff; border-radius: 4px 0 0 4px; outline: none;">
                        <button type="submit" class="newsletter-btn" style="padding: 12px 15px; background-color: var(--primary-color); border: none; color: #fff; font-weight: 600; cursor: pointer; border-radius: 0 4px 4px 0;">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom" style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #3a4a5a; color: #99a9b9;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<<<<<<< HEAD
<!-- Main JS File -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
=======
<style>
    .footer-links li { margin-bottom: 10px; }
    .footer-links a { color: #bdc3c7; transition: color 0.3s, padding-left 0.3s; }
    .footer-links a:hover { color: var(--primary-color); padding-left: 5px; }
    .social-link:hover { background-color: var(--primary-color) !important; }
    @media (max-width: 768px) {
        .footer-main {
            flex-direction: column;
            text-align: center;
        }
        .footer-links {
            display: inline-block; /* This allows text-align:center to work on the ul */
        }
        .footer-social { justify-content: center; }
        .newsletter-form { justify-content: center; }
    }
</style>
>>>>>>> 1f7d65395f4b56b49792a2c435502977a4ad4867
