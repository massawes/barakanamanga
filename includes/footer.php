<?php $base = base_url(); ?>
</main>
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <strong>NAMANGA SECONDARY SCHOOL</strong>
            <p>ICT &amp; Computer Science<br>Digital Resource Centre</p>
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
                <li><a href="<?= e($base) ?>/others.php">Others</a></li>
                <li><a href="<?= e($base) ?>/gallery.php">Gallery</a></li>
            </ul>
        </div>

        <div class="footer-admin">
            <h4>Staff</h4>
            <ul>
                <li><a href="<?= e($base) ?>/admin/login.php">Admin Login</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Namanga Secondary School. All Rights Reserved.
    </div>
</footer>
<?php include __DIR__ . '/assistant-widget.php'; ?>
<script src="<?= e($base) ?>/assets/js/script.js"></script>
</body>
</html>
