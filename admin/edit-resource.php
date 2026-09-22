<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flash_set('error', 'Resource not found.');
    redirect(base_url() . '/admin/manage-resources.php');
}

$stmt = $pdo->prepare('SELECT * FROM resources WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$resource = $stmt->fetch();

if (!$resource) {
    flash_set('error', 'Resource not found.');
    redirect(base_url() . '/admin/manage-resources.php');
}

$errors = [];
$currentYear = (int) date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $formLevel = $resource['form_level'];
        $year = $resource['year'];
        $version = $resource['software_version'];

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        // Only notes/summary/theory allow choosing a Form.
        if (in_array($resource['resource_type'], ['notes', 'summary', 'theory'], true)) {
            $formLevel = $_POST['form_level'] ?? '';
            if (!in_array($formLevel, VALID_FORMS, true)) {
                $errors[] = 'Please select a valid Form.';
            }
        }

        // Theory and practical require a year.
        if (in_array($resource['resource_type'], ['theory', 'practical'], true)) {
            $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
            if (!$year || $year < 2000 || $year > $currentYear + 1) {
                $errors[] = 'Please enter a valid year.';
            }
        }

        if ($resource['resource_type'] === 'software') {
            $version = trim($_POST['version'] ?? '');
        }

        $fileData = null;
        if (empty($errors) && !empty($_FILES['resource_file']['name'])) {
            [$ok, $message, $fileData] = handle_resource_upload($_FILES['resource_file'], $resource['resource_type'], $formLevel);
            if (!$ok) {
                $errors[] = $message;
            }
        }

        if (empty($errors)) {
            if ($fileData) {
                // Replace the physical file: delete the old one after a successful new upload.
                $oldPath = $resource['file_path'];
                $stmt = $pdo->prepare(
                    'UPDATE resources SET title = :title, description = :description, form_level = :form_level,
                     year = :year, software_version = :version, file_name = :file_name, file_path = :file_path,
                     file_size = :file_size, original_name = :original_name WHERE id = :id'
                );
                $stmt->execute([
                    'title'         => $title,
                    'description'   => $description ?: null,
                    'form_level'    => $formLevel,
                    'year'          => $year,
                    'version'       => $version ?: null,
                    'file_name'     => $fileData['file_name'],
                    'file_path'     => $fileData['file_path'],
                    'file_size'     => $fileData['file_size'],
                    'original_name' => $fileData['original_name'],
                    'id'            => $id,
                ]);
                delete_resource_file($oldPath);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE resources SET title = :title, description = :description, form_level = :form_level,
                     year = :year, software_version = :version WHERE id = :id'
                );
                $stmt->execute([
                    'title'       => $title,
                    'description' => $description ?: null,
                    'form_level'  => $formLevel,
                    'year'        => $year,
                    'version'     => $version ?: null,
                    'id'          => $id,
                ]);
            }

            flash_set('success', 'Resource updated successfully.');
            redirect(base_url() . '/admin/manage-resources.php');
        }
    }
    // Reload current values into $resource for redisplay on error.
    $resource['title'] = $title;
    $resource['description'] = $description;
    $resource['form_level'] = $formLevel;
    $resource['year'] = $year;
    $resource['software_version'] = $version;
}

$pageTitle = 'Edit Resource — Admin Panel';
$activePage = 'manage';
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Edit Resource</h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data" class="upload-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $resource['id'] ?>">

    <label for="title">Title</label>
    <input type="text" id="title" name="title" required value="<?= e($resource['title']) ?>">

    <?php if (in_array($resource['resource_type'], ['notes', 'summary', 'theory'], true)): ?>
        <label for="form_level">Form</label>
        <select id="form_level" name="form_level" required>
            <?php foreach (VALID_FORMS as $form): ?>
                <option value="<?= e($form) ?>" <?= $resource['form_level'] === $form ? 'selected' : '' ?>>
                    <?= e(form_label($form)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if (in_array($resource['resource_type'], ['theory', 'practical'], true)): ?>
        <label for="year">Year</label>
        <input type="number" id="year" name="year" required min="2000" max="<?= $currentYear + 1 ?>"
               value="<?= e((string) $resource['year']) ?>">
    <?php endif; ?>

    <?php if ($resource['resource_type'] === 'software'): ?>
        <label for="version">Version</label>
        <input type="text" id="version" name="version" value="<?= e($resource['software_version'] ?? '') ?>">
    <?php endif; ?>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3"><?= e($resource['description'] ?? '') ?></textarea>

    <p class="field-hint">
        Current file: <strong><?= e($resource['original_name']) ?></strong>
        (<?= e(format_file_size((int) $resource['file_size'])) ?>)
    </p>
    <label for="resource_file">Replace File (optional)</label>
    <input type="file" id="resource_file" name="resource_file">
    <p class="field-hint">Leave empty to keep the current file. No file size limit.</p>

    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="<?= e(base_url()) ?>/admin/manage-resources.php" class="btn btn-outline">Cancel</a>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
