<?php
require_once __DIR__ . '/includes/bootstrap.php';

/**
 * Streams a ZIP containing every file in one category (optionally one
 * Form level, or one Gallery media type) so a visitor can download
 * everything in a section with a single click, instead of one file
 * at a time.
 *
 * Usage:
 *   download-all.php?category=notes&form=form1
 *   download-all.php?category=practical
 *   download-all.php?category=gallery&media=image
 */

set_time_limit(0);

if (!class_exists('ZipArchive')) {
    render_zip_error(
        'The "Download All" feature needs the PHP Zip extension, which is not enabled on this server yet. '
        . 'Ask your administrator to enable the "zip" extension in php.ini and restart Apache.'
    );
}

$validCategories = ['notes', 'summary', 'theory', 'practical', 'software', 'others', 'gallery'];
$category = $_GET['category'] ?? '';

if (!in_array($category, $validCategories, true)) {
    http_response_code(404);
    render_zip_error('This download link is not valid.');
}

$form = $_GET['form'] ?? '';
$formValid = in_array($form, VALID_FORMS, true);

$rows = [];
$zipLabel = 'Namanga-' . ucfirst($category);

if ($category === 'gallery') {
    $media = $_GET['media'] ?? '';
    $where = '';
    $params = [];
    if (in_array($media, ['image', 'video'], true)) {
        $where = ' WHERE media_type = :media';
        $params['media'] = $media;
        $zipLabel .= '-' . ucfirst($media) . 's';
    }
    try {
        $stmt = $pdo->prepare('SELECT * FROM gallery_items' . $where . ' ORDER BY created_at DESC');
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } catch (Exception $e) {
        $rows = [];
    }
} else {
    $where = 'resource_type = :type';
    $params = ['type' => $category];
    if ($formValid) {
        $where .= ' AND form_level = :form';
        $params['form'] = $form;
        $zipLabel .= '-' . strtoupper($form);
    }
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE {$where} ORDER BY created_at DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}

if (empty($rows)) {
    render_zip_error('There are no files in this section yet to download.');
}

$realUploadsDir = realpath(__DIR__ . '/uploads');
$tmpZipPath = tempnam(sys_get_temp_dir(), 'namanga_zip_');

$zip = new ZipArchive();
if ($zip->open($tmpZipPath, ZipArchive::OVERWRITE) !== true) {
    render_zip_error('Could not prepare the ZIP file. Please try again.');
}

$usedNames = [];
$addedCount = 0;

foreach ($rows as $row) {
    $fullPath = __DIR__ . '/' . ltrim($row['file_path'], '/');
    $realFilePath = realpath($fullPath);

    // Skip anything missing or outside uploads/ instead of failing the whole ZIP.
    if (!$realFilePath || !$realUploadsDir || strpos($realFilePath, $realUploadsDir) !== 0 || !is_file($realFilePath)) {
        continue;
    }

    $extension = pathinfo($row['file_name'], PATHINFO_EXTENSION);
    $baseName = pathinfo($row['original_name'] ?: $row['file_name'], PATHINFO_FILENAME);
    $baseName = preg_replace('/[^A-Za-z0-9 ._-]/', '_', $baseName);
    $entryName = $baseName . ($extension ? '.' . $extension : '');

    // Avoid overwriting files that share the same name inside the ZIP.
    $suffix = 1;
    while (isset($usedNames[$entryName])) {
        $entryName = $baseName . ' (' . $suffix . ')' . ($extension ? '.' . $extension : '');
        $suffix++;
    }
    $usedNames[$entryName] = true;

    $zip->addFile($realFilePath, $entryName);
    $addedCount++;
}

$zip->close();

if ($addedCount === 0) {
    @unlink($tmpZipPath);
    render_zip_error('None of the files in this section could be found on the server.');
}

$downloadName = preg_replace('/[^A-Za-z0-9._-]/', '-', $zipLabel) . '.zip';

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($tmpZipPath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('X-Content-Type-Options: nosniff');

readfile($tmpZipPath);
@unlink($tmpZipPath);
exit;

function render_zip_error(string $message): void
{
    global $pdo;
    $pageTitle = 'Download Unavailable — Namanga Digital Resource Centre';
    $base = base_url();
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-content"><div class="container narrow">';
    echo '<div class="error-box"><h2>Unable to Download</h2><p>' . e($message) . '</p>';
    echo '<a href="' . e($base) . '/index.php" class="btn btn-primary">Back to Home</a></div>';
    echo '</div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
