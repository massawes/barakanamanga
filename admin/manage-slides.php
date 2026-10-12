<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

$errors = [];

// ---- Handle upload (a single image, or every image in a chosen folder) ----
// The page's script sends a folder one image per request (ajax=1, JSON
// reply) so PHP's max_file_uploads limit never truncates a large folder;
// without JavaScript the whole form posts normally.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $isAjax = ($_POST['ajax'] ?? '') === '1';
    $failures = [];
    $successCount = 0;

    if (!csrf_verify()) {
        $failures[] = 'Your session expired. Please try again.';
    } else {
        $caption = trim($_POST['caption'] ?? '');
        $folderFiles = normalize_uploaded_files_field($_FILES['slide_folder'] ?? null);
        $singleFile  = normalize_uploaded_files_field($_FILES['slide_image'] ?? null);

        // Folders often hold non-image extras (Thumbs.db, desktop.ini…):
        // skip those quietly instead of reporting them as failures.
        $folderFiles = array_values(array_filter(
            $folderFiles,
            static fn(array $f): bool => in_array(strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
        ));
        $files = array_merge($folderFiles, $singleFile);

        if (empty($files)) {
            $failures[] = 'Please choose a folder, or a single image, to upload.';
        }

        $insert = $pdo->prepare(
            'INSERT INTO hero_slides (image_name, image_path, caption, sort_order) VALUES (:name, :path, :caption, :order)'
        );
        // The caption only makes sense for a single image.
        $useCaption = count($files) === 1 && $caption !== '';

        foreach ($files as $file) {
            [$ok, $message, $imageData] = handle_slide_image_upload($file);
            if (!$ok) {
                $failures[] = $file['name'] . ': ' . $message;
                continue;
            }
            $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM hero_slides')->fetch()['next_order'];
            $insert->execute([
                'name'    => $imageData['image_name'],
                'path'    => $imageData['image_path'],
                'caption' => $useCaption ? $caption : null,
                'order'   => $nextOrder,
            ]);
            $successCount++;
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => $successCount > 0, 'uploaded' => $successCount, 'errors' => $failures]);
        exit;
    }

    if ($successCount > 0) {
        flash_set('success', $successCount === 1 ? 'Slide uploaded successfully.' : $successCount . ' slides uploaded successfully.');
        if (!empty($failures)) {
            flash_set('error', implode(' | ', $failures));
        }
        redirect(base_url() . '/admin/manage-slides.php');
    }
    $errors = $failures;
}

// Summary message after the page's script finishes uploading a folder.
if (isset($_GET['uploaded'])) {
    $uploaded = max(0, (int) $_GET['uploaded']);
    $failed = max(0, (int) ($_GET['failed'] ?? 0));
    if ($uploaded > 0) {
        flash_set('success', $uploaded === 1 ? 'Slide uploaded successfully.' : $uploaded . ' slides uploaded successfully.');
    }
    if ($failed > 0) {
        flash_set('error', $failed . ' file(s) could not be uploaded (not a valid image or larger than 10 MB).');
    }
    redirect(base_url() . '/admin/manage-slides.php');
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

<form method="post" action="" enctype="multipart/form-data" class="upload-form" id="slideUploadForm">
    <input type="hidden" name="action" value="upload">
    <?= csrf_field() ?>

    <label for="slide_folder">Select Folder</label>
    <input type="file" id="slide_folder" name="slide_folder[]" multiple webkitdirectory>
    <p class="field-hint">Every image inside the selected folder is added to the slideshow. Other files in the folder are skipped.</p>

    <label for="slide_image">Or Select a Single Image</label>
    <input type="file" id="slide_image" name="slide_image" accept=".jpg,.jpeg,.png,.webp,.gif">
    <p class="field-hint">Allowed types: JPG, PNG, WEBP, GIF. Maximum size: 10 MB per image. A wide photo (landscape, at least 1200px wide) looks best.</p>

    <label for="caption">Caption (optional, only used for a single image)</label>
    <input type="text" id="caption" name="caption" placeholder="e.g. Our ICT Computer Lab">

    <button type="submit" class="btn btn-primary" id="slideUploadBtn">Upload</button>
    <p class="field-hint" id="slideUploadStatus" aria-live="polite"></p>
</form>

<script>
// Uploads a chosen folder one image per request, so folders with any
// number of photos work (PHP only accepts ~20 files in a single request).
(function () {
    var form = document.getElementById('slideUploadForm');
    var folderInput = document.getElementById('slide_folder');
    var singleInput = document.getElementById('slide_image');
    var button = document.getElementById('slideUploadBtn');
    var status = document.getElementById('slideUploadStatus');
    var imagePattern = /\.(jpe?g|png|webp|gif)$/i;

    form.addEventListener('submit', function (event) {
        var files = Array.prototype.filter.call(folderInput.files, function (f) { return imagePattern.test(f.name); });
        if (singleInput.files.length) {
            files.push(singleInput.files[0]);
        }
        if (!files.length) {
            event.preventDefault();
            status.textContent = folderInput.files.length
                ? 'The selected folder has no JPG, PNG, WEBP or GIF images.'
                : 'Please choose a folder, or a single image, to upload.';
            return;
        }
        if (!window.fetch || !window.FormData) {
            return; // very old browser: fall back to a normal form post
        }
        event.preventDefault();
        button.disabled = true;

        var token = form.querySelector('input[name="csrf_token"]');
        var caption = files.length === 1 ? document.getElementById('caption').value : '';
        var uploaded = 0, failed = 0, index = 0;

        function next() {
            if (index >= files.length) {
                window.location.href = window.location.pathname + '?uploaded=' + uploaded + '&failed=' + failed;
                return;
            }
            var file = files[index++];
            status.textContent = 'Uploading ' + index + ' of ' + files.length + ': ' + file.name + '…';
            var data = new FormData();
            data.append('action', 'upload');
            data.append('ajax', '1');
            if (token) { data.append('csrf_token', token.value); }
            data.append('caption', caption);
            data.append('slide_image', file);
            fetch(window.location.pathname, { method: 'POST', body: data, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (result) { if (result.ok) { uploaded++; } else { failed++; } })
                .catch(function () { failed++; })
                .then(next);
        }
        next();
    });
})();
</script>

<h2 style="margin-top:32px;">Current Slides (<?= count($slides) ?>)</h2>

<?php if (empty($slides)): ?>
    <p class="empty-state">No slides uploaded yet. The homepage will show the default plain banner until you add at least one image here.</p>
<?php else: ?>
    <div class="slides-grid">
        <?php foreach ($slides as $i => $slide): ?>
            <div class="slide-item">
                <img src="<?= e($base) ?>/<?= e(optimized_image($slide['image_path'], 600)) ?>" alt="<?= e($slide['caption'] ?? 'Homepage slide') ?>" loading="lazy">
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
