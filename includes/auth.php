<?php
/**
 * Authentication + role helpers for the admin panel.
 *
 * Two roles:
 *   - admin: full control (upload, edit, view, delete, manage staff accounts)
 *   - staff (teachers): can upload notes and exam papers, edit and view —
 *     never delete, and never upload photos/videos (gallery, slideshow).
 * Accounts are created only by the Super Admin (there is no public register page).
 * Included by every admin page (via bootstrap) to enforce login and role.
 */

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Call this at the very top of every protected admin page.
 * Redirects unauthenticated visitors to the login page.
 */
function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        redirect(base_url() . '/admin/login.php');
    }
    // A new account (or one whose password was reset) still uses the
    // temporary password: it must choose its own before doing anything else.
    if (!empty($_SESSION['admin_must_change']) && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'change-password.php') {
        redirect(base_url() . '/admin/change-password.php');
    }
}

/**
 * Temporary password for a new teacher account: the first name in small
 * letters (e.g. "ERICK SOLOMON MAHOO" -> "erick"). The teacher must change
 * it on the first login.
 */
function temporary_password_for(string $fullName): string
{
    $first = preg_split('/[\s.]+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY)[0] ?? '';
    return strtolower($first);
}

/** Username for a new account: the first name in small letters. */
function username_for(string $fullName): string
{
    return preg_replace('/[^a-z0-9]/', '', temporary_password_for($fullName));
}

function current_admin_role(): string
{
    return $_SESSION['admin_role'] ?? 'staff';
}

function is_admin_role(): bool
{
    return current_admin_role() === 'admin';
}

/**
 * Call this (after require_admin_login()) on any action that only the
 * full Admin role may perform — e.g. deleting a resource, a slide, or a
 * staff account. Staff accounts are bounced back to the dashboard with a
 * clear message rather than being allowed to proceed.
 */
function require_admin_role(): void
{
    if (!is_admin_role()) {
        flash_set('error', 'Only an Admin account can do that. Your account has Staff access (upload, edit, view).');
        redirect(base_url() . '/admin/dashboard.php');
    }
}

/**
 * The Super Admin is a single, specific account — the very first Admin
 * account ever created (see ensure_super_admin_setup()) — never just "any
 * account with role = admin". This is what keeps "Manage Staff Accounts"
 * hidden from every other account, even one later promoted to Admin.
 */
function is_super_admin(): bool
{
    return !empty($_SESSION['admin_is_super']);
}

/**
 * Call this (after require_admin_login()) on any page or action that only
 * the Super Admin account may use — currently just "Manage Staff Accounts".
 */
function require_super_admin(): void
{
    if (!is_super_admin()) {
        flash_set('error', 'Only the Super Admin account can do that.');
        redirect(base_url() . '/admin/dashboard.php');
    }
}

function admin_login(int $adminId, string $username, string $role, bool $isSuperAdmin = false, bool $mustChange = false): void
{
    // Prevent session fixation.
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_role'] = $role;
    $_SESSION['admin_is_super'] = $isSuperAdmin;
    $_SESSION['admin_must_change'] = $mustChange;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
