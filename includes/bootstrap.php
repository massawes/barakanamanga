<?php
/**
 * Single entry point every page includes first.
 * Starts the session, loads DB config, helper functions and auth.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookie settings before starting the session.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookieParams['path'],
        'domain'   => $cookieParams['domain'],
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/icons.php';

// Make sure the Super Admin column/account exists before anything else
// runs (login.php reads it, and every admin page's sidebar checks it).
ensure_super_admin_setup($pdo);

// Make sure the Gallery table exists (auto-migrates existing installs
// that pre-date the Gallery feature — no manual SQL import needed).
ensure_gallery_table_exists($pdo);

// There is no file-size limit on this site — uploads of any size are
// allowed (the .htaccess in the project root pushes PHP's own
// upload_max_filesize / post_max_size ceilings as high as PHP allows,
// since those two cannot be changed here — PHP enforces them before a
// script even runs). memory_limit and the execution/input time limits
// CAN be raised at runtime, so we remove them here too as a safety net in
// case the .htaccess values aren't picked up (e.g. PHP running as CGI/FPM).
@ini_set('memory_limit', '-1');
@set_time_limit(0);

if (!APP_DEBUG) {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Keep the database in sync with whatever files actually sit in uploads/
// on disk, so a file copied in (or deleted) directly through Windows
// Explorer shows up (or disappears) on the website automatically —
// no upload form or manual database edit required. Throttled internally
// so this stays cheap even under normal browsing traffic. Never allowed
// to break a page if something about the scan goes wrong.
try {
    sync_filesystem_with_database($pdo);
} catch (Throwable $e) {
    // Silently skip this cycle; the next request will try again.
}
