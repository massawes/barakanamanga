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
            <p>Namanga Secondary School<br>P.O. Box 6560, Namanga<br>Longido District<br>Arusha - Tanzania</p>
            <p><strong>Phone:</strong> <a href="tel:+255695389537">+255 695 389 537</a><br>
            <strong>Email:</strong> <a href="mailto:namanga@longido.dc.go.tz">namanga@longido.dc.go.tz</a></p>
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
                <li><a href="https://results.namangasecondary.sc.tz" target="_blank" rel="noopener">Student Result System</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>QUICK LINKS</h4>
            <ul>
                <?php if (is_portal()): ?>
                    <li><a href="<?= e($base) ?>/index.php">Portal Home</a></li>
                    <li><a href="<?= e(site_link('notes.php')) ?>">Notes</a></li>
                    <li><a href="<?= e(site_link('theory.php')) ?>">Theory Past Papers</a></li>
                    <li><a href="<?= e(site_link('practical.php')) ?>">Form 4 Practical</a></li>
                    <li><a href="<?= e(other_site_home()) ?>">School Website</a></li>
                <?php else: ?>
                    <li><a href="<?= e(site_link('about.php')) ?>">About the School</a></li>
                    <li><a href="<?= e(site_link('academics.php')) ?>">Academics</a></li>
                    <li><a href="<?= e(site_link('admissions.php')) ?>">Admissions</a></li>
                    <li><a href="<?= e(site_link('announcements.php')) ?>">News &amp; Announcements</a></li>
                    <li><a href="<?= e(site_link('gallery.php')) ?>">Gallery</a></li>
                    <li><a href="<?= e(other_site_home()) ?>">Student Portal (Masomo)</a></li>
                <?php endif; ?>
                <li><a href="<?= e($base) ?>/admin/login.php">Staff Login</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>This website is developed and maintained by <strong>Namanga Secondary School</strong></p>
        <p>&copy; <?= date('Y') ?> Namanga Secondary School. All Rights Reserved.</p>
    </div>
</footer>
<?php if (is_portal()) { include __DIR__ . '/assistant-widget.php'; } // Ask AI belongs to the Student Portal ?>
<script src="<?= e($base) ?>/assets/js/script.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
