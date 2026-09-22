<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role(); // Not shown/usable by Staff ("normal") accounts — Admin only.

$typeFilter = $_GET['type'] ?? '';
$sql = 'SELECT * FROM resources';
$params = [];
if (in_array($typeFilter, VALID_TYPES, true)) {
    $sql .= ' WHERE resource_type = :type';
    $params['type'] = $typeFilter;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resources = $stmt->fetchAll();

$pageTitle = 'Manage Resources — Admin Panel';
$activePage = 'manage';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Manage Resources</h1>
<p class="muted">View, edit or delete resources published on the website.</p>

<div class="filter-tabs">
    <a href="<?= e($base) ?>/admin/manage-resources.php" class="<?= $typeFilter === '' ? 'active' : '' ?>">All</a>
    <?php foreach (VALID_TYPES as $type): ?>
        <a href="<?= e($base) ?>/admin/manage-resources.php?type=<?= e($type) ?>"
           class="<?= $typeFilter === $type ? 'active' : '' ?>"><?= e(type_label($type)) ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($resources)): ?>
    <p class="empty-state">No resources found.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Form</th>
                <th>Year</th>
                <th>Size</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resources as $resource): ?>
                <tr>
                    <td data-label="Title"><?= e($resource['title']) ?></td>
                    <td data-label="Type"><?= e(type_label($resource['resource_type'])) ?></td>
                    <td data-label="Form"><?= e(form_label($resource['form_level'])) ?></td>
                    <td data-label="Year"><?= e($resource['year'] ? (string) $resource['year'] : '-') ?></td>
                    <td data-label="Size"><?= e(format_file_size((int) $resource['file_size'])) ?></td>
                    <td data-label="Date Added"><?= e(format_date($resource['created_at'])) ?></td>
                    <td data-label="Actions" class="actions-cell">
                        <a href="<?= e($base) ?>/admin/edit-resource.php?id=<?= (int) $resource['id'] ?>" class="btn btn-outline btn-small">Edit</a>
                        <?php if (is_admin_role()): ?>
                            <form method="post" action="<?= e($base) ?>/admin/delete-resource.php" class="inline-form"
                                  onsubmit="return confirm('Delete this resource? This cannot be undone.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $resource['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
