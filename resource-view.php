<?php
require_once __DIR__ . '/includes/bootstrap.php';

$base = base_url();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$resource = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM resources WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $resource = $stmt->fetch();
}

// Work out where the "Back" link should go, based on the resource's category.
$typeToPage = [
    'notes'     => 'notes.php',
    'summary'   => 'summaries.php',
    'theory'    => 'theory.php',
    'practical' => 'practical.php',
    'software'  => 'software.php',
    'others'    => 'others.php',
];

// Notes, Summary and Theory all use the Form-selection drill-down, so the
// back link should return to the same Form the resource belongs to.
$formDrillDownTypes = ['notes', 'summary', 'theory'];

$backHref = $base . '/index.php';
if ($resource) {
    $backHref = $base . '/' . ($typeToPage[$resource['resource_type']] ?? 'index.php');
    if (in_array($resource['resource_type'], $formDrillDownTypes, true) && !empty($resource['form_level'])) {
        $backHref .= '?form=' . urlencode($resource['form_level']);
    }
}

$pageTitle = $resource ? $resource['title'] . ' | Namanga Secondary School' : 'Resource Not Found';
$noindex = !$resource;
include __DIR__ . '/includes/header.php';
?>

<section class="page-content">
    <div class="container narrow">
        <p><a href="<?= e($backHref) ?>" class="back-link">&larr; Back</a></p>

        <?php if (!$resource): ?>

            <div class="error-box">
                <h2>Resource Not Found</h2>
                <p>This resource may have been removed or the link is incorrect.</p>
                <a href="<?= e($base) ?>/index.php" class="btn btn-primary">Back to Home</a>
            </div>

        <?php else: ?>

            <?php $isPdf = strtolower(pathinfo($resource['file_name'], PATHINFO_EXTENSION)) === 'pdf'; ?>

            <div class="resource-detail-card">
                <div class="resource-meta">
                    <span class="badge"><?= e(type_label($resource['resource_type'])) ?></span>
                    <?php if (!empty($resource['form_level'])): ?>
                        <span class="badge badge-outline"><?= e(form_label($resource['form_level'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($resource['year'])): ?>
                        <span class="badge badge-outline"><?= e((string) $resource['year']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($resource['software_version'])): ?>
                        <span class="badge badge-outline">v<?= e($resource['software_version']) ?></span>
                    <?php endif; ?>
                </div>

                <h1><?= e($resource['title']) ?></h1>

                <?php if (!empty($resource['description'])): ?>
                    <p class="resource-description"><?= e($resource['description']) ?></p>
                <?php endif; ?>

                <p class="file-size">
                    <?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot;
                    <?= e(format_file_size((int) $resource['file_size'])) ?> &middot;
                    Added <?= e(format_date($resource['created_at'])) ?>
                </p>

                <div class="resource-detail-actions">
                    <?php if ($isPdf): ?>
                        <a class="btn btn-outline btn-large" href="<?= e($base) ?>/download.php?id=<?= (int) $resource['id'] ?>&mode=view" target="_blank" rel="noopener">View</a>
                    <?php endif; ?>
                    <a class="btn btn-primary btn-large" href="<?= e($base) ?>/download.php?id=<?= (int) $resource['id'] ?>">Download</a>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
