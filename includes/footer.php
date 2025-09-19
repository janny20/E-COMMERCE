    </main>
<!-- Footer -->
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