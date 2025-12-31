<?php
/**
 * BingeTV Standardized Footer
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <i class="fas fa-satellite-dish"></i>
                    <span>BingeTV</span>
                </div>
                <p>Premium 8K TV streaming service for Kenya. Stream thousands of channels on any device, anywhere.</p>
                <div class="social-links">
                    <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://wa.me/254768704834" target="_blank"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="/">Home</a></li>
                    <li><a href="/channels">Live Channels</a></li>
                    <li><a href="/gallery">Content Gallery</a></li>
                    <li><a href="/apps">Download Apps</a></li>
                    <li><a href="/support">Help & Support</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Account</h4>
                <ul class="footer-links">
                    <li><a href="/login">User Login</a></li>
                    <li><a href="/register">Create Account</a></li>
                    <li><a href="/user/dashboard">My Dashboard</a></li>
                    <li><a href="/user/subscriptions/subscribe">Get Package</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms of Service</a></li>
                    <li><a href="/refund">Refund Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> BingeTV Kenya. All rights reserved.</p>
                <div class="footer-payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <span class="mpesa-badge">M-PESA</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<div class="whatsapp-float">
    <a href="https://wa.me/254768704834?text=Hello%2C%20I%20need%20help%20with%20BingeTV" target="_blank"
        class="whatsapp-btn">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-text">Chat with us</span>
    </a>
</div>

<!-- JavaScript -->
<script src="/js/main.js"></script>
<script src="/js/animations.js"></script>
<?php if (strpos($_SERVER['PHP_SELF'], 'index.php') !== false || strpos($_SERVER['PHP_SELF'], 'apps.php') !== false || $_SERVER['PHP_SELF'] == '/' || $_SERVER['PHP_SELF'] == ''): ?>
    <script src="/js/enhanced.js"></script>
<?php endif; ?>