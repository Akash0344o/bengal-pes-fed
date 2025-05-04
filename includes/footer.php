</main>
        
        <footer class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col">
                        <h3>Bengal Pes Federation</h3>
                        <p>Promoting football excellence in West Bengal since 2023.</p>
                    </div>
                    
                    <div class="footer-col">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="<?= BASE_URL ?>">Home</a></li>
                            <li><a href="<?= BASE_URL ?>/teams.php">Teams</a></li>
                            <li><a href="<?= BASE_URL ?>/tournaments.php">Tournaments</a></li>
                            <li><a href="<?= BASE_URL ?>/media.php">Media Gallery</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-col">
                        <h3>Contact Us</h3>
                        <address>
                            <p><i class="fas fa-map-marker-alt"></i> Kolkata, West Bengal</p>
                            <p><i class="fas fa-phone"></i> +91 9876543210</p>
                            <p><i class="fas fa-envelope"></i> contact@bengalpesfed.org</p>
                        </address>
                    </div>
                    
                    <div class="footer-col">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> Bengal Pes Federation. All rights reserved.</p>
                </div>
            </div>
        </footer>
        
        <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
        <?php if (isset($customScript)): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= $customScript ?>.js"></script>
        <?php endif; ?>
    </body>
</html>