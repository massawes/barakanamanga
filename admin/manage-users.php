<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_super_admin(); // Super Admin only — not every 'admin' role account.

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

$accounts = $pdo->query('SELECT * FROM admins ORDER BY role DESC, created_at ASC')->fetchAll();

$pageTitle = 'Manage Staff Accounts — Admin Panel';
$activePage = 'manage-users';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Manage Staff Accounts</h1>
<p class="muted">Everyone who has registered for an account. Staff can upload, edit and view resources but never delete. Only Admin accounts can delete resources, slides, or manage other accounts.</p>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account): ?>
                <?php $isSelf = (int) $account['id'] === (int) $_SESSION['admin_id']; ?>
                <tr>
                    <td data-label="Username"><?= e($account['username']) ?><?= $isSelf ? ' (you)' : '' ?></td>
                    <td data-label="Role">
                        <span class="badge <?= $account['role'] === 'admin' ? '' : 'badge-outline' ?>">
                            <?= !empty($account['is_super_admin']) ? 'Super Admin' : e(ucfirst($account['role'])) ?>
                        </span>
                    </td>
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
