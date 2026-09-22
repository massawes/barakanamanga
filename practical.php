<?php
require_once __DIR__ . '/includes/bootstrap.php';

$totalItems = (int) $pdo->query("SELECT COUNT(*) FROM resources WHERE resource_type = 'practical'")->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
$pageNum = current_page_number($totalPages);
$offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

$stmt = $pdo->prepare(
    "SELECT * FROM resources WHERE resource_type = 'practical'
     ORDER BY year DESC, created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
);
$stmt->execute();
$practicals = $stmt->fetchAll();

$pageTitle = 'Form 4 Practical — Namanga Digital Resource Centre';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Form 4 Practical</h1>
        <p>Complete Form 4 computer practical examination papers.</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (empty($practicals)): ?>
            <p class="empty-state">No Form 4 practical papers are currently available. Please check again later.</p>
        <?php else: ?>
            <div class="resource-link-list">
                <?php foreach ($practicals as $resource): ?>
                    <a class="resource-link-item" href="<?= e($base) ?>/resource-view.php?id=<?= (int) $resource['id'] ?>">
                        <span class="resource-link-main">
                            <span class="resource-link-title"><?= e($resource['title']) ?></span>
                            <span class="resource-link-meta">
                                <?php if (!empty($resource['year'])): ?><span class="badge badge-outline"><?= e((string) $resource['year']) ?></span><?php endif; ?>
                                <span class="file-size"><?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $resource['file_size'])) ?></span>
                            </span>
                        </span>
                        <span class="resource-link-arrow">&rsaquo;</span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?= render_pagination($pageNum, $totalPages, 'practical.php') ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
