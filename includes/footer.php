<?php $base = base_url(); ?>
</main>
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <img src="<?= e($base) ?>/assets/images/logo-white.png" alt="Namanga Secondary School logo" class="footer-logo">
            <strong>NAMANGA SECONDARY SCHOOL</strong>
            <em class="footer-motto">&ldquo;Education for Liberty and Prosperity&rdquo;</em>
            <p>Secondary School (Form 1&ndash;4) &middot; NECTA S2911<br>P.O. Box 8560, Namanga<br>Longido District, Arusha, Tanzania<br>Tel: <a href="tel:+255695389537">+255 695 389 537</a></p>
            <p class="footer-social">
                <a href="https://www.facebook.com/namangasecondaryschool" target="_blank" rel="noopener">Follow Namanga Secondary School (S.2911), Tanzania on Facebook</a><br>
                <a href="https://www.youtube.com/@namangasecondaryschool" target="_blank" rel="noopener">Watch Namanga Secondary School (S.2911), Tanzania on YouTube</a><br>
                <a href="https://www.tiktok.com/@namangasecondaryschool" target="_blank" rel="noopener">Follow us on TikTok</a><br>
                <a href="https://wa.me/255695389537?text=Habari%20Namanga%20Secondary%20School" target="_blank" rel="noopener">Contact us on WhatsApp: +255 695 389 537</a>
            </p>
        </div>

        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= e($base) ?>/index.php">Home</a></li>
                <li><a href="<?= e($base) ?>/notes.php">Notes</a></li>
                <li><a href="<?= e($base) ?>/summaries.php">Summary</a></li>
                <li><a href="<?= e($base) ?>/theory.php">Theory Past Papers</a></li>
                <li><a href="<?= e($base) ?>/practical.php">Form 4 Practical</a></li>
                <li><a href="<?= e($base) ?>/software.php">Software</a></li>
                <li><a href="<?= e($base) ?>/others.php">Other Subjects</a></li>
                <li><a href="<?= e($base) ?>/gallery.php">The Latest</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Our School</h4>
            <ul>
                <li><a href="<?= e($base) ?>/about.php">About Us</a></li>
                <li><a href="<?= e($base) ?>/mission-vision.php">Mission &amp; Vision</a></li>
                <li><a href="<?= e($base) ?>/history.php">Our History</a></li>
                <li><a href="<?= e($base) ?>/our-students.php">Our Students</a></li>
            </ul>
        </div>

        <div class="footer-admin">
            <h4>Staff</h4>
            <ul>
                <li><a href="<?= e($base) ?>/admin/login.php">Staff Login</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Namanga Secondary School. All Rights Reserved.
    </div>
</footer>
<?php include __DIR__ . '/assistant-widget.php'; ?>
<script src="<?= e($base) ?>/assets/js/script.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
