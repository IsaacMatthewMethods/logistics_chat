php
<?php if (isLoggedIn()): ?>
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; <?php echo date("Y"); ?> Logistics Chat System. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Use</a>
            </div>
        </div>
    </footer>
<?php endif; ?>