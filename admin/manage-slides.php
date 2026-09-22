<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

$errors = [];

// ---- Handle upload ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $caption = trim($_POST['caption'] ?? '');
        [$ok, $message, $imageData] = handle_slide_image_upload($_FILES['slide_image'] ?? []);
        if (!$ok) {
            $errors[] = $message;
        } else {
            $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM hero_slides')->fetch()['next_order'];
            $insert = $pdo->prepare(
                'INSERT INTO hero_slides (image_name, image_path, caption, sort_order) VALUES (:name, :path, :caption, :order)'
            );
            $insert->execute([
                'name'    => $imageData['image_name'],
                'path'    => $imageData['image_path'],
                'caption' => $caption ?: null,
                'order'   => $nextOrder,
            ]);
            flash_set('success', 'Slide uploaded successfully.');
            redirect(base_url() . '/admin/manage-slides.php');
        }
    }
}

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!is_admin_role()) {
        flash_set('error', 'Only an Admin account can delete a slide.');
        redirect(base_url() . '/admin/manage-slides.php');
    }
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM hero_slides WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $slide = $stmt->fetch();
            if ($slide) {
                $pdo->prepare('DELETE FROM hero_slides WHERE id = :id')->execute(['id' => $id]);
                delete_slide_file($slide['image_path']);
                flash_set('success', 'Slide deleted successfully.');
            }
        }
    }
    redirect(base_url() . '/admin/manage-slides.php');
}

// ---- Handle reorder (move up / down) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'move') {
    if (csrf_verify()) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $direction = $_POST['direction'] ?? '';
        if ($id && in_array($direction, ['up', 'down'], true)) {
            $slides = $pdo->query('SELECT id, sort_order FROM hero_slides ORDER BY sort_order ASC, id ASC')->fetchAll();
            $index = null;
            foreach ($slides as $i => $s) {
                if ((int) $s['id'] === $id) {
                    $index = $i;
                    break;
                }
            }
            $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
            if ($index !== null && isset($slides[$swapWith])) {
                $a = $slides[$index];
                $b = $slides[$swapWith];
                $update = $pdo->prepare('UPDATE hero_slides SET sort_order = :order WHERE id = :id');
                $update->execute(['order' => $b['sort_order'], 'id' => $a['id']]);
                $update->execute(['order' => $a['sort_order'], 'id' => $b['id']]);
            }
        }
    }
    redirect(base_url() . '/admin/manage-slides.php');
}

$slides = $pdo->query('SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC')->fetchAll();

$pageTitle = 'Homepage Slideshow — Admin Panel';
$activePage = 'manage-slides';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Homepage Slideshow</h1>
<p class="muted">Upload the photos you want rotating on the homepage banner. No code changes needed — add, reorder or remove images here and the homepage updates automatically.</p>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data" class="upload-form">
    <input type="hidden" name="action" value="upload">
    <?= csrf_field() ?>

    <label for="slide_image">Image</label>
    <input type="file" id="slide_image" name="slide_image" required accept=".jpg,.jpeg,.png,.webp,.gif">
    <p class="field-hint">Allowed types: JPG, PNG, WEBP, GIF. Maximum size: 10 MB. A wide photo (landscape, at least 1200px wide) looks best.</p>

    <label for="caption">Caption (optional)</label>
    <input type="text" id="caption" name="caption" placeholder="e.g. Our ICT Computer Lab">

    <button type="submit" class="btn btn-primary">Upload Slide</button>
</form>

<h2 style="margin-top:32px;">Current Slides (<?= count($slides) ?>)</h2>

<?php if (empty($slides)): ?>
    <p class="empty-state">No slides uploaded yet. The homepage will show the default plain banner until you add at least one image here.</p>
<?php else: ?>
    <div class="slides-grid">
        <?php foreach ($slides as $i => $slide): ?>
            <div class="slide-item">
                <img src="<?= e($base) ?>/<?= e($slide['image_path']) ?>" alt="<?= e($slide['caption'] ?? 'Homepage slide') ?>">
                <div class="slide-item-body">
                    <p class="slide-caption"><?= e($slide['caption'] ?: '(no caption)') ?></p>
                    <div class="slide-actions">
                        <form method="post" action="" class="inline-form">
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="id" value="<?= (int) $slide['id'] ?>">
                            <input type="hidden" name="direction" value="up">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-small" <?= $i === 0 ? 'disabled' : '' ?>>Move Up</button>
                        </form>
                        <form method="post" action="" class="inline-form">
                            <input type="hidden" name="action" value="move">
                            <input type="hidden" name="id" value="<?= (int) $slide['id'] ?>">
                            <input type="hidden" name="direction" value="down">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-small" <?= $i === count($slides) - 1 ? 'disabled' : '' ?>>Move Down</button>
                        </form>
                        <?php if (is_admin_role()): ?>
                            <form method="post" action="" class="inline-form" onsubmit="return confirm('Delete this slide?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $slide['id'] ?>">
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
