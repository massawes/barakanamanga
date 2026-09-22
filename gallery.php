<?php
require_once __DIR__ . '/includes/bootstrap.php';

$typeFilter = $_GET['type'] ?? '';
$where = '';
$params = [];
if (in_array($typeFilter, ['image', 'video'], true)) {
    $where = ' WHERE media_type = :type';
    $params['type'] = $typeFilter;
}

$totalItems = 0;
try {
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM gallery_items' . $where);
    $countStmt->execute($params);
    $totalItems = (int) $countStmt->fetchColumn();
} catch (Exception $e) {
    // gallery_items table not created yet — fail quietly, empty state shows.
}

$totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
$pageNum = current_page_number($totalPages);
$offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

$items = [];
if ($totalItems > 0) {
    $stmt = $pdo->prepare(
        'SELECT * FROM gallery_items' . $where . ' ORDER BY created_at DESC LIMIT ' . RESOURCES_PER_PAGE . ' OFFSET ' . $offset
    );
    $stmt->execute($params);
    $items = $stmt->fetchAll();
}

$pageTitle = 'Gallery — Namanga Digital Resource Centre';
$base = base_url();
include __DIR__ . '/includes/header.php';

$paginationBaseUrl = 'gallery.php' . ($typeFilter !== '' ? '?type=' . $typeFilter : '');
?>

<section class="page-header">
    <div class="container">
        <h1>Gallery</h1>
        <p>Photos and videos from Namanga Secondary School's ICT &amp; Computer Science department.</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="filter-tabs">
            <a href="<?= e($base) ?>/gallery.php" class="<?= $typeFilter === '' ? 'active' : '' ?>">All</a>
            <a href="<?= e($base) ?>/gallery.php?type=image" class="<?= $typeFilter === 'image' ? 'active' : '' ?>">Photos</a>
            <a href="<?= e($base) ?>/gallery.php?type=video" class="<?= $typeFilter === 'video' ? 'active' : '' ?>">Videos</a>
        </div>

        <?php if (empty($items)): ?>
            <p class="empty-state">No gallery items yet. Please check back soon.</p>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($items as $item): ?>
                    <div class="gallery-card">
                        <?php if ($item['media_type'] === 'image'): ?>
                            <a href="<?= e($base) ?>/<?= e($item['file_path']) ?>" target="_blank" rel="noopener" class="gallery-media">
                                <img src="<?= e($base) ?>/<?= e($item['file_path']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                            </a>
                        <?php else: ?>
                            <div class="gallery-media">
                                <video src="<?= e($base) ?>/<?= e($item['file_path']) ?>" controls preload="metadata"></video>
                            </div>
                        <?php endif; ?>
                        <p class="gallery-caption"><?= e($item['title']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <?= render_pagination($pageNum, $totalPages, $paginationBaseUrl) ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
