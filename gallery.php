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

$headings = [
    'image' => ['Photos', 'Photos from life at Namanga Secondary School.'],
    'video' => ['Videos', 'Videos from life at Namanga Secondary School.'],
    ''      => ['The Latest', 'The latest photos and videos from Namanga Secondary School.'],
];
[$heading, $intro] = $headings[in_array($typeFilter, ['image', 'video'], true) ? $typeFilter : ''];
$pageTitle = $heading . ' | The Latest | Namanga Secondary School';
$pageDescription = $intro;
$base = base_url();
include __DIR__ . '/includes/header.php';

$paginationBaseUrl = 'gallery.php' . ($typeFilter !== '' ? '?type=' . $typeFilter : '');
?>

<section class="page-header">
    <div class="container">
        <h1><?= e($heading) ?></h1>
        <p><?= e($intro) ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="filter-tabs-row">
            <div class="filter-tabs">
                <a href="<?= e($base) ?>/gallery.php?type=image" class="<?= $typeFilter === 'image' ? 'active' : '' ?>">Photos</a>
                <a href="<?= e($base) ?>/gallery.php?type=video" class="<?= $typeFilter === 'video' ? 'active' : '' ?>">Videos</a>
                <a href="<?= e($base) ?>/gallery.php" class="<?= $typeFilter === '' ? 'active' : '' ?>">All Media</a>
            </div>
            <?php if (!empty($items)): ?>
                <a href="<?= e($base) ?>/download-all.php?category=gallery<?= $typeFilter !== '' ? '&amp;media=' . e($typeFilter) : '' ?>" class="btn btn-outline btn-small">Download All</a>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
            <p class="empty-state">Nothing here yet. Please check back soon.</p>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($items as $item): ?>
                    <div class="gallery-card">
                        <?php if ($item['media_type'] === 'image'): ?>
                            <a href="<?= e($base) ?>/<?= e(optimized_image($item['file_path'], 2400)) ?>" target="_blank" rel="noopener" class="gallery-media gallery-photo-link" data-title="<?= e($item['title']) ?>">
                                <?php // The browser picks the 1200px copy on sharp (high-resolution) screens. ?>
                                <img src="<?= e($base) ?>/<?= e(optimized_image($item['file_path'], 600)) ?>"
                                     srcset="<?= e($base) ?>/<?= e(optimized_image($item['file_path'], 600)) ?> 600w, <?= e($base) ?>/<?= e(optimized_image($item['file_path'], 1200)) ?> 1200w"
                                     sizes="(max-width: 600px) 100vw, 360px"
                                     alt="<?= e($item['title']) ?>" loading="lazy" decoding="async">
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

            <div id="lightbox" class="lightbox" hidden>
                <button type="button" class="lightbox-btn lightbox-close" aria-label="Close"><?= svg_icon('close', 'icon-svg') ?></button>
                <button type="button" class="lightbox-btn lightbox-prev" aria-label="Previous photo">&lsaquo;</button>
                <img src="" alt="">
                <button type="button" class="lightbox-btn lightbox-next" aria-label="Next photo">&rsaquo;</button>
                <p class="lightbox-caption"></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
