<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_super_admin(); // Super Admin only — not every 'admin' role account.

/**
 * Creates one teacher account: username and temporary password come from
 * the first name. Returns the new username, or null when it already exists.
 */
function create_staff_account(PDO $pdo, string $fullName, string $username = ''): ?string
{
    $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
    $username = $username !== '' ? strtolower(trim($username)) : username_for($fullName);
    $check = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE LOWER(username) = :u');
    $check->execute(['u' => $username]);
    if ((int) $check->fetchColumn() > 0) {
        return null;
    }
    $insert = $pdo->prepare(
        "INSERT INTO admins (username, full_name, password, role, must_change_password)
         VALUES (:u, :n, :p, 'staff', 1)"
    );
    $insert->execute([
        'u' => $username,
        'n' => strtoupper($fullName),
        'p' => password_hash(temporary_password_for($fullName), PASSWORD_DEFAULT),
    ]);
    return $username;
}

// ---- Create one account ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        if ($username === '') {
            $username = username_for($fullName);
        }
        if (temporary_password_for($fullName) === '' || strlen($username) < 3 || !preg_match('/^[a-z0-9._-]+$/i', $username)) {
            flash_set('error', "Type the teacher's full name. The username must have at least 3 letters (letters and numbers only).");
        } elseif (($made = create_staff_account($pdo, $fullName, $username)) === null) {
            flash_set('error', 'The username "' . strtolower($username) . '" is already used. Type another username (for example first name + second name).');
        } else {
            flash_set('success', 'Account created. Username: ' . $made . ', temporary password: ' . temporary_password_for($fullName) . ' (the teacher must change it on the first login).');
        }
    }
    redirect(base_url() . '/admin/manage-users.php');
}

// ---- Create accounts for every teacher on the school list ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_all') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $made = 0;
        $skipped = [];
        foreach (require __DIR__ . '/../includes/staff-list.php' as $name) {
            if (create_staff_account($pdo, $name) !== null) {
                $made++;
            } else {
                $skipped[] = username_for($name);
            }
        }
        flash_set('success', $made . ' teacher accounts created. Username and temporary password = first name in small letters.'
            . ($skipped ? ' Already had an account (not changed): ' . implode(', ', $skipped) . '.' : ''));
    }
    redirect(base_url() . '/admin/manage-users.php');
}

// ---- Reset a password back to the temporary one (first name) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $target = $pdo->prepare('SELECT username, full_name FROM admins WHERE id = :id');
        $target->execute(['id' => $id]);
        $row = $target->fetch();
        if (!$row || $id === (int) $_SESSION['admin_id']) {
            flash_set('error', 'You cannot reset that password here. To change your own password use "Change Password".');
        } else {
            $temp = temporary_password_for((string) ($row['full_name'] ?: $row['username']));
            $pdo->prepare('UPDATE admins SET password = :p, must_change_password = 1 WHERE id = :id')
                ->execute(['p' => password_hash($temp, PASSWORD_DEFAULT), 'id' => $id]);
            flash_set('success', 'Password of ' . $row['username'] . ' is now the temporary password: ' . $temp . ' (must be changed on the next login).');
        }
    }
    redirect(base_url() . '/admin/manage-users.php');
}

// ---- Handle role change (promote/demote) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_role') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $newRole = $_POST['role'] ?? '';
        if ($id && in_array($newRole, ['admin', 'staff'], true)) {
            if ($id === (int) $_SESSION['admin_id']) {
                flash_set('error', 'You cannot change your own role.');
            } else {
                // Never allow the last remaining Admin to be demoted.
                if ($newRole === 'staff') {
                    $adminCount = (int) $pdo->query("SELECT COUNT(*) AS total FROM admins WHERE role = 'admin'")->fetch()['total'];
                    $target = $pdo->prepare('SELECT role FROM admins WHERE id = :id');
                    $target->execute(['id' => $id]);
                    $targetRole = $target->fetchColumn();
                    if ($targetRole === 'admin' && $adminCount <= 1) {
                        flash_set('error', 'You cannot demote the last remaining Admin account.');
                        redirect(base_url() . '/admin/manage-users.php');
                    }
                }
                $update = $pdo->prepare('UPDATE admins SET role = :role WHERE id = :id');
                $update->execute(['role' => $newRole, 'id' => $id]);
                flash_set('success', 'Account role updated successfully.');
            }
        }
    }
    redirect(base_url() . '/admin/manage-users.php');
}

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id === (int) $_SESSION['admin_id']) {
            flash_set('error', 'You cannot delete your own account while logged in.');
        } elseif ($id) {
            $target = $pdo->prepare('SELECT role FROM admins WHERE id = :id');
            $target->execute(['id' => $id]);
            $targetRole = $target->fetchColumn();
            if ($targetRole === 'admin') {
                $adminCount = (int) $pdo->query("SELECT COUNT(*) AS total FROM admins WHERE role = 'admin'")->fetch()['total'];
                if ($adminCount <= 1) {
                    flash_set('error', 'You cannot delete the last remaining Admin account.');
                    redirect(base_url() . '/admin/manage-users.php');
                }
            }
            $pdo->prepare('DELETE FROM admins WHERE id = :id')->execute(['id' => $id]);
            flash_set('success', 'Account deleted successfully.');
        }
    }
    redirect(base_url() . '/admin/manage-users.php');
}

$accounts = $pdo->query('SELECT * FROM admins ORDER BY is_super_admin DESC, role ASC, username ASC')->fetchAll();

$pageTitle = 'Manage Staff Accounts — Admin Panel';
$activePage = 'manage-users';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Manage Staff Accounts</h1>
<p class="muted">Only you (Super Admin) create accounts: there is no public register page. A new account gets
username and temporary password = the teacher's first name in small letters (e.g. ERICK SOLOMON MAHOO &rarr; <strong>erick</strong> / <strong>erick</strong>).
On the first login the teacher must choose a new password.
Teachers (Staff) upload notes and exam papers only. Only Admin accounts upload photos/videos, manage the slideshow and delete.</p>

<div class="adm-card">
    <div class="adm-card-head">Create a teacher account</div>
    <div class="adm-card-body">
        <form method="post" action="" autocomplete="off">
            <input type="hidden" name="action" value="create_user">
            <?= csrf_field() ?>
            <label for="full_name">Teacher's full name</label>
            <input type="text" id="full_name" name="full_name" required placeholder="e.g. ERICK SOLOMON MAHOO">
            <label for="new_username">Username (leave empty = first name)</label>
            <input type="text" id="new_username" name="username" placeholder="e.g. erick">
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
    </div>
</div>

<?php
$listNames = require __DIR__ . '/../includes/staff-list.php';
$existing = array_map('strtolower', array_column($accounts, 'username'));
$missing = array_values(array_filter($listNames, fn ($n) => !in_array(username_for($n), $existing, true)));
?>
<div class="adm-card">
    <div class="adm-card-head">Teachers on the school list (<?= count($listNames) ?>)</div>
    <div class="adm-card-body">
        <?php if ($missing): ?>
            <p><?= count($missing) ?> teachers on the list have no account yet: <?= e(implode(', ', $missing)) ?>.</p>
            <form method="post" action="" onsubmit="return confirm('Create <?= count($missing) ?> teacher accounts now?');">
                <input type="hidden" name="action" value="create_all">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Create accounts for all <?= count($missing) ?> teachers</button>
            </form>
        <?php else: ?>
            <p class="muted">Every teacher on the list has an account.</p>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Full name</th>
                <th>Role</th>
                <th>Password</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account): ?>
                <?php $isSelf = (int) $account['id'] === (int) $_SESSION['admin_id']; ?>
                <tr>
                    <td data-label="Username"><?= e($account['username']) ?><?= $isSelf ? ' (you)' : '' ?></td>
                    <td data-label="Full name"><?= e($account['full_name'] ?? '') ?></td>
                    <td data-label="Role">
                        <span class="badge <?= $account['role'] === 'admin' ? '' : 'badge-outline' ?>">
                            <?= !empty($account['is_super_admin']) ? 'Super Admin' : e(ucfirst($account['role'])) ?>
                        </span>
                    </td>
                    <td data-label="Password"><?= !empty($account['must_change_password']) ? 'Temporary (not changed yet)' : 'Own password' ?></td>
                    <td data-label="Created"><?= e(format_date($account['created_at'])) ?></td>
                    <td data-label="Actions" class="actions-cell">
                        <?php if (!$isSelf): ?>
                            <form method="post" action="" class="inline-form">
                                <input type="hidden" name="action" value="set_role">
                                <input type="hidden" name="id" value="<?= (int) $account['id'] ?>">
                                <input type="hidden" name="role" value="<?= $account['role'] === 'admin' ? 'staff' : 'admin' ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-small">
                                    <?= $account['role'] === 'admin' ? 'Demote to Staff' : 'Promote to Admin' ?>
                                </button>
                            </form>
                            <form method="post" action="" class="inline-form" onsubmit="return confirm('Reset this password to the temporary one (first name)?');">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="id" value="<?= (int) $account['id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-small">Reset Password</button>
                            </form>
                            <form method="post" action="" class="inline-form" onsubmit="return confirm('Delete this account? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?= (int) $account['id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
