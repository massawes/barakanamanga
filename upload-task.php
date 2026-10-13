<?php
require_once __DIR__ . '/includes/bootstrap.php';

// ------------------------------------------------------------
// PUBLIC task upload — deliberately requires NO login, so any
// student can submit their work. Because it is open to anyone,
// it is protected differently from the admin upload pages:
//   - narrower file-type whitelist (office documents only, see
//     allowed_extensions_for_type('task') in includes/functions.php)
//   - a hidden honeypot field to block simple spam bots
//   - a short per-session cooldown between submissions
// ------------------------------------------------------------

$base = base_url();
$errors = [];
$successTask = null;

const TASK_UPLOAD_COOLDOWN_SECONDS = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } elseif (!empty($_POST['website'] ?? '')) {
        // Honeypot field — invisible to real visitors, so if it has a
        // value the submission almost certainly came from a bot.
        $errors[] = 'Submission rejected.';
    } elseif (!empty($_SESSION['last_task_upload']) && (time() - $_SESSION['last_task_upload']) < TASK_UPLOAD_COOLDOWN_SECONDS) {
        $errors[] = 'Please wait a few seconds before submitting another task.';
    } else {
        $uploaderName = trim($_POST['uploader_name'] ?? '');
        $formLevel = $_POST['form_level'] ?? '';
        $title = trim($_POST['title'] ?? '');

        if ($uploaderName === '' || mb_strlen($uploaderName) > 150) {
            $errors[] = 'Please enter your full name (required).';
        }
        if (!in_array($formLevel, VALID_FORMS, true)) {
            $errors[] = 'Please select your Form.';
        }

        $file = $_FILES['task_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please choose a file to upload.';
        }

        if (empty($errors)) {
            [$ok, $message, $fileData] = handle_resource_upload($file, 'task', $formLevel);

            if (!$ok) {
                $errors[] = $message;
            } else {
                $taskTitle = $title !== '' ? $title : derive_title_from_filename($file['name']);

                $insert = $pdo->prepare(
                    "INSERT INTO tasks (uploader_name, title, form_level, file_name, file_path, file_size, original_name)
                     VALUES (:uploader_name, :title, :form_level, :file_name, :file_path, :file_size, :original_name)"
                );
                $insert->execute([
                    'uploader_name' => $uploaderName,
                    'title'         => $taskTitle,
                    'form_level'    => $formLevel,
                    'file_name'     => $fileData['file_name'],
                    'file_path'     => $fileData['file_path'],
                    'file_size'     => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                ]);

                $_SESSION['last_task_upload'] = time();
                $successTask = [
                    'id'    => (int) $pdo->lastInsertId(),
                    'title' => $taskTitle,
                ];
            }
        }
    }
}

$pageTitle = 'Upload Task | Namanga Secondary School';
$pageDescription = 'Students of Namanga Secondary School, Tanzania can upload their assignments and tasks here.';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Upload Task</h1>
        <p>Submit your assignment or task file — no account needed. Choose your Form, add your name, and upload your document.</p>
    </div>
</section>

<section class="page-content">
    <div class="container narrow">

        <?php if ($successTask): ?>
            <div class="alert alert-success">
                <strong>Uploaded successfully!</strong> "<?= e($successTask['title']) ?>" has been submitted.
            </div>
            <div class="resource-detail-actions" style="margin-bottom: 24px;">
                <a class="btn btn-outline btn-large" href="<?= e($base) ?>/task-view.php?id=<?= $successTask['id'] ?>" target="_blank" rel="noopener">View</a>
                <a class="btn btn-primary btn-large" href="<?= e($base) ?>/task-download.php?id=<?= $successTask['id'] ?>">Download</a>
            </div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <form method="post" action="" enctype="multipart/form-data" class="upload-form">
            <?= csrf_field() ?>

            <!-- Honeypot: real visitors never see or fill this in. -->
            <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                <label for="website">Leave this field blank</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <label for="uploader_name">Your Full Name</label>
            <input type="text" id="uploader_name" name="uploader_name" required maxlength="150"
                   placeholder="e.g. Juma Mwita"
                   value="<?= e($_POST['uploader_name'] ?? '') ?>">

            <label for="form_level">Your Form</label>
            <select id="form_level" name="form_level" required>
                <option value="">-- Select Form --</option>
                <?php foreach (VALID_FORMS as $form): ?>
                    <option value="<?= e($form) ?>" <?= (($_POST['form_level'] ?? '') === $form) ? 'selected' : '' ?>>
                        <?= e(form_label($form)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="title">Task Title (optional)</label>
            <input type="text" id="title" name="title" maxlength="255"
                   placeholder="e.g. ICT Assignment 3 — Computer Networks"
                   value="<?= e($_POST['title'] ?? '') ?>">
            <p class="field-hint">Leave this blank and the file name will be used instead.</p>

            <label for="task_file">Select File</label>
            <input type="file" id="task_file" name="task_file" required>
            <p class="field-hint">Allowed types: PDF, Word (doc/docx), Excel (xls/xlsx), PowerPoint (ppt/pptx), ODT/ODS/ODP, RTF, TXT, HTML, Access (accdb), Publisher (pub). Maximum one file per submission.</p>

            <button type="submit" class="btn btn-primary">Upload Task</button>
        </form>

        <p style="margin-top: 24px;">
            <a href="<?= e($base) ?>/tasks.php" class="back-link">View all submitted tasks &rarr;</a>
        </p>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
