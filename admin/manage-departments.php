<?php
// Departments & Heads of Department (Admins only). Shown as flip tiles on the home page and Academics page.
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role();

$self = base_url() . '/admin/manage-departments.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
        redirect($self);
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $update = $pdo->prepare('UPDATE departments SET subject = :s, head_name = :h, head_phone = :p, sort_order = :o, is_visible = :v WHERE id = :id');
        foreach ((array) ($_POST['dept'] ?? []) as $id => $row) {
            $subject = trim((string) ($row['subject'] ?? ''));
            if ($subject === '' || !ctype_digit((string) $id)) {
                continue;
            }
            $update->execute([
                's'  => mb_substr($subject, 0, 120),
                'h'  => ($h = trim((string) ($row['head_name'] ?? ''))) !== '' ? mb_substr($h, 0, 150) : null,
                'p'  => ($p = trim((string) ($row['head_phone'] ?? ''))) !== '' ? mb_substr($p, 0, 40) : null,
                'o'  => (int) ($row['sort_order'] ?? 0),
                'v'  => isset($row['is_visible']) ? 1 : 0,
                'id' => (int) $id,
            ]);
        }
        flash_set('success', 'Departments saved.');
    } elseif ($action === 'add') {
        $subject = trim((string) ($_POST['new_subject'] ?? ''));
        if ($subject !== '') {
            $next = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM departments')->fetchColumn();
            $pdo->prepare('INSERT INTO departments (subject, sort_order) VALUES (:s, :o)')->execute(['s' => mb_substr($subject, 0, 120), 'o' => $next]);
            flash_set('success', 'Subject added.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM departments WHERE id = :id')->execute(['id' => (int) ($_POST['id'] ?? 0)]);
        flash_set('success', 'Subject removed.');
    }
    redirect($self);
}

$departments = $pdo->query('SELECT * FROM departments ORDER BY sort_order ASC, id ASC')->fetchAll();

$pageTitle = 'Departments — Admin Panel';
$activePage = 'manage-departments';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Departments &amp; Heads of Department</h1>
<p class="muted">Type the real name of each Head of Department. On the website a subject tile turns over to show the
name only when a name is filled in. The phone number is optional (the Contact button then calls it; otherwise it opens the
school Contact page). Untick "Show" to hide a subject.</p>

<form method="post" action="<?= e($self) ?>">
    <input type="hidden" name="action" value="save">
    <?= csrf_field() ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Subject</th><th>Head of Department</th><th>Phone (optional)</th><th>Show</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($departments as $d): $id = (int) $d['id']; ?>
                <tr>
                    <td data-label="Order"><input type="number" name="dept[<?= $id ?>][sort_order]" value="<?= (int) $d['sort_order'] ?>" style="width:70px"></td>
                    <td data-label="Subject"><input type="text" name="dept[<?= $id ?>][subject]" value="<?= e($d['subject']) ?>" maxlength="120" required></td>
                    <td data-label="Head of Department"><input type="text" name="dept[<?= $id ?>][head_name]" value="<?= e($d['head_name'] ?? '') ?>" maxlength="150" placeholder="e.g. Mr. John Doe"></td>
                    <td data-label="Phone"><input type="text" name="dept[<?= $id ?>][head_phone]" value="<?= e($d['head_phone'] ?? '') ?>" maxlength="40" placeholder="07xx xxx xxx"></td>
                    <td data-label="Show"><input type="checkbox" name="dept[<?= $id ?>][is_visible]" value="1" <?= $d['is_visible'] ? 'checked' : '' ?>></td>
                    <td data-label=""><button type="submit" form="del-<?= $id ?>" class="btn btn-danger btn-small" onclick="return confirm('Remove this subject?');">Remove</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:14px;">Save all</button>
</form>

<?php foreach ($departments as $d): ?>
    <form method="post" action="<?= e($self) ?>" id="del-<?= (int) $d['id'] ?>" style="display:none">
        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $d['id'] ?>"><?= csrf_field() ?>
    </form>
<?php endforeach; ?>

<form method="post" action="<?= e($self) ?>" class="upload-form" style="margin-top:28px;">
    <input type="hidden" name="action" value="add">
    <?= csrf_field() ?>
    <label for="new_subject">Add a subject / department</label>
    <input type="text" id="new_subject" name="new_subject" maxlength="120" placeholder="e.g. Agriculture">
    <button type="submit" class="btn btn-outline">Add</button>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
