<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

$counts = ['notes' => 0, 'summary' => 0, 'theory' => 0, 'practical' => 0, 'software' => 0, 'others' => 0];
$stmt = $pdo->query('SELECT resource_type, COUNT(*) AS total FROM resources GROUP BY resource_type');
foreach ($stmt->fetchAll() as $row) {
    $counts[$row['resource_type']] = (int) $row['total'];
}

$galleryCount = 0;
try {
    $galleryCount = (int) $pdo->query('SELECT COUNT(*) FROM gallery_items')->fetchColumn();
} catch (Exception $e) {
    // gallery_items table not created yet — fail quietly.
}

$pageTitle = 'Dashboard — Admin Panel';
$activePage = 'dashboard';
include __DIR__ . '/../includes/admin-header.php';
$base = base_url();

// Coloured stat boxes: [count, label, icon, colour class, page the "More info" link opens]
$statBoxes = [
    [$counts['notes'], 'Notes', 'notes', 'adm-box-sky', 'upload-notes.php'],
    [$counts['summary'], 'Summaries', 'summary', 'adm-box-green', 'upload-summary.php'],
    [$counts['theory'], 'Theory Papers', 'theory', 'adm-box-gold', 'upload-theory.php'],
    [$counts['practical'], 'Practical Papers', 'practical', 'adm-box-red', 'upload-practical.php'],
    [$counts['software'], 'Software', 'software', 'adm-box-navy', 'upload-software.php'],
    [$counts['others'], 'Other Subjects', 'others', 'adm-box-pink', 'upload-others.php'],
    [$galleryCount, 'Gallery Items', 'gallery', 'adm-box-teal', 'manage-gallery.php'],
];
?>

<h1>Dashboard</h1>
<p class="muted">Overview of resources currently published on the website.</p>

<div class="adm-boxes">
    <?php foreach ($statBoxes as [$count, $label, $icon, $colour, $page]): ?>
        <div class="adm-box <?= e($colour) ?>">
            <div class="adm-box-inner">
                <span class="adm-box-number"><?= (int) $count ?></span>
                <span class="adm-box-label"><?= e($label) ?></span>
            </div>
            <?= svg_icon($icon, 'icon-svg adm-box-icon') ?>
            <a href="<?= e($base) ?>/admin/<?= e($page) ?>" class="adm-box-more">More info &rsaquo;</a>
        </div>
    <?php endforeach; ?>
</div>

<div class="adm-card">
    <div class="adm-card-head">Quick Actions</div>
    <div class="adm-card-body quick-actions">
        <a href="<?= e($base) ?>/admin/upload-notes.php" class="btn btn-primary">+ Add Notes</a>
        <a href="<?= e($base) ?>/admin/upload-summary.php" class="btn btn-primary">+ Add Summary</a>
        <a href="<?= e($base) ?>/admin/upload-theory.php" class="btn btn-primary">+ Add Theory Paper</a>
        <a href="<?= e($base) ?>/admin/upload-practical.php" class="btn btn-primary">+ Add Practical</a>
        <a href="<?= e($base) ?>/admin/upload-software.php" class="btn btn-primary">+ Add Software</a>
        <a href="<?= e($base) ?>/admin/upload-others.php" class="btn btn-primary">+ Add Others</a>
        <a href="<?= e($base) ?>/admin/upload-gallery.php" class="btn btn-primary">+ Add Gallery</a>
        <?php if (is_admin_role()): ?>
        <a href="<?= e($base) ?>/admin/manage-resources.php" class="btn btn-outline">Manage Resources</a>
        <?php endif; ?>
        <a href="<?= e($base) ?>/admin/manage-gallery.php" class="btn btn-outline">Manage Gallery</a>
        <a href="<?= e($base) ?>/admin/manage-slides.php" class="btn btn-outline">Homepage Slideshow</a>
        <?php if (is_super_admin()): ?>
        <a href="<?= e($base) ?>/admin/manage-users.php" class="btn btn-outline">Manage Staff Accounts</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
