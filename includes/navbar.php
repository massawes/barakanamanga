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

// Two websites (see includes/site.php): the school website tells people about the school;
// the Student Portal (masomo.) holds the learning materials. Each has its own menu.
// A page of the other website gets its full address automatically (site_link()).
if (is_portal()) {
    $navLinks = [
        'index.php'     => 'Portal Home',
        'notes.php'     => 'Notes',
        'theory.php'    => 'Theory Past Papers',
        'practical.php' => 'Form 4 Practical',
        '#results'      => [
            'label' => 'Results',
            'children' => [
                'https://results.namangasecondary.sc.tz' => 'Student Result System',
                'results.php'                            => 'NECTA Results',
            ],
        ],
    ];
    $siteSwitch = ['School Website', other_site_home()];
} else {
    $navLinks = [
        'index.php'     => 'Home',
        'about.php'     => [
            'label' => 'About Us',
            'children' => [
                'about.php'          => 'About the School',
                'mission-vision.php' => 'Mission & Vision',
                'history.php'        => 'Our History',
                'our-students.php'   => 'Student Life',
            ],
        ],
        'academics.php'  => 'Academics',
        'admissions.php' => 'Admissions',
        '#results'      => [
            'label' => 'Results',
            'children' => [
                'results.php'                            => 'NECTA Results (Form 2 & 4)',
                'https://results.namangasecondary.sc.tz' => 'Student Result System',
            ],
        ],
        'announcements.php' => 'News',
        'gallery.php'   => [
            'label' => 'Gallery',
            'children' => [
                'gallery.php?type=image' => 'Photos',
                'gallery.php?type=video' => 'Videos',
            ],
        ],
        'contact.php'   => 'Contact',
    ];
    $siteSwitch = ['Student Portal', other_site_home()];
}

// Local link to a page of this website, or the full address when the page belongs to the other one.
$navHref = static function (string $file) use ($base): string {
    return $file === 'index.php' ? $base . '/index.php' : site_link($file);
};

// Social buttons in the top bar. To add one (e.g. Instagram), add a line here
// with its address and an icon path (24x24 viewBox).
$socialLinks = [
    'Facebook' => ['https://www.facebook.com/namangasecondaryschool',
        'M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.33-.04-1.56-.14-2.86-.14C11.93 2 10 3.66 10 6.7v2.8H7v4h3V22h4v-8.5z'],
    'WhatsApp' => ['https://wa.me/255695389537?text=Habari%20Namanga%20Secondary%20School',
        'M2 22l1.36-4.97A9.95 9.95 0 0 1 2 12C2 6.48 6.48 2 12 2s10 4.48 10 10-4.48 10-10 10a9.95 9.95 0 0 1-5.03-1.36L2 22zm6.39-14.69a.96.96 0 0 0-.37.1 1.3 1.3 0 0 0-.3.23c-.12.11-.18.21-.26.31A2.73 2.73 0 0 0 6.9 9.62c0 .49.13.97.33 1.41.41.9 1.08 1.86 1.97 2.74.21.21.42.43.65.63a9.45 9.45 0 0 0 3.84 2.05l.57.09c.19.01.37 0 .56-.01a2 2 0 0 0 .83-.23c.17-.09.24-.13.38-.22l.13-.09c.13-.1.22-.17.33-.29.08-.09.16-.19.21-.3.08-.16.16-.47.19-.73.02-.2.02-.31.01-.37 0-.11-.09-.22-.19-.27l-.58-.26s-.87-.38-1.4-.62a.5.5 0 0 0-.18-.04.48.48 0 0 0-.38.13c-.01 0-.07.06-.8.93a.35.35 0 0 1-.37.13 1.4 1.4 0 0 1-.19-.07l-.25-.11a6 6 0 0 1-1.58-1c-.12-.11-.24-.23-.36-.35a6.3 6.3 0 0 1-1.02-1.27l-.06-.09a.92.92 0 0 1-.1-.21c-.04-.15.06-.26.06-.26s.24-.27.36-.41c.1-.13.19-.25.26-.37.12-.19.16-.39.09-.54-.28-.68-.57-1.36-.87-2.04-.06-.13-.23-.23-.39-.25l-.16-.02a3.4 3.4 0 0 0-.4 0z'],
    'YouTube' => ['https://www.youtube.com/@namangasecondaryschool',
        'M12.24 4c.54 0 1.87.02 3.29.07l.5.02c1.43.07 2.86.18 3.57.38.94.27 1.69 1.04 1.94 2.02.4 1.56.45 4.6.46 5.34v.5c-.01.73-.06 3.78-.46 5.33-.25.99-1 1.76-1.94 2.03-.71.19-2.14.31-3.57.38l-.5.02c-1.42.06-2.75.07-3.29.07h-.49c-1.13 0-5.86-.06-7.36-.48-.94-.26-1.69-1.04-1.94-2.02C2.05 16.1 2 13.06 2 12.33v-.33c0-.73.05-3.78.45-5.34.26-.98 1-1.76 1.94-2.02 1.5-.42 6.23-.47 7.36-.48h.49zM10 8.5v7l6-3.5-6-3.5z'],
    'TikTok' => ['https://www.tiktok.com/@namangasecondaryschool',
        'M16 8.25v7.25a6.5 6.5 0 1 1-5-6.33v3.17a3.5 3.5 0 1 0 2 3.16V2h3a5 5 0 0 0 5 5v3a7.97 7.97 0 0 1-5-1.75z'],
];
?>
<div class="top-bar">
    <div class="top-bar-inner">
        <a href="tel:+255695389537" class="top-phone" aria-label="Call 0695 389 537">
            <span class="top-phone-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24 11.36 11.36 0 0 0 3.57.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg></span>
            <span>0695389537</span>
        </a>
        <ul class="top-social" aria-label="Follow Namanga Secondary School">
            <?php foreach ($socialLinks as $socialName => [$socialUrl, $socialPath]): ?>
                <li>
                    <a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener" aria-label="<?= e($socialName) ?>" title="<?= e($socialName) ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($socialPath) ?>"/></svg>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if (is_admin_logged_in()): ?>
            <a href="<?= e($base) ?>/admin/dashboard.php" class="top-login">Dashboard</a>
        <?php else: ?>
            <a href="<?= e($base) ?>/admin/login.php" class="top-login">Staff Login</a>
        <?php endif; ?>
    </div>
</div>
<?php // Big white header (owner 2026-11-08, like atc.ac.tz): coat of arms | names + motto | school logo. ?>
<div class="gov-header">
    <a class="gov-header-inner" href="<?= e($base) ?>/index.php">
        <img src="<?= e($base) ?>/assets/images/coat-of-arms.png" alt="Coat of arms of Tanzania" class="gov-arms" width="240" height="278">
        <span class="gov-text">
            <span class="gov-country">The United Republic of Tanzania</span>
            <span class="gov-school">Namanga Secondary School</span>
            <span class="gov-motto"><?= is_portal() ? 'Student Learning Portal &middot; Masomo' : 'Education for Liberty and Prosperity' ?></span>
        </span>
        <img src="<?= e($base) ?>/assets/images/logo-navy.png" alt="Namanga Secondary School logo" class="gov-logo" width="480" height="279">
    </a>
</div>
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
                            <a href="<?= $menuOnly ? e($file) : e($navHref($file)) ?>"
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
                                            <a href="<?= e($navHref($childFile)) ?>"
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
                            <a href="<?= e($navHref($file)) ?>"
                               class="<?= $currentPage === $file ? 'active' : '' ?>">
                                <?= e($label) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (is_portal()): // Searching notes and papers belongs to the Student Portal. ?>
                <li class="nav-search">
                    <form action="<?= e(site_link('search.php')) ?>" method="get" class="nav-search-form">
                        <span class="nav-search-icon" aria-hidden="true"><?= svg_icon('search', 'icon-svg icon-svg-small') ?></span>
                        <input type="text" name="q" id="navSearchInput" placeholder="Search resources..."
                               value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search resources">
                        <button type="submit" aria-label="Search"><?= svg_icon('search', 'icon-svg icon-svg-small') ?></button>
                        <?php // Desktop: this square button opens the search box (see .nav-search-toggle). ?>
                        <label for="navSearchInput" class="nav-search-toggle" aria-hidden="true"><?= svg_icon('search', 'icon-svg') ?></label>
                    </form>
                </li>
                <?php endif; ?>
                <li class="nav-site-switch">
                    <a href="<?= e($siteSwitch[1]) ?>" class="site-switch-btn"><?= svg_icon(is_portal() ? 'school' : 'notes', 'icon-svg icon-svg-small') ?> <?= e($siteSwitch[0]) ?></a>
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
