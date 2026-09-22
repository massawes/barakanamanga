<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!is_admin_role()) {
        flash_set('error', 'Only an Admin account can delete a gallery item.');
        redirect(base_url() . '/admin/manage-gallery.php');
    }
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM gallery_items WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch();
            if ($item) {
                $pdo->prepare('DELETE FROM gallery_items WHERE id = :id')->execute(['id' => $id]);
                delete_gallery_file($item['file_path']);
                flash_set('success', 'Gallery item deleted successfully.');
            }
        }
    }
    redirect(base_url() . '/admin/manage-gallery.php');
}

$typeFilter = $_GET['type'] ?? '';
$sql = 'SELECT * FROM gallery_items';
$params = [];
if (in_array($typeFilter, ['image', 'video'], true)) {
    $sql .= ' WHERE media_type = :type';
    $params['type'] = $typeFilter;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$pageTitle = 'Manage Gallery — Admin Panel';
$activePage = 'manage-gallery';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Manage Gallery</h1>
<p class="muted">View or delete photos and videos published in the public Gallery.</p>

<div class="filter-tabs">
    <a href="<?= e($base) ?>/admin/manage-gallery.php" class="<?= $typeFilter === '' ? 'active' : '' ?>">All</a>
    <a href="<?= e($base) ?>/admin/manage-gallery.php?type=image" class="<?= $typeFilter === 'image' ? 'active' : '' ?>">Photos</a>
    <a href="<?= e($base) ?>/admin/manage-gallery.php?type=video" class="<?= $typeFilter === 'video' ? 'active' : '' ?>">Videos</a>
</div>

<?php if (empty($items)): ?>
    <p class="empty-state">No gallery items found.</p>
<?php else: ?>
    <div class="slides-grid">
        <?php foreach ($items as $item): ?>
            <div class="slide-item">
                <?php if ($item['media_type'] === 'image'): ?>
                    <img src="<?= e($base) ?>/<?= e($item['file_path']) ?>" alt="<?= e($item['title']) ?>">
                <?php else: ?>
                    <video src="<?= e($base) ?>/<?= e($item['file_path']) ?>" muted preload="metadata"></video>
                <?php endif; ?>
                <div class="slide-item-body">
                    <p class="slide-caption">
                        <?= e($item['title']) ?>
                        <span class="badge badge-outline"><?= $item['media_type'] === 'image' ? 'Photo' : 'Video' ?></span>
                    </p>
                    <p class="muted" style="font-size:0.8rem;margin:0 0 8px;">
                        <?= e(format_file_size((int) $item['file_size'])) ?> &middot; <?= e(format_date($item['created_at'])) ?>
                    </p>
                    <div class="slide-actions">
                        <?php if (is_admin_role()): ?>
                            <form method="post" action="" class="inline-form" onsubmit="return confirm('Delete this gallery item? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
