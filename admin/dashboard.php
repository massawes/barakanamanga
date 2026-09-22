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
?>

<h1>Dashboard</h1>
<p class="muted">Overview of resources currently published on the website.</p>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-number"><?= $counts['notes'] ?></span>
        <span class="stat-label">Total Notes</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $counts['summary'] ?></span>
        <span class="stat-label">Total Summaries</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $counts['theory'] ?></span>
        <span class="stat-label">Total Theory Papers</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $counts['practical'] ?></span>
        <span class="stat-label">Total Practical Papers</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $counts['software'] ?></span>
        <span class="stat-label">Total Software</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $counts['others'] ?></span>
        <span class="stat-label">Total Others</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $galleryCount ?></span>
        <span class="stat-label">Total Gallery Items</span>
    </div>
</div>

<h2>Quick Actions</h2>
<div class="quick-actions">
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

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
