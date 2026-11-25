<?php
/**
 * Announcements / News (Matangazo) for the school website (owner 2026-11-25).
 * Written by Admins in admin/manage-announcements.php, shown on the school home page
 * and on announcements.php. An optional attachment (PDF, Word, image) can be added,
 * e.g. joining instructions or a school calendar.
 */

const ANNOUNCEMENT_CATEGORIES = [
    'announcement' => 'Announcement',
    'news'         => 'News',
    'event'        => 'Event',
    'admission'    => 'Admission',
];
const ANNOUNCEMENT_UPLOAD_DIR = 'uploads/announcements';
const ANNOUNCEMENT_ALLOWED_EXT = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];
const ANNOUNCEMENT_MAX_BYTES = 20 * 1024 * 1024;

function ensure_announcements_table(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS announcements (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                body TEXT NOT NULL,
                category VARCHAR(20) NOT NULL DEFAULT 'announcement',
                published_on DATE NOT NULL,
                attachment_path VARCHAR(500) NULL,
                attachment_name VARCHAR(255) NULL,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_published (is_published, published_on)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {
        // Try again on a later request.
    }
}

/** Published announcements, newest first. */
function latest_announcements(PDO $pdo, int $limit = 5): array
{
    try {
        $stmt = $pdo->prepare('SELECT * FROM announcements WHERE is_published = 1 ORDER BY published_on DESC, id DESC LIMIT ' . max(1, $limit));
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function announcement_category_label(string $key): string
{
    return ANNOUNCEMENT_CATEGORIES[$key] ?? 'Announcement';
}

/** First ~$chars characters of the body as plain text, for lists. */
function announcement_excerpt(string $body, int $chars = 180): string
{
    $text = trim(preg_replace('/\s+/', ' ', $body));
    if (mb_strlen($text) <= $chars) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $chars), " ,.;:") . '…';
}

/** Body as safe HTML: paragraphs, line breaks, and web links made clickable. */
function announcement_body_html(string $body): string
{
    $out = [];
    foreach (preg_split('/\R{2,}/', trim($body)) as $para) {
        $html = nl2br(e($para));
        $html = preg_replace('~(https?://[^\s<]+)~', '<a href="$1" target="_blank" rel="noopener">$1</a>', $html);
        $out[] = '<p>' . $html . '</p>';
    }
    return implode("\n", $out);
}

/**
 * Saves an uploaded attachment. Returns [ok, message, ['path' => ..., 'name' => ...]].
 */
function save_announcement_attachment(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [true, '', null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'The attachment could not be uploaded (error ' . (int) $file['error'] . ').', null];
    }
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ANNOUNCEMENT_ALLOWED_EXT, true)) {
        return [false, 'Attachment type not allowed. Use PDF, Word (DOC/DOCX) or an image (JPG/PNG/WEBP).', null];
    }
    if ((int) $file['size'] > ANNOUNCEMENT_MAX_BYTES) {
        return [false, 'The attachment is larger than 20 MB.', null];
    }
    $dir = __DIR__ . '/../' . ANNOUNCEMENT_UPLOAD_DIR;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        return [false, 'Could not create the attachments folder on the server.', null];
    }
    $stored = bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) {
        return [false, 'Could not save the attachment on the server.', null];
    }
    $original = preg_replace('/[^\w .()-]+/u', '_', basename((string) $file['name']));
    return [true, '', ['path' => ANNOUNCEMENT_UPLOAD_DIR . '/' . $stored, 'name' => $original]];
}

function delete_announcement_attachment(?string $path): void
{
    if ($path && str_starts_with($path, ANNOUNCEMENT_UPLOAD_DIR . '/')) {
        @unlink(__DIR__ . '/../' . $path);
    }
}
