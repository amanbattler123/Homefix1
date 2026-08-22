<footer class="footer-modern">
    <div class="container">
        <!-- Main Footer Content -->
        <div class="footer-content">
            <!-- Company Info Section -->
            <div class="footer-section">
                <div class="footer-logo">
                    <i class="fas fa-tools"></i>
                    <span>HomeFix Pro</span>
                </div>
                <p class="footer-description">Your trusted partner for home services in Addis Ababa – connecting you with reliable local professionals.</p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="X">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="footer-section">
                <h4 class="footer-heading">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="footer-link">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php" class="footer-link">Services</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="footer-link">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="footer-link">Contact</a></li>
                    <?php if (!in_array(basename($_SERVER['PHP_SELF']), ['privacy.php', 'terms.php'], true)): ?>
                        <li><a href="<?php echo SITE_URL; ?>/faq.php" class="footer-link">FAQ</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/blog.php" class="footer-link">Blog</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Services Section -->
            <div class="footer-section">
                <h4 class="footer-heading">Our Services</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/services.php#plumbing" class="footer-link">Plumbing</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php#electrical" class="footer-link">Electrical</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php#carpentry" class="footer-link">Carpentry</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php#painting" class="footer-link">Painting</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php#cleaning" class="footer-link">Cleaning</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php#maintenance" class="footer-link">Maintenance</a></li>
                </ul>
            </div>

            <!-- Contact Info Section -->
            <div class="footer-section">
                <h4 class="footer-heading">Contact Info</h4>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <div>
                            <p class="contact-text">Bole, Addis Ababa</p>
                            <p class="contact-subtext">Ethiopia</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <div>
                            <p class="contact-text">+251 11 123 4567</p>
                            <p class="contact-subtext">Mon–Fri 8:00am–8:00pm</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope contact-icon"></i>
                        <div>
                            <p class="contact-text">info@homefixpro.et</p>
                            <p class="contact-subtext">Support available 7 days a week</p>
                        </div>
                    </div>
                </div>
                
                <!-- Newsletter Subscription -->
                <div class="newsletter">
                    <h5 class="newsletter-title">Stay Updated</h5>
                    <p class="newsletter-text">Subscribe for home care tips, offers, and updates for Addis Ababa</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="newsletter-input" placeholder="Your email address" required>
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="copyright">&copy; <?php echo date('Y'); ?> HomeFix Pro. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="<?php echo SITE_URL; ?>/privacy.php" class="footer-bottom-link">Privacy Policy</a>
                    <a href="<?php echo SITE_URL; ?>/terms.php" class="footer-bottom-link">Terms of Service</a>
                    <?php if (!in_array(basename($_SERVER['PHP_SELF']), ['privacy.php', 'terms.php'], true)): ?>
                        <a href="<?php echo SITE_URL; ?>/sitemap.php" class="footer-bottom-link">Sitemap</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>
</footer>

<!-- Include JavaScript files -->
<script src="<?php echo SITE_URL; ?>/assets/js/footer.js"></script>
</body>
</html>