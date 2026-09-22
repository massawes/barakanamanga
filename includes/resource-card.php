<?php
/**
 * Renders one resource as a card/list item.
 * Expects $resource (assoc array from the resources table) and $base (base_url()).
 * Include this file inside a loop: foreach ($resources as $resource) { include ...; }
 */
$isPdf = strtolower(pathinfo($resource['file_name'], PATHINFO_EXTENSION)) === 'pdf';
?>
<div class="resource-item">
    <div class="resource-info">
        <h3><?= e($resource['title']) ?></h3>
        <?php if (!empty($resource['description'])): ?>
            <p class="resource-description"><?= e($resource['description']) ?></p>
        <?php endif; ?>
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
            <span class="file-size"><?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $resource['file_size'])) ?></span>
        </div>
    </div>
    <div class="resource-actions">
        <?php if ($isPdf): ?>
            <a class="btn btn-outline" href="<?= e($base) ?>/download.php?id=<?= (int) $resource['id'] ?>&mode=view" target="_blank" rel="noopener">View</a>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?= e($base) ?>/download.php?id=<?= (int) $resource['id'] ?>">Download</a>
    </div>
</div>
