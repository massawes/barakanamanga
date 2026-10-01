<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$mode = ($_GET['mode'] ?? '') === 'view' ? 'view' : 'download';

if (!$id) {
    http_response_code(404);
    render_download_error('Resource not found.');
}

$stmt = $pdo->prepare('SELECT * FROM resources WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$resource = $stmt->fetch();

if (!$resource) {
    http_response_code(404);
    render_download_error('Resource not found.');
}

$fullPath = __DIR__ . '/' . ltrim($resource['file_path'], '/');
// Guard against path traversal: the resolved path must stay inside uploads/.
$realUploadsDir = realpath(__DIR__ . '/uploads');
$realFilePath = realpath($fullPath);

if (!$realFilePath || !$realUploadsDir || strpos($realFilePath, $realUploadsDir) !== 0 || !is_file($realFilePath)) {
    render_download_error('This file is unavailable. It may have been removed.');
}

$extension = strtolower(pathinfo($resource['file_name'], PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'zip'  => 'application/zip',
    'rar'  => 'application/vnd.rar',
    '7z'   => 'application/x-7z-compressed',
    'exe'  => 'application/octet-stream',
    'msi'  => 'application/octet-stream',
];
$mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

// "View" is only meaningful for PDFs; anything else always downloads.
$disposition = ($mode === 'view' && $extension === 'pdf') ? 'inline' : 'attachment';

$downloadName = pathinfo($resource['original_name'], PATHINFO_FILENAME) . '.' . $extension;
$downloadName = preg_replace('/[^A-Za-z0-9 ._-]/', '_', $downloadName);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mimeType);
header('Content-Disposition: ' . $disposition . '; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($realFilePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('X-Content-Type-Options: nosniff');

readfile($realFilePath);
exit;

function render_download_error(string $message): void
{
    global $pageTitle, $base;
    $pageTitle = 'File Unavailable | Namanga Secondary School';
    $noindex = true;
    $base = base_url();
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-content"><div class="container narrow">';
    echo '<div class="error-box"><h2>Unable to Download</h2><p>' . e($message) . '</p>';
    echo '<a href="' . e($base) . '/index.php" class="btn btn-primary">Back to Home</a></div>';
    echo '</div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
