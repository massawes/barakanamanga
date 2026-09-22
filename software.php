<?php
require_once __DIR__ . '/includes/bootstrap.php';

$totalItems = (int) $pdo->query("SELECT COUNT(*) FROM resources WHERE resource_type = 'software'")->fetchColumn();
$totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
$pageNum = current_page_number($totalPages);
$offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

$stmt = $pdo->prepare(
    "SELECT * FROM resources WHERE resource_type = 'software'
     ORDER BY created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
);
$stmt->execute();
$softwareList = $stmt->fetchAll();

$pageTitle = 'Software — Namanga Digital Resource Centre';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Software</h1>
        <p>Educational and programming software used in ICT and Computer Science practical lessons.</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (empty($softwareList)): ?>
            <p class="empty-state">No software is currently available. Please check again later.</p>
        <?php else: ?>
            <div class="resource-link-list">
                <?php foreach ($softwareList as $resource): ?>
                    <a class="resource-link-item" href="<?= e($base) ?>/resource-view.php?id=<?= (int) $resource['id'] ?>">
                        <span class="resource-link-main">
                            <span class="resource-link-title"><?= e($resource['title']) ?></span>
                            <span class="resource-link-meta">
                                <?php if (!empty($resource['software_version'])): ?><span class="badge badge-outline">v<?= e($resource['software_version']) ?></span><?php endif; ?>
                                <span class="file-size"><?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $resource['file_size'])) ?></span>
                            </span>
                        </span>
                        <span class="resource-link-arrow">&rsaquo;</span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?= render_pagination($pageNum, $totalPages, 'software.php') ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
