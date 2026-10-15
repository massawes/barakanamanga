    </main>
    <footer class="adm-footer">
        <a href="<?= e(base_url()) ?>/index.php" class="adm-footer-btn" target="_blank" rel="noopener"><?= svg_icon('home', 'icon-svg icon-svg-small') ?> View website</a>
        <span><strong>Copyright &copy; <?= date('Y') ?> Namanga Secondary School.</strong> All rights reserved.</span>
    </footer>
</div>
<script src="<?= e(base_url()) ?>/assets/js/script.js"></script>
<script>
// Admin menu button: hides/shows the sidebar on computers, slides it in on phones.
(function () {
    var toggle = document.getElementById('admToggle');
    var overlay = document.getElementById('admOverlay');
    if (!toggle) { return; }
    var small = function () { return window.matchMedia('(max-width: 900px)').matches; };
    toggle.addEventListener('click', function () {
        document.body.classList.toggle(small() ? 'adm-sidebar-open' : 'adm-sidebar-hidden');
    });
    overlay.addEventListener('click', function () { document.body.classList.remove('adm-sidebar-open'); });
})();
</script>
</body>
</html>
