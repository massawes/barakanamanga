<?php
// Expects $pageTitle and $activePage (a short key like 'dashboard') set by the including page.
$pageTitle = $pageTitle ?? 'Admin Panel — Namanga Digital Resource Centre';
$base = base_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= e($base) ?>/assets/css/style.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="admin-header-inner">
        <a class="brand brand-admin" href="<?= e($base) ?>/admin/dashboard.php">
            <span class="brand-name">NAMANGA ADMIN</span>
        </a>
        <?php if (is_admin_logged_in()): ?>
            <div class="admin-header-actions">
                <span class="admin-username">Signed in as <?= e($_SESSION['admin_username'] ?? '') ?> (<?= is_super_admin() ? 'Super Admin' : (is_admin_role() ? 'Admin' : 'Staff') ?>)</span>
                <a href="<?= e($base) ?>/index.php" class="btn btn-outline btn-small">View Site</a>
                <a href="<?= e($base) ?>/admin/logout.php" class="btn btn-danger btn-small">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</header>
<div class="admin-layout">
    <?php if (is_admin_logged_in()): ?>
    <nav class="admin-sidebar">
        <ul>
            <li><a href="<?= e($base) ?>/admin/dashboard.php" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-notes.php" class="<?= ($activePage ?? '') === 'upload-notes' ? 'active' : '' ?>">Upload Notes</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-summary.php" class="<?= ($activePage ?? '') === 'upload-summary' ? 'active' : '' ?>">Upload Summary</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-theory.php" class="<?= ($activePage ?? '') === 'upload-theory' ? 'active' : '' ?>">Upload Theory Paper</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-practical.php" class="<?= ($activePage ?? '') === 'upload-practical' ? 'active' : '' ?>">Upload Practical</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-software.php" class="<?= ($activePage ?? '') === 'upload-software' ? 'active' : '' ?>">Upload Software</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-others.php" class="<?= ($activePage ?? '') === 'upload-others' ? 'active' : '' ?>">Upload Others</a></li>
            <li><a href="<?= e($base) ?>/admin/upload-gallery.php" class="<?= ($activePage ?? '') === 'upload-gallery' ? 'active' : '' ?>">Upload Gallery</a></li>
            <?php if (is_admin_role()): ?>
            <li><a href="<?= e($base) ?>/admin/manage-resources.php" class="<?= ($activePage ?? '') === 'manage' ? 'active' : '' ?>">Manage Resources</a></li>
            <?php endif; ?>
            <li><a href="<?= e($base) ?>/admin/manage-gallery.php" class="<?= ($activePage ?? '') === 'manage-gallery' ? 'active' : '' ?>">Manage Gallery</a></li>
            <li><a href="<?= e($base) ?>/admin/manage-slides.php" class="<?= ($activePage ?? '') === 'manage-slides' ? 'active' : '' ?>">Homepage Slideshow</a></li>
            <?php if (is_super_admin()): ?>
            <li><a href="<?= e($base) ?>/admin/manage-users.php" class="<?= ($activePage ?? '') === 'manage-users' ? 'active' : '' ?>">Manage Staff Accounts</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <main class="admin-content">
        <?php if ($msg = flash_get('success')): ?>
            <div class="alert alert-success"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash_get('error')): ?>
            <div class="alert alert-error"><?= e($msg) ?></div>
        <?php endif; ?>
