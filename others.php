<?php
require_once __DIR__ . '/includes/bootstrap.php';

$totalItems = (int) $pdo->query("SELECT COUNT(*) FROM resources WHERE resource_type = 'others'")->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
$pageNum = current_page_number($totalPages);
$offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

$stmt = $pdo->prepare(
    "SELECT * FROM resources WHERE resource_type = 'others'
     ORDER BY created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
);
$stmt->execute();
$othersList = $stmt->fetchAll();

$pageTitle = 'Other Subjects | Namanga Secondary School';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Other Subjects</h1>
        <p>Resources for other subjects such as Geography, History, Kiswahili and more.</p>
        <?php if (!empty($othersList)): ?>
            <a href="<?= e($base) ?>/download-all.php?category=others" class="btn btn-gold download-all-btn">Download All</a>
        <?php endif; ?>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (empty($othersList)): ?>
            <p class="empty-state">No additional resources are currently available. Please check again later.</p>
        <?php else: ?>
            <div class="resource-link-list">
                <?php foreach ($othersList as $resource): ?>
                    <a class="resource-link-item" href="<?= e($base) ?>/resource-view.php?id=<?= (int) $resource['id'] ?>">
                        <span class="resource-link-main">
                            <span class="resource-link-title"><?= e($resource['title']) ?></span>
                            <span class="resource-link-meta">
                                <span class="file-size"><?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $resource['file_size'])) ?></span>
                            </span>
                        </span>
                        <span class="resource-link-arrow">&rsaquo;</span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?= render_pagination($pageNum, $totalPages, 'others.php') ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
