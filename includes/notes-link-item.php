<?php
/** One uploaded note in a notes.php list. Expects $resource and $base. */
?>
<a class="resource-link-item" href="<?= e($base) ?>/resource-view.php?id=<?= (int) $resource['id'] ?>">
    <span class="resource-link-main">
        <span class="resource-link-title"><?= e($resource['title']) ?></span>
        <span class="resource-link-meta">
            <span class="file-size"><?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $resource['file_size'])) ?></span>
        </span>
    </span>
    <span class="resource-link-arrow">&rsaquo;</span>
</a>
