<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

$errors = [];
$failures = [];
$successCount = 0;
$uploadedItems = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');

        $folderFiles = normalize_uploaded_files_field($_FILES['gallery_folder'] ?? null);
        $singleFile  = normalize_uploaded_files_field($_FILES['gallery_single_file'] ?? null);
        $files = array_merge($folderFiles, $singleFile);

        if (empty($files)) {
            $errors[] = 'Please select a folder, or a single photo/video, to upload.';
        }

        if (empty($errors)) {
            $insert = $pdo->prepare(
                "INSERT INTO gallery_items (title, media_type, file_name, file_path, file_size, original_name)
                 VALUES (:title, :media_type, :file_name, :file_path, :file_size, :original_name)"
            );

            $useTypedTitle = (count($files) === 1 && $title !== '');

            foreach ($files as $file) {
                [$ok, $message, $fileData] = handle_gallery_upload($file);
                if (!$ok) {
                    $failures[] = $file['name'] . ': ' . $message;
                    continue;
                }
                $itemTitle = $useTypedTitle ? $title : derive_title_from_filename($file['name']);
                $insert->execute([
                    'title'         => $itemTitle,
                    'media_type'    => $fileData['media_type'],
                    'file_name'     => $fileData['file_name'],
                    'file_path'     => $fileData['file_path'],
                    'file_size'     => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                ]);
                $uploadedItems[] = [
                    'title'      => $itemTitle,
                    'file_path'  => $fileData['file_path'],
                    'media_type' => $fileData['media_type'],
                ];
                $successCount++;
            }

            if ($successCount > 0) {
                $message = $successCount === 1
                    ? 'Gallery item uploaded successfully.'
                    : $successCount . ' gallery items uploaded successfully.';
                if (!empty($failures)) {
                    $message .= ' (' . count($failures) . ' file(s) were skipped — see below.)';
                }
                flash_set('success', $message);
                if (!empty($failures)) {
                    flash_set('error', implode(' | ', $failures));
                }
                flash_set_uploaded_gallery_items($uploadedItems);
                redirect(base_url() . '/admin/upload-gallery.php');
            } else {
                $errors = $failures;
            }
        }
    }
}

$pageTitle = 'Upload Gallery — Admin Panel';
$activePage = 'upload-gallery';
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Upload Gallery</h1>
<p class="muted">Add photos and videos to the public Gallery page. Optionally give it a Title, then choose the folder containing the files.</p>

<?php $justUploaded = flash_get_uploaded_gallery_items(); ?>
<?php if (!empty($justUploaded)): ?>
    <div class="uploaded-results">
        <h2>Just Uploaded — View or Download</h2>
        <ul class="uploaded-list">
            <?php foreach ($justUploaded as $r): ?>
                <li>
                    <span class="uploaded-title"><?= e($r['title']) ?> (<?= $r['media_type'] === 'image' ? 'Photo' : 'Video' ?>)</span>
                    <span class="uploaded-actions">
                        <a href="<?= e($base) ?>/<?= e($r['file_path']) ?>" class="btn btn-outline btn-small" target="_blank">View</a>
                        <a href="<?= e($base) ?>/<?= e($r['file_path']) ?>" class="btn btn-primary btn-small" download>Download</a>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="<?= e($base) ?>/gallery.php" class="btn btn-outline btn-small">See in Gallery</a>
    </div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data" class="upload-form">
    <?= csrf_field() ?>

    <label for="title">Title (only used for a single file)</label>
    <input type="text" id="title" name="title" value="<?= e($_POST['title'] ?? '') ?>">
    <p class="field-hint">Leave this blank when uploading a folder with several files — a title will be generated automatically from each file's name (you can rename it afterwards from Manage Gallery).</p>

    <label for="gallery_folder">Select Folder</label>
    <input type="file" id="gallery_folder" name="gallery_folder[]" multiple webkitdirectory>
    <p class="field-hint">Every photo and video inside the selected folder is added to the Gallery. Allowed types: JPG, PNG, WEBP, GIF, MP4, WEBM, MOV, AVI, MKV, M4V. There is no file size limit — upload files of any size.</p>

    <label for="gallery_single_file">Or Select a Single File</label>
    <input type="file" id="gallery_single_file" name="gallery_single_file">
    <p class="field-hint">Use this instead of "Select Folder" above when you only want to upload one photo or video at a time.</p>

    <button type="submit" class="btn btn-primary">Upload</button>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
