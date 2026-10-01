<?php
/**
 * Shared helper functions used across the whole application.
 * Included by config bootstrap after the database connection.
 */

// ------------------------------------------------------------
// Super Admin migration
// ------------------------------------------------------------

/**
 * Makes sure the admins table has an `is_super_admin` column, and that
 * exactly one account (the very first Admin account ever created — usually
 * the one made via create-admin.php during initial setup) is marked as the
 * Super Admin. Only the Super Admin can see/use "Manage Staff Accounts",
 * even after other accounts get promoted to the regular 'admin' role.
 * Safe to run on every request: cheap, and never breaks a page if it fails
 * (e.g. the DB user lacks ALTER privileges) — it simply retries next time.
 */
function ensure_super_admin_setup(PDO $pdo): void
{
    try {
        $columnExists = (int) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'is_super_admin'"
        )->fetchColumn();

        if ($columnExists === 0) {
            $pdo->exec("ALTER TABLE admins ADD COLUMN is_super_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
        }

        $hasSuperAdmin = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE is_super_admin = 1")->fetchColumn();
        if ($hasSuperAdmin === 0) {
            $firstAdminId = $pdo->query(
                "SELECT id FROM admins WHERE role = 'admin' ORDER BY id ASC LIMIT 1"
            )->fetchColumn();
            if ($firstAdminId) {
                $update = $pdo->prepare('UPDATE admins SET is_super_admin = 1 WHERE id = :id');
                $update->execute(['id' => $firstAdminId]);
            }
        }
    } catch (Throwable $e) {
        // Silently skip this cycle; the next request will try again.
    }
}

/**
 * Creates the gallery_items table on demand, so an existing live install
 * (which pre-dates the Gallery feature) picks it up automatically the
 * first time any page loads — no manual SQL import required.
 */
function ensure_gallery_table_exists(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS gallery_items (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                media_type ENUM('image','video') NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT UNSIGNED NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {
        // Silently skip this cycle; the next request will try again.
    }
}

// ------------------------------------------------------------
// Basic output / escaping helpers
// ------------------------------------------------------------

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function base_url(): string
{
    // Works out the base folder the app is running from, e.g.
    // /namanga-resource-centre  so links work regardless of
    // where the project sits under htdocs.
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // If we are inside /admin/, go one level up.
    if (basename($scriptDir) === 'admin') {
        $scriptDir = dirname($scriptDir);
    }
    return rtrim($scriptDir, '/');
}

// ------------------------------------------------------------
// SEO helpers
// ------------------------------------------------------------

// The one public address of the live site. Canonical links, the sitemap
// and structured data always point here (even when browsing on XAMPP),
// so Google only ever sees a single URL for each page.
const SITE_URL = 'https://namangawebsite.freepage.cc';

/**
 * Absolute public URL of the page being viewed, for <link rel="canonical">.
 * index.php collapses to "/", and junk query parameters (InfinityFree's
 * "?i=1" bot-check redirect, tracking tags) are dropped so duplicates
 * don't get indexed separately.
 */
function canonical_url(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = substr($path, strlen(base_url())) ?: '/';
    if (basename($path) === 'index.php') {
        $path = substr($path, 0, -strlen('index.php'));
    }

    $keep = array_intersect_key($_GET, array_flip(['form', 'id']));
    ksort($keep);
    $query = $keep ? '?' . http_build_query($keep) : '';

    return SITE_URL . $path . $query;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// ------------------------------------------------------------
// Flash messages (success / error banners across redirects)
// ------------------------------------------------------------

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get(string $type): ?string
{
    if (!empty($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Carries the list of just-uploaded resources across the redirect after an
 * upload form is submitted, so the page can show direct "View"/"Download"
 * links right away — a Staff account has no other easy way to find the
 * file it just uploaded (it cannot see "Manage Resources", which is
 * Admin-only). Each item: ['id' => int, 'title' => string].
 */
function flash_set_uploaded_resources(array $items): void
{
    $_SESSION['flash_uploaded_resources'] = $items;
}

function flash_get_uploaded_resources(): array
{
    if (!empty($_SESSION['flash_uploaded_resources'])) {
        $items = $_SESSION['flash_uploaded_resources'];
        unset($_SESSION['flash_uploaded_resources']);
        return $items;
    }
    return [];
}

/**
 * Same idea as flash_set_uploaded_resources(), for the Gallery (which has
 * no resource-view.php equivalent — each item: ['title' => string,
 * 'file_path' => string, 'media_type' => 'image'|'video']).
 */
function flash_set_uploaded_gallery_items(array $items): void
{
    $_SESSION['flash_uploaded_gallery'] = $items;
}

function flash_get_uploaded_gallery_items(): array
{
    if (!empty($_SESSION['flash_uploaded_gallery'])) {
        $items = $_SESSION['flash_uploaded_gallery'];
        unset($_SESSION['flash_uploaded_gallery']);
        return $items;
    }
    return [];
}

// ------------------------------------------------------------
// CSRF protection
// ------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ------------------------------------------------------------
// Display helpers
// ------------------------------------------------------------

function format_file_size(int $bytes): string
{
    if ($bytes <= 0) {
        return '0 B';
    }
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = min($i, count($units) - 1);
    $value = $bytes / (1024 ** $i);
    return round($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
}

function format_date(string $datetime): string
{
    $ts = strtotime($datetime);
    return $ts ? date('d M Y', $ts) : '';
}

function form_label(?string $formLevel): string
{
    $map = [
        'form1' => 'Form 1',
        'form2' => 'Form 2',
        'form3' => 'Form 3',
        'form4' => 'Form 4',
    ];
    return $map[$formLevel] ?? '-';
}

function type_label(string $type): string
{
    $map = [
        'notes'     => 'Notes',
        'summary'   => 'Summary',
        'theory'    => 'Theory Past Paper',
        'practical' => 'Form 4 Practical',
        'software'  => 'Software',
        'others'    => 'Other Subjects',
    ];
    return $map[$type] ?? ucfirst($type);
}

const VALID_FORMS = ['form1', 'form2', 'form3', 'form4'];
const VALID_TYPES = ['notes', 'summary', 'theory', 'practical', 'software', 'others'];
const RESOURCES_PER_PAGE = 12;

// ------------------------------------------------------------
// Pagination (keeps resource-listing pages short, even as the
// number of uploaded files grows into the hundreds)
// ------------------------------------------------------------

/**
 * Reads and validates the current page number from ?page=.
 * Always returns at least 1, and never above $totalPages (so an
 * out-of-range page in the URL just falls back to the last page).
 */
function current_page_number(int $totalPages): int
{
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    if ($page < 1) {
        $page = 1;
    }
    if ($totalPages > 0 && $page > $totalPages) {
        $page = $totalPages;
    }
    return $page;
}

/**
 * Renders a simple "Page 1, 2, 3 ..." navigation bar as an HTML
 * string. $baseUrl should already include the path and any fixed
 * query params (e.g. "notes.php?form=form1") — the page number is
 * appended as &page=N or ?page=N as appropriate.
 */
function render_pagination(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $sep = (strpos($baseUrl, '?') === false) ? '?' : '&';
    $urlFor = fn (int $p) => e($baseUrl) . $sep . 'page=' . $p;

    $html = '<nav class="pagination" aria-label="Pagination">';

    if ($currentPage > 1) {
        $html .= '<a href="' . $urlFor($currentPage - 1) . '" class="page-link page-prev">&laquo; Prev</a>';
    }

    // Show a window of page numbers around the current page, plus the
    // first and last page, so the bar stays short even with 50+ pages.
    $window = 2;
    $shown = [];
    for ($p = 1; $p <= $totalPages; $p++) {
        if ($p === 1 || $p === $totalPages || abs($p - $currentPage) <= $window) {
            $shown[] = $p;
        }
    }

    $prev = null;
    foreach ($shown as $p) {
        if ($prev !== null && $p - $prev > 1) {
            $html .= '<span class="page-ellipsis">&hellip;</span>';
        }
        $active = $p === $currentPage ? ' active' : '';
        $html .= '<a href="' . $urlFor($p) . '" class="page-link' . $active . '">' . $p . '</a>';
        $prev = $p;
    }

    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $urlFor($currentPage + 1) . '" class="page-link page-next">Next &raquo;</a>';
    }

    $html .= '</nav>';
    return $html;
}

// ------------------------------------------------------------
// Upload directory mapping
// ------------------------------------------------------------

function upload_subdirectory(string $resourceType, ?string $formLevel): string
{
    switch ($resourceType) {
        case 'notes':
            return 'notes/' . $formLevel;
        case 'summary':
            return 'summaries/' . $formLevel;
        case 'theory':
            return 'theory/' . $formLevel;
        case 'practical':
            return 'practical/form4';
        case 'software':
            return 'software';
        case 'others':
            return 'others';
        default:
            throw new InvalidArgumentException('Unknown resource type.');
    }
}

/**
 * Allowed file extensions for a resource category, and the hard
 * block-list that is never allowed regardless of category. Shared by
 * both the upload-form validator and the filesystem sync scanner, so
 * the two never disagree about what is safe to publish.
 */
function allowed_extensions_for_type(string $resourceType): array
{
    $documentExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
    $softwareExtensions = ['zip', 'rar', 'exe', 'msi', '7z', 'iso', 'img'];
    // "Others" is a catch-all for anything that doesn't fit the categories
    // above, so it accepts a wider set of common document/image types too.
    $otherExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'txt'];

    if ($resourceType === 'software') {
        return $softwareExtensions;
    }
    if ($resourceType === 'others') {
        return $otherExtensions;
    }
    return $documentExtensions;
}

function blocked_extensions(): array
{
    return ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'pht', 'phar',
        'cgi', 'pl', 'py', 'asp', 'aspx', 'sh', 'js', 'html', 'htm', 'svg'];
}

/**
 * Validates and stores an uploaded file securely.
 *
 * Returns an array: [success(bool), message(string), data(array|null)]
 * data contains: file_name, file_path (relative, from project root),
 * file_size, original_name.
 */
function handle_resource_upload(array $file, string $resourceType, ?string $formLevel): array
{
    $allowedExtensions = allowed_extensions_for_type($resourceType);
    $blockedExtensions = blocked_extensions();

    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'Invalid file upload submission.', null];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return [false, 'Please choose a file to upload.', null];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return [false, 'The file is too large.', null];
        default:
            return [false, 'File upload failed. Please try again.', null];
    }

    // No application-level size ceiling — the site imposes no maximum file
    // size at all; the only real-world limit is the free disk space on the
    // server. (PHP's own upload_max_filesize / post_max_size are raised as
    // high as PHP allows via .htaccess in the project root, so they never
    // get in the way either — see that file for details.)
    if ($file['size'] <= 0) {
        return [false, 'The uploaded file appears to be empty.', null];
    }

    $originalName = $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($extension === '' || in_array($extension, $blockedExtensions, true)) {
        return [false, 'This file type is not allowed.', null];
    }
    if (!in_array($extension, $allowedExtensions, true)) {
        return [false, 'This file type is not allowed for this resource category. Allowed: ' . implode(', ', $allowedExtensions), null];
    }

    // Verify real MIME type (do not trust the browser blindly, but
    // also do not rely on finfo alone for zip/exe ambiguity — extension
    // whitelist above is the primary control).
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    $disallowedMimeFragments = ['php', 'x-httpd', 'text/x-sh', 'application/x-sh'];
    foreach ($disallowedMimeFragments as $fragment) {
        if (stripos($mime, $fragment) !== false) {
            return [false, 'This file type is not allowed.', null];
        }
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return [false, 'File upload failed validation.', null];
    }

    // Build safe unique storage name; never trust original filename.
    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;

    $subDir = upload_subdirectory($resourceType, $formLevel);
    $uploadsRoot = dirname(__DIR__) . '/uploads/';
    $targetDir = $uploadsRoot . $subDir . '/';

    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            return [false, 'Server storage folder could not be created.', null];
        }
    }

    $targetPath = $targetDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, 'File could not be saved on the server.', null];
    }

    @chmod($targetPath, 0644);

    return [true, 'ok', [
        'file_name'     => $safeName,
        'file_path'     => 'uploads/' . $subDir . '/' . $safeName,
        'file_size'     => $file['size'],
        'original_name' => $originalName,
    ]];
}

function delete_resource_file(string $relativeFilePath): void
{
    $fullPath = dirname(__DIR__) . '/' . ltrim($relativeFilePath, '/');
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// ------------------------------------------------------------
// Homepage slideshow image upload (separate from document uploads —
// images only, stored in uploads/slides/)
// ------------------------------------------------------------

function handle_slide_image_upload(array $file): array
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'Invalid file upload submission.', null];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return [false, 'Please choose an image to upload.', null];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return [false, 'The image is too large.', null];
        default:
            return [false, 'Image upload failed. Please try again.', null];
    }

    $maxSize = 10 * 1024 * 1024; // 10 MB — plenty for a homepage photo
    if ($file['size'] > $maxSize) {
        return [false, 'The image is too large. Maximum allowed size is 10 MB.', null];
    }
    if ($file['size'] <= 0) {
        return [false, 'The uploaded image appears to be empty.', null];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return [false, 'Only JPG, PNG, WEBP or GIF images are allowed.', null];
    }

    // Confirm it's a genuine image (not a renamed script) two ways:
    // real MIME type, and that getimagesize() can actually read it.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    if (!in_array($mime, $allowedMimes, true)) {
        return [false, 'This file is not a valid image.', null];
    }
    if (@getimagesize($file['tmp_name']) === false) {
        return [false, 'This file is not a valid image.', null];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return [false, 'File upload failed validation.', null];
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetDir = dirname(__DIR__) . '/uploads/slides/';

    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            return [false, 'Server storage folder could not be created.', null];
        }
    }

    $targetPath = $targetDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, 'Image could not be saved on the server.', null];
    }

    @chmod($targetPath, 0644);

    return [true, 'ok', [
        'image_name' => $safeName,
        'image_path' => 'uploads/slides/' . $safeName,
    ]];
}

function delete_slide_file(string $relativeFilePath): void
{
    $fullPath = dirname(__DIR__) . '/' . ltrim($relativeFilePath, '/');
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// ------------------------------------------------------------
// Gallery upload (public photo/video gallery — images and videos
// mixed together, stored in uploads/gallery/)
// ------------------------------------------------------------

function allowed_gallery_extensions(): array
{
    return [
        'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'video' => ['mp4', 'webm', 'mov', 'avi', 'mkv', 'm4v'],
    ];
}

function gallery_media_type_for_extension(string $extension): ?string
{
    foreach (allowed_gallery_extensions() as $mediaType => $extensions) {
        if (in_array($extension, $extensions, true)) {
            return $mediaType;
        }
    }
    return null;
}

/**
 * Validates and stores an uploaded Gallery file (photo or video).
 * Returns [success(bool), message(string), data(array|null)].
 * data contains: media_type, file_name, file_path, file_size, original_name.
 */
function handle_gallery_upload(array $file): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return [false, 'Invalid file upload submission.', null];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return [false, 'Please choose a file to upload.', null];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return [false, 'The file is too large.', null];
        default:
            return [false, 'File upload failed. Please try again.', null];
    }

    // No application-level size ceiling — same as the resource uploader.
    if ($file['size'] <= 0) {
        return [false, 'The uploaded file appears to be empty.', null];
    }

    $originalName = $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $mediaType = gallery_media_type_for_extension($extension);

    if ($mediaType === null) {
        return [false, 'Only photos (JPG, PNG, WEBP, GIF) or videos (MP4, WEBM, MOV, AVI, MKV, M4V) are allowed in the Gallery.', null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';

    if ($mediaType === 'image') {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes, true) || @getimagesize($file['tmp_name']) === false) {
            return [false, 'This file is not a valid image.', null];
        }
    } else {
        // Video containers report many different MIME types depending on
        // codec — just make sure it isn't a script disguised with a video
        // extension. The extension whitelist above is the primary control.
        $disallowedFragments = ['php', 'x-httpd', 'text/x-sh', 'application/x-sh', 'text/html'];
        foreach ($disallowedFragments as $fragment) {
            if (stripos($mime, $fragment) !== false) {
                return [false, 'This file is not a valid video.', null];
            }
        }
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return [false, 'File upload failed validation.', null];
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetDir = dirname(__DIR__) . '/uploads/gallery/';

    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            return [false, 'Server storage folder could not be created.', null];
        }
    }

    $targetPath = $targetDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, 'File could not be saved on the server.', null];
    }

    @chmod($targetPath, 0644);

    return [true, 'ok', [
        'media_type'    => $mediaType,
        'file_name'     => $safeName,
        'file_path'     => 'uploads/gallery/' . $safeName,
        'file_size'     => $file['size'],
        'original_name' => $originalName,
    ]];
}

function delete_gallery_file(string $relativeFilePath): void
{
    $fullPath = dirname(__DIR__) . '/' . ltrim($relativeFilePath, '/');
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// ------------------------------------------------------------
// Bulk upload helpers (upload a folder / several files at once)
// ------------------------------------------------------------

/**
 * Turns a multi-file $_FILES field (e.g. $_FILES['resource_files'] from
 * an <input name="resource_files[]" multiple> or a folder picker using
 * webkitdirectory) into a flat list of individual single-file arrays,
 * skipping any slot where nothing was actually chosen.
 */
function normalize_uploaded_files_field(?array $field): array
{
    $result = [];

    if (!$field || !isset($field['name'])) {
        return $result;
    }

    // Defensive: if it somehow arrives as a single (non-array) file, wrap it.
    if (!is_array($field['name'])) {
        if (($field['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $result[] = $field;
        }
        return $result;
    }

    $count = count($field['name']);
    for ($i = 0; $i < $count; $i++) {
        $error = $field['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue; // empty slot, nothing chosen here
        }
        $result[] = [
            'name'     => $field['name'][$i],
            'type'     => $field['type'][$i] ?? '',
            'tmp_name' => $field['tmp_name'][$i],
            'error'    => $error,
            'size'     => $field['size'][$i] ?? 0,
        ];
    }

    return $result;
}

/**
 * Builds a readable title from a filename when the administrator didn't
 * type one in (used for bulk/folder uploads where typing a title per
 * file isn't practical): "form2_computer_networks.pdf" -> "Form2 Computer Networks"
 */
function derive_title_from_filename(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-', '.'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    return ucwords(trim($name));
}

// ------------------------------------------------------------
// Filesystem <-> database sync
//
// Some teachers copy files straight into the Windows uploads/ folders
// (or delete them there) instead of using the website's upload form.
// This keeps the database in sync with whatever is actually on disk,
// so the website always reflects reality:
//   - a file that appears on disk becomes a resource automatically
//   - a resource whose file was removed from disk disappears too
// ------------------------------------------------------------

/**
 * Maps each uploads/ subfolder to its resource_type value in the
 * database. Must match upload_subdirectory()'s top-level folder names.
 */
function sync_folder_type_map(): array
{
    return [
        'notes'      => 'notes',
        'summaries'  => 'summary',
        'theory'     => 'theory',
        'practical'  => 'practical',
        'software'   => 'software',
        'others'     => 'others',
    ];
}

/**
 * Scans uploads/ and reconciles the resources table with what is
 * actually on disk. Cheap and safe to call often — a marker file
 * throttles real scans to at most once every $minIntervalSeconds
 * across ALL visitors, so normal page loads stay fast.
 *
 * Returns ['added' => int, 'removed' => int, 'skipped' => bool].
 */
function sync_filesystem_with_database(PDO $pdo, bool $force = false, int $minIntervalSeconds = 8): array
{
    $uploadsRoot = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadsRoot)) {
        return ['added' => 0, 'removed' => 0, 'skipped' => true];
    }

    $marker = $uploadsRoot . '/.sync_marker';
    if (!$force && is_file($marker) && (time() - filemtime($marker)) < $minIntervalSeconds) {
        return ['added' => 0, 'removed' => 0, 'skipped' => true];
    }
    // Touch immediately so overlapping requests don't all start scanning at once.
    @touch($marker);

    $added = 0;
    $removed = 0;

    // ---- 1. Remove resources whose file no longer exists on disk ----
    // (covers a file being deleted, moved, or renamed manually)
    $rows = $pdo->query('SELECT id, file_path FROM resources')->fetchAll();
    $knownPaths = [];
    foreach ($rows as $row) {
        $fullPath = dirname(__DIR__) . '/' . ltrim($row['file_path'], '/');
        if (is_file($fullPath)) {
            $knownPaths[$row['file_path']] = true;
        } else {
            $pdo->prepare('DELETE FROM resources WHERE id = :id')->execute(['id' => $row['id']]);
            $removed++;
        }
    }

    // ---- 2. Add resources for files that exist on disk but aren't tracked ----
    // (covers a file being copied in manually via Windows Explorer)
    $insert = $pdo->prepare(
        'INSERT INTO resources (title, description, resource_type, form_level, year, file_name, file_path, file_size, original_name)
         VALUES (:title, NULL, :type, :form, :year, :file_name, :file_path, :file_size, :original_name)'
    );

    foreach (sync_folder_type_map() as $folder => $resourceType) {
        $base = $uploadsRoot . '/' . $folder;
        if (!is_dir($base)) {
            continue;
        }

        $allowedExtensions = allowed_extensions_for_type($resourceType);
        $blockedExtensions = blocked_extensions();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $filename = $fileInfo->getFilename();
            if ($filename === '.htaccess' || str_starts_with($filename, '.')) {
                continue;
            }

            $relativePath = 'uploads/' . $folder . '/' . ltrim(
                str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($base))),
                '/'
            );

            if (isset($knownPaths[$relativePath])) {
                continue; // already tracked, nothing to do
            }

            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($extension === '' || in_array($extension, $blockedExtensions, true)
                || !in_array($extension, $allowedExtensions, true)) {
                // Never auto-publish a file type we wouldn't accept through
                // the normal upload form (keeps this feature safe).
                continue;
            }

            // Work out Form / Year from the folder path and filename.
            $subPath = str_replace('\\', '/', dirname(substr($fileInfo->getPathname(), strlen($base))));
            $pathParts = array_values(array_filter(explode('/', $subPath), fn ($p) => $p !== '' && $p !== '.'));

            $formLevel = null;
            foreach ($pathParts as $part) {
                if (in_array($part, VALID_FORMS, true)) {
                    $formLevel = $part;
                    break;
                }
            }
            if ($resourceType === 'practical') {
                $formLevel = 'form4';
            }

            $year = null;
            if (preg_match('/(19|20)\d{2}/', $filename, $matches)) {
                $year = (int) $matches[0];
            }

            $title = derive_title_from_filename($filename);

            $insert->execute([
                'title'         => $title !== '' ? $title : $filename,
                'type'          => $resourceType,
                'form'          => $formLevel,
                'year'          => $year,
                'file_name'     => $filename,
                'file_path'     => $relativePath,
                'file_size'     => $fileInfo->getSize(),
                'original_name' => $filename,
            ]);
            $knownPaths[$relativePath] = true;
            $added++;
        }
    }

    return ['added' => $added, 'removed' => $removed, 'skipped' => false];
}

// ------------------------------------------------------------
// Offline site assistant
// ------------------------------------------------------------
// This is not a language model — it recognises a small set of English +
// Swahili greetings/keywords, then searches this site's own resources and
// gallery tables for matches. It runs entirely on this PHP/MySQL server,
// with no internet connection or external AI service required, so it
// keeps working even if the school's internet is down.
// ------------------------------------------------------------

/**
 * Answers a visitor's typed question by searching the site's own
 * database. Returns an array with a reply string plus any matching
 * resources/gallery items, ready to be JSON-encoded by assistant.php.
 */
function assistant_answer(PDO $pdo, string $message): array
{
    $text = mb_strtolower(trim($message), 'UTF-8');

    if ($text === '') {
        return assistant_reply(
            "Niulize kuhusu Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Others, au Gallery — mfano: \"notes za form 2\" au \"software ya autocad\"."
        );
    }

    // ---- Small talk (checked first, so a greeting doesn't get treated
    //      as a failed resource search) ----
    if (preg_match('/^\s*(hi|hello|hey|habari|mambo|hujambo|salama|vipi)\b/u', $text)) {
        return assistant_reply(
            "Habari! Mimi ni msaidizi wa tovuti hii (ninafanya kazi bila internet — natafuta tu kwenye database ya tovuti hii). Niulize kuhusu Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Others, au Gallery — mfano: \"notes za form 2\"."
        );
    }
    if (preg_match('/\b(asante|thanks|thank you|shukrani)\b/u', $text)) {
        return assistant_reply("Karibu sana! Niko hapa ukihitaji msaada zaidi kutafuta resources.");
    }
    if (preg_match('/\b(msaada|help|nisaidie|unawezaje|unaweza nini)\b/u', $text)) {
        return assistant_reply(
            "Unaweza kuniuliza kuhusu: Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Others, au Gallery (picha/video). Unaweza pia kutaja kidato — mfano \"notes za form 1\" au \"software ya autocad\"."
        );
    }

    // ---- Resource-type keywords (English + Swahili) ----
    $typeKeywords = [
        'notes'     => ['notes', 'note', 'maelezo'],
        'summary'   => ['summary', 'summaries', 'muhtasari'],
        'theory'    => ['theory', 'nadharia', 'past paper', 'past papers', 'mtihani'],
        'practical' => ['practical', 'vitendo'],
        'software'  => ['software', 'program', 'programu', 'application'],
        'others'    => ['others', 'nyingine', 'mengine'],
    ];
    $matchedTypes = [];
    $consumedPhrases = [];
    foreach ($typeKeywords as $type => $phrases) {
        foreach ($phrases as $phrase) {
            if (str_contains($text, $phrase)) {
                $matchedTypes[$type] = true;
                $consumedPhrases[] = $phrase;
                break;
            }
        }
    }

    // ---- Gallery keywords (a separate table from resources) ----
    $galleryPhrases = ['gallery', 'picha', 'photo', 'photos', 'picture', 'pictures', 'video', 'videos', 'matukio'];
    $wantsGallery = false;
    foreach ($galleryPhrases as $phrase) {
        if (str_contains($text, $phrase)) {
            $wantsGallery = true;
            $consumedPhrases[] = $phrase;
            break;
        }
    }

    // ---- Form-level keywords ----
    $formKeywords = [
        'form1' => ['form 1', 'form1', 'kidato cha kwanza', 'kidato 1'],
        'form2' => ['form 2', 'form2', 'kidato cha pili', 'kidato 2'],
        'form3' => ['form 3', 'form3', 'kidato cha tatu', 'kidato 3'],
        'form4' => ['form 4', 'form4', 'kidato cha nne', 'kidato 4'],
    ];
    $matchedForm = null;
    foreach ($formKeywords as $form => $phrases) {
        foreach ($phrases as $phrase) {
            if (str_contains($text, $phrase)) {
                $matchedForm = $form;
                $consumedPhrases[] = $phrase;
                break 2;
            }
        }
    }

    // ---- Whatever free-text words are left over are used as a plain
    //      title/description keyword search (same idea as search.php) ----
    $stripped = $text;
    foreach ($consumedPhrases as $phrase) {
        $stripped = str_replace($phrase, ' ', $stripped);
    }
    $stopwords = [
        'the', 'a', 'an', 'is', 'are', 'do', 'you', 'have', 'for', 'me', 'please', 'can', 'i', 'want',
        'need', 'give', 'nipe', 'nina', 'nataka', 'tafadhali', 'naomba', 'ninahitaji', 'kwa', 'ya', 'za',
        'na', 'wa', 'la', 'ni', 'je', 'kuna', 'iko', 'zipo', 'vipi', 'how', 'where', 'what', 'which',
        'any', 'of', 'to', 'and', 'some', 'about',
    ];
    $words = preg_split('/[^\p{L}0-9]+/u', $stripped, -1, PREG_SPLIT_NO_EMPTY);
    $keywords = [];
    foreach ($words as $word) {
        if (mb_strlen($word) < 3 || in_array($word, $stopwords, true)) {
            continue;
        }
        $keywords[] = $word;
    }
    $keywords = array_slice(array_values(array_unique($keywords)), 0, 5);

    $resources = [];
    $galleryItems = [];

    if ($wantsGallery) {
        $params = [];
        $sql = "SELECT * FROM gallery_items";
        if (!empty($keywords)) {
            $kwConditions = [];
            foreach ($keywords as $i => $kw) {
                $kwConditions[] = "title LIKE :kw$i";
                $params["kw$i"] = '%' . $kw . '%';
            }
            $sql .= " WHERE " . implode(' OR ', $kwConditions);
        }
        $sql .= " ORDER BY created_at DESC LIMIT 6";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $galleryItems = $stmt->fetchAll();
    }

    if (!$wantsGallery || !empty($matchedTypes) || $matchedForm !== null || !empty($keywords)) {
        $conditions = [];
        $params = [];

        if (!empty($matchedTypes)) {
            $placeholders = [];
            $i = 0;
            foreach (array_keys($matchedTypes) as $type) {
                $key = "type$i";
                $placeholders[] = ":$key";
                $params[$key] = $type;
                $i++;
            }
            $conditions[] = "resource_type IN (" . implode(',', $placeholders) . ")";
        }

        if ($matchedForm !== null) {
            $conditions[] = "form_level = :form";
            $params['form'] = $matchedForm;
        }

        if (!empty($keywords)) {
            $kwConditions = [];
            foreach ($keywords as $i => $kw) {
                $kwConditions[] = "(title LIKE :kwt$i OR description LIKE :kwd$i)";
                $params["kwt$i"] = '%' . $kw . '%';
                $params["kwd$i"] = '%' . $kw . '%';
            }
            $conditions[] = '(' . implode(' OR ', $kwConditions) . ')';
        }

        if (!empty($conditions)) {
            $sql = "SELECT * FROM resources WHERE " . implode(' AND ', $conditions)
                 . " ORDER BY created_at DESC LIMIT 6";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resources = $stmt->fetchAll();
        }
    }

    $totalFound = count($resources) + count($galleryItems);
    if ($totalFound === 0) {
        $replyText = "Samahani, sikupata kitu kinachoendana na hilo. Jaribu maneno mengine, au vinjari kategoria kwenye menyu (Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Others, Gallery).";
    } elseif ($totalFound === 1) {
        $replyText = "Nimepata kitu kimoja kinachoendana na swali lako:";
    } else {
        $replyText = "Nimepata vitu " . $totalFound . " vinavyoweza kukusaidia:";
    }

    $searchUrl = null;
    if (!empty($keywords)) {
        $searchUrl = base_url() . '/search.php?q=' . urlencode(implode(' ', $keywords));
    }

    return [
        'reply' => $replyText,
        'resources' => array_map(static function (array $r): array {
            return [
                'id'            => (int) $r['id'],
                'title'         => $r['title'],
                'type_label'    => type_label($r['resource_type']),
                'form_label'    => form_label($r['form_level']),
                'view_url'      => base_url() . '/resource-view.php?id=' . (int) $r['id'],
                'download_url'  => base_url() . '/download.php?id=' . (int) $r['id'],
            ];
        }, $resources),
        'gallery' => array_map(static function (array $g): array {
            return [
                'id'         => (int) $g['id'],
                'title'      => $g['title'],
                'media_type' => $g['media_type'],
                'file_url'   => base_url() . '/' . $g['file_path'],
            ];
        }, $galleryItems),
        'search_url' => $searchUrl,
    ];
}

/**
 * Small helper so the short "no results found / greeting / thanks"
 * replies in assistant_answer() don't have to repeat the same empty
 * resources/gallery/search_url shape every time.
 */
function assistant_reply(string $text): array
{
    return [
        'reply'      => $text,
        'resources'  => [],
        'gallery'    => [],
        'search_url' => null,
    ];
}
