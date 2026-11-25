<?php
// Alumni register (Admins only). Former students sign up on alumni.php; their details are private.
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role();

$self = base_url() . '/admin/manage-alumni.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare('DELETE FROM alumni WHERE id = :id')->execute(['id' => (int) ($_POST['id'] ?? 0)]);
        flash_set('success', 'Alumni entry deleted.');
    } else {
        flash_set('error', 'Your session expired. Please try again.');
    }
    redirect($self);
}

$rows = $pdo->query('SELECT * FROM alumni ORDER BY created_at DESC, id DESC')->fetchAll();

// CSV export (Excel opens it).
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="namanga_alumni_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Full name', 'Year completed', 'Last Form', 'Phone', 'Email', 'Occupation', 'Location', 'Message', 'Registered']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['full_name'], $r['year_completed'], $r['last_form'], $r['phone'], $r['email'], $r['occupation'], $r['location'], $r['message'], $r['created_at']]);
    }
    exit;
}

$pageTitle = 'Alumni — Admin Panel';
$activePage = 'manage-alumni';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Alumni (<?= count($rows) ?>)</h1>
<p class="muted">Former students who registered on the <a href="<?= e(site_link('alumni.php')) ?>" target="_blank" rel="noopener">Alumni page</a>.
These details are private: only Admins can see them.</p>
<?php if ($rows): ?>
    <p><a class="btn btn-outline" href="<?= e($self) ?>?export=csv">Download as Excel (CSV)</a></p>
<?php endif; ?>

<?php if (!$rows): ?>
    <p class="empty-state">No alumni have registered yet.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Completed</th><th>Phone / Email</th><th>Occupation &amp; location</th><th>Message</th><th>Registered</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td data-label="Name"><?= e($r['full_name']) ?></td>
                <td data-label="Completed"><?= e(trim(($r['last_form'] ?? '') . ' ' . ($r['year_completed'] ?? ''))) ?></td>
                <td data-label="Phone / Email"><?= e($r['phone'] ?? '') ?><br><?= e($r['email'] ?? '') ?></td>
                <td data-label="Occupation"><?= e($r['occupation'] ?? '') ?><br><?= e($r['location'] ?? '') ?></td>
                <td data-label="Message"><?= nl2br(e($r['message'] ?? '')) ?></td>
                <td data-label="Registered"><?= e(format_date($r['created_at'])) ?></td>
                <td data-label="">
                    <form method="post" action="<?= e($self) ?>" class="inline-form" onsubmit="return confirm('Delete this entry?');">
                        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
