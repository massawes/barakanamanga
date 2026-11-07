<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role(); // Photos and videos (gallery, homepage slideshow): Admin only, not teachers.

$errors = [];
$failures = [];
$successCount = 0;
$uploadedItems = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $eventName = trim(preg_replace('/\s+/', ' ', $_POST['event_name'] ?? ''));
        $eventDate = trim($_POST['event_date'] ?? '');
        if ($eventDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            $eventDate = '';
        }
        if ($eventName === '') {
            $errors[] = 'Type the name of the event (for example: Form Four Graduation 2026).';
        }
        // Year of the event (required): the Photos page shows one year at a time.
        $eventYear = (int) ($_POST['event_year'] ?? 0);
        if (!in_array($eventYear, gallery_year_choices(), true)) {
            $errors[] = 'Choose the year of the event.';
        } elseif ($eventDate !== '' && (int) substr($eventDate, 0, 4) !== $eventYear) {
            $errors[] = 'The event date (' . $eventDate . ') is not in the year you chose (' . $eventYear . '). Please fix the year or the date.';
        }

        $folderFiles = normalize_uploaded_files_field($_FILES['gallery_folder'] ?? null);
        $singleFile  = normalize_uploaded_files_field($_FILES['gallery_single_file'] ?? null);
        $files = array_merge($folderFiles, $singleFile);

        if (empty($files)) {
            $errors[] = 'Please select a folder, or a single photo/video, to upload.';
        }

        if (empty($errors)) {
            $insert = $pdo->prepare(
                "INSERT INTO gallery_items (title, event_name, event_date, event_year, media_type, file_name, file_path, file_size, original_name)
                 VALUES (:title, :event_name, :event_date, :event_year, :media_type, :file_name, :file_path, :file_size, :original_name)"
            );

            $useTypedTitle = (count($files) === 1 && $title !== '');

            foreach ($files as $file) {
                [$ok, $message, $fileData] = handle_gallery_upload($file);
                if (!$ok) {
                    $failures[] = $file['name'] . ': ' . $message;
                    continue;
                }
                $itemTitle = $useTypedTitle ? $title : $eventName;
                $insert->execute([
                    'title'         => $itemTitle,
                    'event_name'    => $eventName,
                    'event_date'    => $eventDate !== '' ? $eventDate : null,
                    'event_year'    => $eventYear,
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
<p class="muted">Add photos and videos to The Latest. Type the event name, choose the year (and date), then choose the folder (or one file).</p>

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
        <a href="<?= e($base) ?>/gallery.php?type=image" class="btn btn-outline btn-small">See in Gallery</a>
    </div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data" class="upload-form">
    <?= csrf_field() ?>

    <label for="event_name">Event name (required)</label>
    <input type="text" id="event_name" name="event_name" required maxlength="150" list="known-events"
           placeholder="e.g. Form Four Graduation 2026" value="<?= e($_POST['event_name'] ?? '') ?>">
    <datalist id="known-events">
        <?php foreach ($pdo->query("SELECT DISTINCT event_name FROM gallery_items WHERE event_name IS NOT NULL ORDER BY event_name")->fetchAll(PDO::FETCH_COLUMN) as $ev): ?>
            <option value="<?= e($ev) ?>">
        <?php endforeach; ?>
    </datalist>
    <p class="field-hint">All photos of one event become ONE album on the Photos page, with this name written on the photos. Type an existing name to add photos to that album.</p>

    <label for="event_year">Year of the event (required)</label>
    <?php $pickedYear = (int) ($_POST['event_year'] ?? date('Y')); ?>
    <select id="event_year" name="event_year" required>
        <?php foreach (gallery_year_choices() as $y): ?>
            <option value="<?= $y ?>"<?= $y === $pickedYear ? ' selected' : '' ?>><?= $y ?></option>
        <?php endforeach; ?>
    </select>
    <p class="field-hint">On the Photos page visitors choose a year and see only that year's events. The year is also written after the event name on the photos (for example "Form Four Graduation <?= (int) date('Y') ?>").</p>

    <label for="event_date">Event date</label>
    <input type="date" id="event_date" name="event_date" value="<?= e($_POST['event_date'] ?? '') ?>">

    <label for="title">Title (optional, only used for a single file)</label>
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
