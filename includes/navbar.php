<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$base = base_url();

// True when $href (e.g. "gallery.php?type=image") is the page being viewed,
// including its ?type= filter when the link has one.
$isCurrentLink = function (string $href) use ($currentPage): bool {
    $parts = parse_url($href);
    if (($parts['path'] ?? '') !== $currentPage) {
        return false;
    }
    parse_str($parts['query'] ?? '', $query);
    return ($query['type'] ?? '') === ($_GET['type'] ?? '');
};

$navLinks = [
    'index.php'     => 'Home',
    'about.php'     => [
        'label' => 'Our School',
        'children' => [
            'about.php'          => 'About Us',
            'mission-vision.php' => 'Mission & Vision',
            'history.php'        => 'Our History',
            'our-students.php'   => 'Our Students',
        ],
    ],
    'notes.php'     => 'Notes',
    'theory.php'    => 'Theory Past Papers',
    'practical.php' => 'Form 4 Practical',
    'gallery.php'   => [
        'label' => 'The Latest',
        'children' => [
            'gallery.php?type=image' => 'Photos',
            'gallery.php?type=video' => 'Videos',
            'gallery.php'            => 'All Media',
        ],
    ],
    'results.php'   => 'Results',
    // ICT Services has no page of its own: the label only opens the list.
    // To add another service, add one more line under 'children'
    // (a full https:// address opens in a new tab).
    '#ict-services' => [
        'label' => 'ICT Services',
        'children' => [
            'https://namangaresults.freepage.cc' => 'Student Result System',
        ],
    ],
    // 'contact.php' => 'Contact', // temporarily removed from navigation
];
?>
<header class="site-header">
    <div class="navbar">
        <a class="brand" href="<?= e($base) ?>/index.php">
            <img src="<?= e($base) ?>/assets/images/logo-navy.png" alt="Namanga Secondary School logo" class="brand-logo" onerror="this.style.display='none'">
            <span class="brand-text">
                <span class="brand-name">NAMANGA SECONDARY SCHOOL</span>
                <span class="brand-tagline">Longido &middot; Arusha &middot; Tanzania</span>
            </span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <div class="mobile-nav-head">
                <span class="brand-name">NAMANGA SECONDARY SCHOOL</span>
                <button type="button" class="nav-close" id="navClose" aria-label="Close menu"><?= svg_icon('close', 'icon-svg') ?></button>
            </div>
            <ul>
                <?php foreach ($navLinks as $file => $label): ?>
                    <?php if (is_array($label)): ?>
                        <?php $sectionActive = (bool) array_filter(array_keys($label['children']), fn ($href) => parse_url($href, PHP_URL_PATH) === $currentPage); ?>
                        <?php $menuOnly = str_starts_with($file, '#'); ?>
                        <li class="has-dropdown">
                            <a href="<?= $menuOnly ? e($file) : e($base) . '/' . e($file) ?>"
                               class="dropdown-trigger <?= $sectionActive ? 'active' : '' ?>" aria-haspopup="true"<?= $menuOnly ? ' data-menu-only' : '' ?>>
                                <?= e($label['label']) ?><?= svg_icon('chevron-down', 'icon-svg dropdown-caret') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($label['children'] as $childFile => $childLabel): ?>
                                    <?php $external = preg_match('#^https?://#i', $childFile) === 1; ?>
                                    <li>
                                        <?php if ($external): ?>
                                            <a href="<?= e($childFile) ?>" target="_blank" rel="noopener">
                                                <?= e($childLabel) ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= e($base) ?>/<?= e($childFile) ?>"
                                               class="<?= $isCurrentLink($childFile) ? 'active' : '' ?>">
                                                <?= e($childLabel) ?>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?= e($base) ?>/<?= e($file) ?>"
                               class="<?= $currentPage === $file ? 'active' : '' ?>">
                                <?= e($label) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="nav-search">
                    <form action="<?= e($base) ?>/search.php" method="get" class="nav-search-form">
                        <span class="nav-search-icon" aria-hidden="true"><?= svg_icon('search', 'icon-svg icon-svg-small') ?></span>
                        <input type="text" name="q" id="navSearchInput" placeholder="Search resources..."
                               value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search resources">
                        <button type="submit" aria-label="Search"><?= svg_icon('search', 'icon-svg icon-svg-small') ?></button>
                        <?php // Desktop: this square button opens the search box (see .nav-search-toggle). ?>
                        <label for="navSearchInput" class="nav-search-toggle" aria-hidden="true"><?= svg_icon('search', 'icon-svg') ?></label>
                    </form>
                </li>
                <li class="nav-auth">
                    <?php if (is_admin_logged_in()): ?>
                        <a href="<?= e($base) ?>/admin/dashboard.php" class="nav-auth-link">Dashboard</a>
                    <?php else: ?>
                        <a href="<?= e($base) ?>/admin/login.php" class="nav-auth-link">Staff Login</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>
