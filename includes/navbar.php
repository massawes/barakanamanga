<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$base = base_url();

$navLinks = [
    'index.php'     => 'Home',
    'notes.php'     => 'Notes',
    'summaries.php' => 'Summary',
    'theory.php'    => 'Theory Past Papers',
    'practical.php' => 'Form 4 Practical',
    'software.php'  => 'Software',
    'others.php'    => 'Others',
    'gallery.php'   => 'Gallery',
    // 'about.php'  => 'About',   // temporarily removed from navigation
    // 'contact.php' => 'Contact', // temporarily removed from navigation
];
?>
<header class="site-header">
    <div class="navbar">
        <a class="brand" href="<?= e($base) ?>/index.php">
            <img src="<?= e($base) ?>/assets/images/school-logo.png" alt="Namanga Secondary School logo" class="brand-logo" onerror="this.style.display='none'">
            <span class="brand-text">
                <span class="brand-name">NAMANGA SECONDARY SCHOOL</span>
                <span class="brand-tagline">Digital Resource Centre</span>
            </span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <ul>
                <?php foreach ($navLinks as $file => $label): ?>
                    <li>
                        <a href="<?= e($base) ?>/<?= e($file) ?>"
                           class="<?= $currentPage === $file ? 'active' : '' ?>">
                            <?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="nav-search">
                    <form action="<?= e($base) ?>/search.php" method="get" class="nav-search-form">
                        <span class="nav-search-icon" aria-hidden="true"><?= svg_icon('search', 'icon-svg icon-svg-small') ?></span>
                        <input type="text" name="q" placeholder="Search resources..."
                               value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search resources">
                        <button type="submit" aria-label="Search"><?= svg_icon('search', 'icon-svg icon-svg-small') ?></button>
                    </form>
                </li>
                <li class="nav-auth">
                    <?php if (is_admin_logged_in()): ?>
                        <a href="<?= e($base) ?>/admin/dashboard.php" class="nav-auth-link">Dashboard</a>
                    <?php else: ?>
                        <a href="<?= e($base) ?>/admin/login.php" class="nav-auth-link">Staff Login</a>
                        <a href="<?= e($base) ?>/admin/register.php" class="nav-auth-link nav-auth-register">Register</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>
