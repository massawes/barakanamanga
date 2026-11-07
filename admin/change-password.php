<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();

// Every account can change its own password here. An account that still
// uses the temporary password (its first name) is sent here after login
// and cannot open any other admin page until it picks a new one.

$mustChange = !empty($_SESSION['admin_must_change']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password, full_name, username FROM admins WHERE id = :id');
        $stmt->execute(['id' => (int) $_SESSION['admin_id']]);
        $me = $stmt->fetch();

        if (!$me || !password_verify($current, $me['password'])) {
            $errors[] = 'Your current password is wrong.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'The new password must have at least 6 characters.';
        }
        if ($new !== $confirm) {
            $errors[] = 'The two new passwords are not the same.';
        }
        if ($me && $new !== '' && (
            password_verify($new, $me['password'])
            || strtolower($new) === temporary_password_for((string) ($me['full_name'] ?: $me['username']))
        )) {
            $errors[] = 'Choose a new password, different from the old one and from your name.';
        }

        if (empty($errors)) {
            $update = $pdo->prepare('UPDATE admins SET password = :p, must_change_password = 0 WHERE id = :id');
            $update->execute(['p' => password_hash($new, PASSWORD_DEFAULT), 'id' => (int) $_SESSION['admin_id']]);
            $_SESSION['admin_must_change'] = false;
            flash_set('success', 'Your password has been changed. Use the new password next time you log in.');
            redirect(base_url() . '/admin/dashboard.php');
        }
    }
}

$pageTitle = 'Change Password — Admin Panel';
$activePage = 'change-password';
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Change Password</h1>
<?php if ($mustChange): ?>
    <div class="alert alert-success">
        Welcome! You logged in with a temporary password (your first name).
        Please choose your own password now. Keep it secret.
    </div>
<?php endif; ?>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="adm-card">
    <div class="adm-card-body">
        <form method="post" action="" autocomplete="off">
            <?= csrf_field() ?>
            <label for="current_password">Current password<?= $mustChange ? ' (your first name, in small letters)' : '' ?></label>
            <div class="password-field">
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                <button type="button" class="password-toggle-btn" aria-label="Show password" tabindex="-1">
                    <?= svg_icon('eye', 'icon-svg icon-eye') ?>
                    <?= svg_icon('eye-off', 'icon-svg icon-eye-off') ?>
                </button>
            </div>

            <label for="new_password">New password (at least 6 characters)</label>
            <div class="password-field">
                <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
                <button type="button" class="password-toggle-btn" aria-label="Show password" tabindex="-1">
                    <?= svg_icon('eye', 'icon-svg icon-eye') ?>
                    <?= svg_icon('eye-off', 'icon-svg icon-eye-off') ?>
                </button>
            </div>

            <label for="confirm_password">Type the new password again</label>
            <div class="password-field">
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
                <button type="button" class="password-toggle-btn" aria-label="Show password" tabindex="-1">
                    <?= svg_icon('eye', 'icon-svg icon-eye') ?>
                    <?= svg_icon('eye-off', 'icon-svg icon-eye-off') ?>
                </button>
            </div>

            <button type="submit" class="btn btn-primary">Save New Password</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
