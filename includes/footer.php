    </main>
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Logistics Chat System. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
            </div>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) == 'chat.php'): ?>
        <script src="<?php echo BASE_URL; ?>assets/js/chat.js"></script>
    <?php endif; ?>
</body>
</html>