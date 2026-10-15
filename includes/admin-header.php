<?php
// Expects $pageTitle and $activePage (a short key like 'dashboard') set by the including page.
// Layout (AdminLTE / NECTA PREMS style): dark sidebar on the left with
// grouped links, a green top bar with the menu toggle and the signed-in
// user, then the page title with a breadcrumb.
$pageTitle = $pageTitle ?? 'Admin Panel — Namanga Digital Resource Centre';
$activePage = $activePage ?? '';
$base = base_url();
$loggedIn = is_admin_logged_in();

// Sidebar: key => [label, icon, page] for a single link, or
// key => [label, icon, [activePage => [label, page], ...]] for a group.
$adminMenu = ['dashboard' => ['Dashboard', 'dashboard', 'dashboard.php']];
$adminMenu['upload'] = ['Upload', 'upload', [
    'upload-notes'     => ['Notes', 'upload-notes.php'],
    'upload-summary'   => ['Summary', 'upload-summary.php'],
    'upload-theory'    => ['Theory Paper', 'upload-theory.php'],
    'upload-practical' => ['Practical', 'upload-practical.php'],
    'upload-software'  => ['Software', 'upload-software.php'],
    'upload-others'    => ['Others', 'upload-others.php'],
    'upload-gallery'   => ['Gallery', 'upload-gallery.php'],
]];
$manageLinks = [];
if (is_admin_role()) {
    $manageLinks['manage'] = ['Resources', 'manage-resources.php'];
}
$manageLinks['manage-gallery'] = ['Gallery', 'manage-gallery.php'];
$manageLinks['manage-slides'] = ['Homepage Slideshow', 'manage-slides.php'];
$adminMenu['manage'] = ['Manage', 'manage', $manageLinks];
if (is_super_admin()) {
    $adminMenu['manage-users'] = ['Staff Accounts', 'users', 'manage-users.php'];
}

// Breadcrumb: Home / [group /] current page
$crumbs = [];
foreach ($adminMenu as $key => [$label, , $target]) {
    if (is_array($target) && isset($target[$activePage])) {
        $crumbs = [$label, $target[$activePage][0]];
    } elseif ($key === $activePage) {
        $crumbs = [$label];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="192x192" href="<?= e($base) ?>/assets/images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap">
<link rel="stylesheet" href="<?= e($base) ?>/assets/css/style.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-body<?= $loggedIn ? '' : ' adm-guest' ?>">
<?php if ($loggedIn): ?>
<aside class="adm-sidebar" id="admSidebar">
    <a class="adm-brand" href="<?= e($base) ?>/admin/dashboard.php">
        <img src="<?= e($base) ?>/assets/images/logo-white.png" alt="" class="adm-brand-logo">
        <span>NAMANGA ADMIN</span>
    </a>
    <nav class="adm-menu">
        <?php foreach ($adminMenu as $key => [$label, $icon, $target]): ?>
            <?php if (is_array($target)): ?>
                <?php $groupOpen = isset($target[$activePage]); ?>
                <details class="adm-group"<?= $groupOpen ? ' open' : '' ?>>
                    <summary class="adm-link<?= $groupOpen ? ' adm-link-open' : '' ?>">
                        <?= svg_icon($icon, 'icon-svg adm-icon') ?>
                        <span><?= e($label) ?></span>
                        <?= svg_icon('chevron-left', 'icon-svg adm-caret') ?>
                    </summary>
                    <?php foreach ($target as $childKey => [$childLabel, $childPage]): ?>
                        <a href="<?= e($base) ?>/admin/<?= e($childPage) ?>"
                           class="adm-sublink<?= $childKey === $activePage ? ' active' : '' ?>"><?= e($childLabel) ?></a>
                    <?php endforeach; ?>
                </details>
            <?php else: ?>
                <a href="<?= e($base) ?>/admin/<?= e($target) ?>" class="adm-link<?= $key === $activePage ? ' active' : '' ?>">
                    <?= svg_icon($icon, 'icon-svg adm-icon') ?>
                    <span><?= e($label) ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        <a href="<?= e($base) ?>/index.php" class="adm-link" target="_blank" rel="noopener">
            <?= svg_icon('home', 'icon-svg adm-icon') ?>
            <span>View Website</span>
        </a>
        <a href="<?= e($base) ?>/admin/logout.php" class="adm-link">
            <?= svg_icon('logout', 'icon-svg adm-icon') ?>
            <span>Logout</span>
        </a>
    </nav>
</aside>
<div class="adm-overlay" id="admOverlay"></div>
<?php endif; ?>
<div class="adm-main">
    <header class="adm-topbar">
        <?php if ($loggedIn): ?>
            <button type="button" class="adm-toggle" id="admToggle" aria-label="Show or hide the menu"><?= svg_icon('menu', 'icon-svg') ?></button>
            <span class="adm-topbar-school">NAMANGA SECONDARY SCHOOL &middot; S2911</span>
            <details class="adm-user">
                <summary>
                    <span class="adm-avatar"><?= e(strtoupper(substr($_SESSION['admin_username'] ?? '?', 0, 1))) ?></span>
                    <span class="adm-user-name"><?= e($_SESSION['admin_username'] ?? '') ?></span>
                    <?= svg_icon('chevron-down', 'icon-svg adm-user-caret') ?>
                </summary>
                <div class="adm-user-menu">
                    <p class="adm-user-role"><?= is_super_admin() ? 'Super Admin' : (is_admin_role() ? 'Admin' : 'Staff') ?></p>
                    <a href="<?= e($base) ?>/index.php" target="_blank" rel="noopener">View Website</a>
                    <a href="<?= e($base) ?>/admin/logout.php">Logout</a>
                </div>
            </details>
        <?php else: ?>
            <a class="adm-topbar-brand" href="<?= e($base) ?>/index.php">NAMANGA ADMIN</a>
        <?php endif; ?>
    </header>
    <main class="admin-content">
        <?php if ($loggedIn && $crumbs !== []): ?>
            <nav class="adm-breadcrumb" aria-label="Breadcrumb">
                <a href="<?= e($base) ?>/admin/dashboard.php">Home</a>
                <?php foreach ($crumbs as $crumb): ?>
                    <span>/</span> <?= e($crumb) ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        <?php if ($msg = flash_get('success')): ?>
            <div class="alert alert-success"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash_get('error')): ?>
            <div class="alert alert-error"><?= e($msg) ?></div>
        <?php endif; ?>
