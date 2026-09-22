<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

$errors = [];
$failures = [];
$successCount = 0;
$uploadedResources = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');

        $folderFiles = normalize_uploaded_files_field($_FILES['resource_folder'] ?? null);
        $singleFile  = normalize_uploaded_files_field($_FILES['resource_single_file'] ?? null);
        $files = array_merge($folderFiles, $singleFile);

        if (empty($files)) {
            $errors[] = 'Please select a folder, or a single file, to upload.';
        }

        if (empty($errors)) {
            $insert = $pdo->prepare(
                "INSERT INTO resources (title, description, resource_type, form_level, file_name, file_path, file_size, original_name)
                 VALUES (:title, NULL, 'others', NULL, :file_name, :file_path, :file_size, :original_name)"
            );

            $useTypedTitle = (count($files) === 1 && $title !== '');

            foreach ($files as $file) {
                [$ok, $message, $fileData] = handle_resource_upload($file, 'others', null);
                if (!$ok) {
                    $failures[] = $file['name'] . ': ' . $message;
                    continue;
                }
                $resourceTitle = $useTypedTitle ? $title : derive_title_from_filename($file['name']);
                $insert->execute([
                    'title'         => $resourceTitle,
                    'file_name'     => $fileData['file_name'],
                    'file_path'     => $fileData['file_path'],
                    'file_size'     => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                ]);
                $uploadedResources[] = ['id' => (int) $pdo->lastInsertId(), 'title' => $resourceTitle];
                $successCount++;
            }

            if ($successCount > 0) {
                $message = $successCount === 1
                    ? 'Resource uploaded successfully.'
                    : $successCount . ' resources uploaded successfully.';
                if (!empty($failures)) {
                    $message .= ' (' . count($failures) . ' file(s) were skipped — see below.)';
                }
                flash_set('success', $message);
                if (!empty($failures)) {
                    flash_set('error', implode(' | ', $failures));
                }
                flash_set_uploaded_resources($uploadedResources);
                redirect(base_url() . '/admin/upload-others.php');
            } else {
                $errors = $failures;
            }
        }
    }
}

$pageTitle = 'Upload Others — Admin Panel';
$activePage = 'upload-others';
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Upload Others</h1>
<p class="muted">Use this for anything that doesn't fit Notes, Summary, Theory Past Papers, Form 4 Practical or Software. Optionally give it a Title, then choose the folder containing the files.</p>

<?php $justUploaded = flash_get_uploaded_resources(); ?>
<?php if (!empty($justUploaded)): ?>
    <div class="uploaded-results">
        <h2>Just Uploaded — View or Download</h2>
        <ul class="uploaded-list">
            <?php foreach ($justUploaded as $r): ?>
                <li>
                    <span class="uploaded-title"><?= e($r['title']) ?></span>
                    <span class="uploaded-actions">
                        <a href="<?= e($base) ?>/resource-view.php?id=<?= (int) $r['id'] ?>" class="btn btn-outline btn-small" target="_blank">View</a>
                        <a href="<?= e($base) ?>/download.php?id=<?= (int) $r['id'] ?>" class="btn btn-primary btn-small">Download</a>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data" class="upload-form">
    <?= csrf_field() ?>

    <label for="title">Title (only used for a single file)</label>
    <input type="text" id="title" name="title" value="<?= e($_POST['title'] ?? '') ?>">
    <p class="field-hint">Leave this blank when uploading a folder with several files — a title will be generated automatically from each file's name (you can rename it afterwards from Manage Resources).</p>

    <label for="resource_folder">Select Folder</label>
    <input type="file" id="resource_folder" name="resource_folder[]" multiple webkitdirectory>
    <p class="field-hint">Every file inside the selected folder will be uploaded as a separate "Others" resource. Allowed types: PDF, Word, PowerPoint, Excel, ZIP, RAR, JPG, PNG, TXT. There is no file size limit — upload files of any size.</p>

    <label for="resource_single_file">Or Select a Single File</label>
    <input type="file" id="resource_single_file" name="resource_single_file">
    <p class="field-hint">Use this instead of "Select Folder" above when you only want to upload one file at a time.</p>

    <button type="submit" class="btn btn-primary">Upload</button>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
