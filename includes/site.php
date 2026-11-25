<?php
/**
 * One codebase, two websites (owner 2026-11-25):
 *
 *   https://namangasecondary.sc.tz         School website: tells people about the school
 *                                          (home, about, academics, admissions, results, announcements, gallery, contact).
 *   https://masomo.namangasecondary.sc.tz  Student Portal ("Masomo"): notes, past papers, practicals, Ask AI.
 *
 * Both share the same files, database, uploads and admin panel. Which website a visitor sees depends only
 * on the address (host) they opened. A page opened on the wrong address is sent (301) to the right one.
 *
 * On XAMPP / localhost: add ?site=portal or ?site=school once to switch (remembered in a cookie).
 */

const SCHOOL_URL = 'https://namangasecondary.sc.tz';
const PORTAL_URL = 'https://masomo.namangasecondary.sc.tz';
const LIVE_DOMAIN = 'namangasecondary.sc.tz';
const PORTAL_HOST_PREFIX = 'masomo.';

// Pages that belong to the Student Portal. Every other public page belongs to the school website.
const PORTAL_PAGES = [
    'notes.php', 'theory.php', 'practical.php', 'resource-view.php', 'download.php', 'download-all.php',
    'search.php', 'summaries.php', 'software.php', 'others.php', 'tasks.php', 'task-view.php',
    'task-download.php', 'upload-task.php', 'download-app.php', 'assistant.php',
];
// Pages that work on both addresses.
const SHARED_PAGES = ['index.php', 'sitemap.php', 'robots.php'];

function request_host(): string
{
    return strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
}

function is_live_host(): bool
{
    $host = request_host();
    return $host === LIVE_DOMAIN || str_ends_with($host, '.' . LIVE_DOMAIN);
}

/** True when the visitor is on the Student Portal. */
function is_portal(): bool
{
    static $portal = null;
    if ($portal !== null) {
        return $portal;
    }
    if (is_live_host()) {
        return $portal = str_starts_with(request_host(), PORTAL_HOST_PREFIX);
    }
    // Local testing (XAMPP, php -S): ?site=portal|school, remembered in a cookie.
    $choice = $_GET['site'] ?? ($_COOKIE['nss_site'] ?? 'school');
    if (isset($_GET['site']) && in_array($_GET['site'], ['portal', 'school'], true) && PHP_SAPI !== 'cli') {
        setcookie('nss_site', $_GET['site'], ['path' => '/', 'samesite' => 'Lax']);
    }
    return $portal = ($choice === 'portal');
}

/** Public address of the website being viewed (no trailing slash). */
function current_site_url(): string
{
    return is_portal() ? PORTAL_URL : SCHOOL_URL;
}

/**
 * Sends a page opened on the wrong address to the right website (only on the live domain).
 * Admin, API, uploads and assets work on both addresses.
 */
function enforce_site_split(): void
{
    if (!is_live_host() || PHP_SAPI === 'cli') {
        return;
    }
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_contains($script, '/admin/') || str_contains($script, '/api/')) {
        return;
    }
    $page = basename($script);
    if (in_array($page, SHARED_PAGES, true)) {
        return;
    }
    $portalPage = in_array($page, PORTAL_PAGES, true);
    if ($portalPage === is_portal()) {
        return;
    }
    $target = ($portalPage ? PORTAL_URL : SCHOOL_URL) . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $target, true, 301);
    exit;
}

/** Link to a page on the right website: same-site pages stay relative, others get the full address. */
function site_link(string $page): string
{
    $file = (string) parse_url($page, PHP_URL_PATH);
    $isPortalPage = in_array(basename($file), PORTAL_PAGES, true);
    if ($isPortalPage === is_portal() || in_array(basename($file), SHARED_PAGES, true) && $file !== 'index.php') {
        return base_url() . '/' . ltrim($page, '/');
    }
    if (!is_live_host()) {
        // Local: switch site with ?site=… so links still work on XAMPP.
        $sep = str_contains($page, '?') ? '&' : '?';
        return base_url() . '/' . ltrim($page, '/') . $sep . 'site=' . ($isPortalPage ? 'portal' : 'school');
    }
    return ($isPortalPage ? PORTAL_URL : SCHOOL_URL) . '/' . ltrim($page, '/');
}

/** Home page of the other website (school <-> portal). */
function other_site_home(): string
{
    if (!is_live_host()) {
        return base_url() . '/index.php?site=' . (is_portal() ? 'school' : 'portal');
    }
    return (is_portal() ? SCHOOL_URL : PORTAL_URL) . '/';
}
