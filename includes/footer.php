    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>E-Shop</h3>
                    <p>Your one-stop destination for all your shopping needs. Quality products at affordable prices.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>pages/index.php">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/products.php">Products</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <?php
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        $query = "SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name LIMIT 5";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<li><a href="' . BASE_URL . 'pages/products.php?category=' . $row['slug'] . '">' . $row['name'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Street, New York, USA</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope"></i> info@eshop.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> E-Shop. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) == 'cart.php'): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/cart.js"></script>
    <?php endif; ?>
    <?php if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false || strpos($_SERVER['PHP_SELF'], 'vendor/') !== false): ?>
    <script src="<?php echo BASE_URL; ?>assets/js/admin.js"></script>
    <?php endif; ?>
</body>
</html>