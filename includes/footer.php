<?php $base = base_url(); ?>
</main>
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <h4>NAMANGA SECONDARY SCHOOL</h4>
            <img src="<?= e($base) ?>/assets/images/logo-white.png" alt="Namanga Secondary School logo" class="footer-logo">
            <em class="footer-motto">&ldquo;Education for Liberty and Prosperity&rdquo;</em>
        </div>

        <div class="footer-contact">
            <h4>CONTACT US</h4>
            <p>Namanga Secondary School<br>P.O. Box 8560, Namanga<br>Longido District<br>Arusha - Tanzania</p>
            <p><strong>Phone:</strong> <a href="tel:+255695389537">+255 695 389 537</a><br>
            <strong>Email:</strong> <a href="mailto:namangasecondary@gmail.com">namangasecondary@gmail.com</a></p>
            <?php if (!empty($socialLinks)): ?>
                <ul class="footer-social-icons">
                    <?php foreach ($socialLinks as $socialName => [$socialUrl, $socialPath]): ?>
                        <li><a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener" aria-label="<?= e($socialName) ?>" title="<?= e($socialName) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($socialPath) ?>"/></svg></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="footer-links">
            <h4>USEFUL LINKS</h4>
            <ul>
                <li><a href="https://www.necta.go.tz" target="_blank" rel="noopener">National Examinations Council of Tanzania (NECTA)</a></li>
                <li><a href="https://www.moe.go.tz" target="_blank" rel="noopener">Ministry of Education, Science and Technology</a></li>
                <li><a href="https://www.tamisemi.go.tz" target="_blank" rel="noopener">President's Office - Regional Administration and Local Government (TAMISEMI)</a></li>
                <li><a href="https://www.tie.go.tz" target="_blank" rel="noopener">Tanzania Institute of Education (TIE)</a></li>
                <li><a href="https://namangaresults.freepage.cc" target="_blank" rel="noopener">Student Result System</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>QUICK LINKS</h4>
            <ul>
                <li><a href="<?= e($base) ?>/about.php">About Us</a></li>
                <li><a href="<?= e($base) ?>/notes.php">Notes</a></li>
                <li><a href="<?= e($base) ?>/theory.php">Theory Past Papers</a></li>
                <li><a href="<?= e($base) ?>/practical.php">Form 4 Practical</a></li>
                <li><a href="<?= e($base) ?>/gallery.php">The Latest</a></li>
                <li><a href="<?= e($base) ?>/admin/login.php">Staff Login</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>This website is developed and maintained by <strong>Namanga Secondary School</strong></p>
        <p>&copy; <?= date('Y') ?> Namanga Secondary School. All Rights Reserved.</p>
    </div>
</footer>
<?php include __DIR__ . '/assistant-widget.php'; ?>
<script src="<?= e($base) ?>/assets/js/script.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
