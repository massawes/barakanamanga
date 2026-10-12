<?php
require_once __DIR__ . '/includes/bootstrap.php';

$base = base_url();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$task = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $task = $stmt->fetch();
}

$backHref = $base . '/tasks.php';
if ($task && !empty($task['form_level'])) {
    $backHref .= '?form=' . urlencode($task['form_level']);
}

$pageTitle = $task ? e($task['title']) . ' — Namanga Digital Resource Centre' : 'Task Not Found';
include __DIR__ . '/includes/header.php';
?>

<section class="page-content">
    <div class="container narrow">
        <p><a href="<?= e($backHref) ?>" class="back-link">&larr; Back</a></p>

        <?php if (!$task): ?>

            <div class="error-box">
                <h2>Task Not Found</h2>
                <p>This task may have been removed or the link is incorrect.</p>
                <a href="<?= e($base) ?>/tasks.php" class="btn btn-primary">Back to Tasks</a>
            </div>

        <?php else: ?>

            <?php $extension = strtolower(pathinfo($task['file_name'], PATHINFO_EXTENSION)); ?>
            <?php $isPdf = $extension === 'pdf'; ?>

            <div class="resource-detail-card">
                <div class="resource-meta">
                    <span class="badge">Task</span>
                    <span class="badge badge-outline"><?= e(form_label($task['form_level'])) ?></span>
                    <span class="badge badge-outline"><?= e($task['uploader_name']) ?></span>
                </div>

                <h1><?= e($task['title']) ?></h1>

                <p class="file-size">
                    <?= e(strtoupper($extension)) ?> &middot;
                    <?= e(format_file_size((int) $task['file_size'])) ?> &middot;
                    Submitted <?= e(format_date($task['created_at'])) ?>
                </p>

                <div class="resource-detail-actions">
                    <?php if ($isPdf): ?>
                        <a class="btn btn-outline btn-large" href="<?= e($base) ?>/task-download.php?id=<?= (int) $task['id'] ?>&mode=view" target="_blank" rel="noopener" id="taskViewLink">View</a>
                        <button type="button" class="btn btn-outline btn-large" id="taskPrintBtn">Print</button>
                    <?php endif; ?>
                    <a class="btn btn-primary btn-large" href="<?= e($base) ?>/task-download.php?id=<?= (int) $task['id'] ?>">Download</a>
                </div>

                <?php if (!$isPdf): ?>
                    <p class="field-hint" style="margin-top: 16px;">
                        This file is a <?= e(strtoupper($extension)) ?> document, so it can't be previewed or printed
                        directly in the browser. Click <strong>Download</strong>, then open it in its normal
                        application (e.g. Word, Excel, PowerPoint) to view or print it.
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($isPdf): ?>
                <div class="pdf-preview-frame" style="margin-top: 20px;">
                    <iframe src="<?= e($base) ?>/task-download.php?id=<?= (int) $task['id'] ?>&mode=view"
                            style="width: 100%; height: 70vh; border: 1px solid #ddd; border-radius: 6px;"
                            title="<?= e($task['title']) ?> preview"></iframe>
                </div>
                <script>
                    document.getElementById('taskPrintBtn').addEventListener('click', function () {
                        var win = window.open('<?= e($base) ?>/task-download.php?id=<?= (int) $task['id'] ?>&mode=view', '_blank');
                        if (win) {
                            win.addEventListener('load', function () {
                                win.focus();
                                win.print();
                            });
                        }
                    });
                </script>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
