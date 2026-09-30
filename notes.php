<?php
require_once __DIR__ . '/includes/bootstrap.php';

$selectedForm = $_GET['form'] ?? '';
$formSelected = in_array($selectedForm, VALID_FORMS, true);

$base = base_url();

if ($formSelected) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM resources WHERE resource_type = 'notes' AND form_level = :form");
    $countStmt->execute(['form' => $selectedForm]);
    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
    $pageNum = current_page_number($totalPages);
    $offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

    $stmt = $pdo->prepare(
        "SELECT * FROM resources WHERE resource_type = 'notes' AND form_level = :form
         ORDER BY created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
    );
    $stmt->execute(['form' => $selectedForm]);
    $items = $stmt->fetchAll();
    $pageTitle = form_label($selectedForm) . ' Notes — Namanga Digital Resource Centre';
} else {
    // Counts per Form, shown on the selection menu.
    $counts = ['form1' => 0, 'form2' => 0, 'form3' => 0, 'form4' => 0];
    $stmt = $pdo->query("SELECT form_level, COUNT(*) AS total FROM resources WHERE resource_type = 'notes' GROUP BY form_level");
    foreach ($stmt->fetchAll() as $row) {
        if (isset($counts[$row['form_level']])) {
            $counts[$row['form_level']] = (int) $row['total'];
        }
    }
    $pageTitle = 'Notes — Namanga Digital Resource Centre';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Notes</h1>
        <p><?= $formSelected ? e(form_label($selectedForm) . ' — complete ICT & Computer Science notes.') : 'Choose a Form to see its notes.' ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if (!$formSelected): ?>

            <div class="cards-grid">
                <?php foreach (VALID_FORMS as $form): ?>
                    <a href="<?= e($base) ?>/notes.php?form=<?= e($form) ?>" class="category-card form-select-card">
                        <div class="category-icon"><?= svg_icon('notes') ?></div>
                        <h3 class="form-big-label"><?= e(strtoupper(form_label($form))) ?></h3>
                        <p>Notes for <?= e(form_label($form)) ?>.</p>
                        <span class="card-count"><?= $counts[$form] ?> file<?= $counts[$form] === 1 ? '' : 's' ?></span>
                        <span class="btn btn-outline">View <?= e(form_label($form)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <p><a href="<?= e($base) ?>/notes.php" class="back-link">&larr; Back to Form selection</a></p>
            <div class="section-title-row">
                <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> Notes</h2>
                <?php if (!empty($items)): ?>
                    <a href="<?= e($base) ?>/download-all.php?category=notes&amp;form=<?= e($selectedForm) ?>" class="btn btn-outline btn-small">Download All</a>
                <?php endif; ?>
            </div>

            <?php if (empty($items)): ?>
                <p class="empty-state">No <?= e(form_label($selectedForm)) ?> notes are currently available. Please check again later.</p>
            <?php else: ?>
                <div class="resource-link-list">
                    <?php foreach ($items as $resource): ?>
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
                <?= render_pagination($pageNum, $totalPages, 'notes.php?form=' . $selectedForm) ?>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
