<?php
require_once __DIR__ . '/includes/bootstrap.php';

$selectedForm = $_GET['form'] ?? '';
$formSelected = in_array($selectedForm, VALID_FORMS, true);

$base = base_url();

if ($formSelected) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE form_level = :form");
    $countStmt->execute(['form' => $selectedForm]);
    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
    $pageNum = current_page_number($totalPages);
    $offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

    // Newest submissions first.
    $stmt = $pdo->prepare(
        "SELECT * FROM tasks WHERE form_level = :form
         ORDER BY created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
    );
    $stmt->execute(['form' => $selectedForm]);
    $items = $stmt->fetchAll();
    $pageTitle = form_label($selectedForm) . ' Tasks | Namanga Secondary School';
} else {
    $counts = ['form1' => 0, 'form2' => 0, 'form3' => 0, 'form4' => 0];
    $stmt = $pdo->query("SELECT form_level, COUNT(*) AS total FROM tasks GROUP BY form_level");
    foreach ($stmt->fetchAll() as $row) {
        if (isset($counts[$row['form_level']])) {
            $counts[$row['form_level']] = (int) $row['total'];
        }
    }
    $pageTitle = 'Student Tasks | Namanga Secondary School';
    $pageDescription = 'Assignments and tasks submitted by students of Namanga Secondary School (S.2911), Tanzania, for Form 1 to Form 4.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Tasks</h1>
        <p><?= $formSelected ? e(form_label($selectedForm) . ' — submitted tasks, newest first.') : 'Choose a Form to see submitted tasks.' ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if (!$formSelected): ?>

            <p style="margin-bottom: 20px;">
                <a href="<?= e($base) ?>/upload-task.php" class="btn btn-primary">Upload Task</a>
            </p>

            <div class="photo-cards-grid">
                <?php foreach (VALID_FORMS as $form): ?>
                    <?= render_photo_card($base . '/tasks.php?form=' . $form, 'tasks-' . $form, form_label($form),
                        'Submitted tasks for ' . form_label($form) . '.', count_label($counts[$form], 'task'), 'View ' . form_label($form)) ?>
                <?php endforeach; ?>
            </div>
            <?= render_card_photo_credits(array_map(fn ($f) => 'tasks-' . $f, VALID_FORMS)) ?>

        <?php else: ?>

            <p><a href="<?= e($base) ?>/tasks.php" class="back-link">&larr; Back to Form selection</a></p>
            <div class="section-title-row">
                <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> Tasks</h2>
                <a href="<?= e($base) ?>/upload-task.php" class="btn btn-outline btn-small">Upload Task</a>
            </div>

            <?php if (empty($items)): ?>
                <p class="empty-state">No <?= e(form_label($selectedForm)) ?> tasks have been submitted yet.</p>
            <?php else: ?>
                <div class="resource-link-list">
                    <?php foreach ($items as $task): ?>
                        <a class="resource-link-item" href="<?= e($base) ?>/task-view.php?id=<?= (int) $task['id'] ?>">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($task['title']) ?></span>
                                <span class="resource-link-meta">
                                    <span class="badge badge-outline"><?= e($task['uploader_name']) ?></span>
                                    <span class="file-size"><?= e(strtoupper(pathinfo($task['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $task['file_size'])) ?> &middot; <?= e(format_date($task['created_at'])) ?></span>
                                </span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?= render_pagination($pageNum, $totalPages, 'tasks.php?form=' . $selectedForm) ?>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
