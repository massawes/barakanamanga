<?php
// Announcements / News (Matangazo) for the school website. Admin only.
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role();

$self = base_url() . '/admin/manage-announcements.php';
$errors = [];
$editing = null;

// ---- Save (new or edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $category = (string) ($_POST['category'] ?? 'announcement');
        $date = (string) ($_POST['published_on'] ?? '');
        $published = isset($_POST['is_published']) ? 1 : 0;
        $removeAttachment = isset($_POST['remove_attachment']);

        if ($title === '' || mb_strlen($title) > 200) {
            $errors[] = 'Please write a title (up to 200 characters).';
        }
        if ($body === '') {
            $errors[] = 'Please write the announcement text.';
        }
        if (!isset(ANNOUNCEMENT_CATEGORIES[$category])) {
            $category = 'announcement';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            $date = date('Y-m-d');
        }

        $old = null;
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $old = $stmt->fetch() ?: null;
            if (!$old) {
                $errors[] = 'That announcement no longer exists.';
            }
        }

        $attachment = null;
        if (!$errors) {
            [$ok, $message, $attachment] = save_announcement_attachment($_FILES['attachment'] ?? []);
            if (!$ok) {
                $errors[] = $message;
            }
        }

        if (!$errors) {
            $path = $old['attachment_path'] ?? null;
            $name = $old['attachment_name'] ?? null;
            if ($attachment || $removeAttachment) {
                delete_announcement_attachment($path);
                $path = $attachment['path'] ?? null;
                $name = $attachment['name'] ?? null;
            }
            $data = [
                'title' => $title, 'body' => $body, 'category' => $category, 'published_on' => $date,
                'path' => $path, 'name' => $name, 'pub' => $published,
            ];
            if ($old) {
                $pdo->prepare('UPDATE announcements SET title = :title, body = :body, category = :category, published_on = :published_on,
                    attachment_path = :path, attachment_name = :name, is_published = :pub WHERE id = :id')
                    ->execute($data + ['id' => $id]);
                flash_set('success', 'Announcement updated.');
            } else {
                $pdo->prepare('INSERT INTO announcements (title, body, category, published_on, attachment_path, attachment_name, is_published, created_by)
                    VALUES (:title, :body, :category, :published_on, :path, :name, :pub, :by)')
                    ->execute($data + ['by' => (int) ($_SESSION['admin_id'] ?? 0) ?: null]);
                flash_set('success', $published ? 'Announcement published on the school website.' : 'Announcement saved as a draft (not shown on the website).');
            }
            redirect($self);
        }
        // Keep what was typed when there is an error.
        $editing = ['id' => $id, 'title' => $title, 'body' => $body, 'category' => $category, 'published_on' => $date,
            'is_published' => $published, 'attachment_name' => $old['attachment_name'] ?? null];
    }
}

// ---- Delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare('SELECT attachment_path FROM announcements WHERE id = :id');
        $stmt->execute(['id' => $id]);
        if (($row = $stmt->fetch()) !== false) {
            $pdo->prepare('DELETE FROM announcements WHERE id = :id')->execute(['id' => $id]);
            delete_announcement_attachment($row['attachment_path']);
            flash_set('success', 'Announcement deleted.');
        }
    } else {
        flash_set('error', 'Your session expired. Please try again.');
    }
    redirect($self);
}

// ---- Open one for editing ----
if ($editing === null && ($editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT))) {
    $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editing = $stmt->fetch() ?: null;
}

$list = $pdo->query('SELECT * FROM announcements ORDER BY published_on DESC, id DESC')->fetchAll();
$form = $editing ?? ['id' => null, 'title' => '', 'body' => '', 'category' => 'announcement', 'published_on' => date('Y-m-d'), 'is_published' => 1, 'attachment_name' => null];

$pageTitle = 'Announcements / News — Admin Panel';
$activePage = 'manage-announcements';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Announcements / News</h1>
<p class="muted">Write announcements, news and events here. Published ones appear on the school website home page
(newest first) and on the <a href="<?= e(site_link('announcements.php')) ?>" target="_blank" rel="noopener">Announcements page</a>.
You can attach a file, e.g. joining instructions (PDF).</p>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="<?= e($self) ?>" enctype="multipart/form-data" class="upload-form">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e((string) ($form['id'] ?? '')) ?>">
    <?= csrf_field() ?>
    <h2 style="margin-top:0;"><?= $form['id'] ? 'Edit announcement' : 'New announcement' ?></h2>

    <label for="title">Title</label>
    <input type="text" id="title" name="title" maxlength="200" required value="<?= e($form['title']) ?>" placeholder="e.g. Form One 2027 joining instructions">

    <label for="category">Type</label>
    <select id="category" name="category">
        <?php foreach (ANNOUNCEMENT_CATEGORIES as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $form['category'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="published_on">Date</label>
    <input type="date" id="published_on" name="published_on" value="<?= e($form['published_on']) ?>">

    <label for="body">Text</label>
    <textarea id="body" name="body" rows="9" required placeholder="Write the full announcement. Leave an empty line between paragraphs."><?= e($form['body']) ?></textarea>
    <p class="field-hint">Leave an empty line between paragraphs. Web addresses (https://…) become clickable links.</p>

    <label for="attachment">Attachment (optional)</label>
    <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
    <p class="field-hint">PDF, Word or image, up to 20 MB.<?php if (!empty($form['attachment_name'])): ?> Current file: <strong><?= e($form['attachment_name']) ?></strong> (choose a new file to replace it).<?php endif; ?></p>
    <?php if (!empty($form['attachment_name'])): ?>
        <label class="checkbox-line"><input type="checkbox" name="remove_attachment" value="1"> Remove the current attachment</label>
    <?php endif; ?>

    <label class="checkbox-line"><input type="checkbox" name="is_published" value="1" <?= !empty($form['is_published']) ? 'checked' : '' ?>> Show on the website (untick to keep as a draft)</label>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
        <button type="submit" class="btn btn-primary"><?= $form['id'] ? 'Save changes' : 'Publish announcement' ?></button>
        <?php if ($form['id']): ?>
            <a href="<?= e($self) ?>" class="btn btn-outline">Cancel</a>
        <?php endif; ?>
    </div>
</form>

<h2 style="margin-top:32px;">All announcements (<?= count($list) ?>)</h2>
<?php if (!$list): ?>
    <p class="empty-state">No announcements yet. The home page hides the Announcements section until you publish one.</p>
<?php else: ?>
    <div class="announce-admin-list">
        <?php foreach ($list as $row): ?>
            <div class="announce-admin-item">
                <div>
                    <span class="badge badge-outline"><?= e(announcement_category_label($row['category'])) ?></span>
                    <?php if (!$row['is_published']): ?><span class="badge">Draft</span><?php endif; ?>
                    <strong class="announce-admin-title"><?= e($row['title']) ?></strong>
                    <span class="muted"><?= e(date('d M Y', strtotime($row['published_on']))) ?><?= $row['attachment_name'] ? ' · 📎 ' . e($row['attachment_name']) : '' ?></span>
                </div>
                <div class="slide-actions">
                    <?php if ($row['is_published']): ?>
                        <a class="btn btn-outline btn-small" href="<?= e(site_link('announcements.php?id=' . (int) $row['id'])) ?>" target="_blank" rel="noopener">View</a>
                    <?php endif; ?>
                    <a class="btn btn-outline btn-small" href="<?= e($self) ?>?edit=<?= (int) $row['id'] ?>">Edit</a>
                    <form method="post" action="<?= e($self) ?>" class="inline-form" onsubmit="return confirm('Delete this announcement? This cannot be undone.');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
