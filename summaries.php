<?php
require_once __DIR__ . '/includes/bootstrap.php';

$selectedForm = $_GET['form'] ?? '';
$formSelected = in_array($selectedForm, VALID_FORMS, true);

$base = base_url();

if ($formSelected) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM resources WHERE resource_type = 'summary' AND form_level = :form");
    $countStmt->execute(['form' => $selectedForm]);
    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
    $pageNum = current_page_number($totalPages);
    $offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

    $stmt = $pdo->prepare(
        "SELECT * FROM resources WHERE resource_type = 'summary' AND form_level = :form
         ORDER BY created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
    );
    $stmt->execute(['form' => $selectedForm]);
    $items = $stmt->fetchAll();
    $pageTitle = form_label($selectedForm) . ' Summary | Namanga Secondary School';
} else {
    // Counts per Form, shown on the selection menu.
    $counts = ['form1' => 0, 'form2' => 0, 'form3' => 0, 'form4' => 0];
    $stmt = $pdo->query("SELECT form_level, COUNT(*) AS total FROM resources WHERE resource_type = 'summary' GROUP BY form_level");
    foreach ($stmt->fetchAll() as $row) {
        if (isset($counts[$row['form_level']])) {
            $counts[$row['form_level']] = (int) $row['total'];
        }
    }
    $pageTitle = 'Summary | Namanga Secondary School';
    $pageDescription = 'Short revision summaries for Form 1 to Form 4 from Namanga Secondary School (S.2911), Longido, Arusha, Tanzania. Free to view and download.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Summary</h1>
        <p><?= $formSelected ? e(form_label($selectedForm) . ' — quick revision summaries.') : 'Choose a Form to see its summaries.' ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if (!$formSelected): ?>

            <div class="photo-cards-grid">
                <?php foreach (VALID_FORMS as $form): ?>
                    <?= render_photo_card($base . '/summaries.php?form=' . $form, 'summary-' . $form, form_label($form),
                        'Quick revision summaries for ' . form_label($form) . '.', count_label($counts[$form], 'file'), 'View ' . form_label($form)) ?>
                <?php endforeach; ?>
            </div>
            <?= render_card_photo_credits(array_map(fn ($f) => 'summary-' . $f, VALID_FORMS)) ?>

        <?php else: ?>

            <p><a href="<?= e($base) ?>/summaries.php" class="back-link">&larr; Back to Form selection</a></p>
            <div class="section-title-row">
                <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> Summary</h2>
                <?php if (!empty($items)): ?>
                    <a href="<?= e($base) ?>/download-all.php?category=summary&amp;form=<?= e($selectedForm) ?>" class="btn btn-outline btn-small">Download All</a>
                <?php endif; ?>
            </div>

            <?php if (empty($items)): ?>
                <p class="empty-state">No <?= e(form_label($selectedForm)) ?> summary is currently available. Please check again later.</p>
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
                <?= render_pagination($pageNum, $totalPages, 'summaries.php?form=' . $selectedForm) ?>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
