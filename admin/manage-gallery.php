<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role(); // Photos and videos (gallery, homepage slideshow): Admin only, not teachers.

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!is_admin_role()) {
        flash_set('error', 'Only an Admin account can delete a gallery item.');
        redirect(base_url() . '/admin/manage-gallery.php');
    }
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM gallery_items WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch();
            if ($item) {
                $pdo->prepare('DELETE FROM gallery_items WHERE id = :id')->execute(['id' => $id]);
                delete_gallery_file($item['file_path']);
                flash_set('success', 'Gallery item deleted successfully.');
            }
        }
    }
    redirect(base_url() . '/admin/manage-gallery.php');
}

// ---- Rename an event (album) or change its date: all its photos/videos follow ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rename_event') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $old = (string) ($_POST['old_name'] ?? '');
        $oldYear = (int) ($_POST['old_year'] ?? 0);
        $new = trim(preg_replace('/\s+/', ' ', $_POST['event_name'] ?? ''));
        $year = (int) ($_POST['event_year'] ?? 0);
        $date = trim($_POST['event_date'] ?? '');
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
        if ($new === '') {
            flash_set('error', 'The event name cannot be empty.');
        } elseif (!in_array($year, gallery_year_choices(), true)) {
            flash_set('error', 'Choose the year of the event.');
        } elseif ($date !== null && (int) substr($date, 0, 4) !== $year) {
            flash_set('error', 'The date ' . $date . ' is not in the year ' . $year . '. Please fix the year or the date.');
        } else {
            // "School Life" = photos that have no event yet. Only that event's photos of that year change.
            $where = $old === 'School Life' ? "(event_name IS NULL OR event_name = '' OR event_name = 'School Life')" : 'event_name = :old';
            $where .= ' AND COALESCE(event_year, YEAR(event_date), YEAR(created_at)) = :oy';
            $params = ['new' => $new, 'd' => $date, 'y' => $year, 'oy' => $oldYear];
            if ($old !== 'School Life') {
                $params['old'] = $old;
            }
            $stmt = $pdo->prepare("UPDATE gallery_items SET event_name = :new, event_date = :d, event_year = :y WHERE $where");
            $stmt->execute($params);
            flash_set('success', 'Event saved: ' . gallery_album_title($new, $year) . ' (' . $stmt->rowCount() . ' items).');
        }
    }
    redirect(base_url() . '/admin/manage-gallery.php');
}

// ---- Move one photo/video to another event ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_event') {
    if (!csrf_verify()) {
        flash_set('error', 'Your session expired. Please try again.');
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $new = trim(preg_replace('/\s+/', ' ', $_POST['event_name'] ?? ''));
        if ($id && $new !== '') {
            // Take the date and year of that event when it already exists (newest one with that name).
            $d = $pdo->prepare('SELECT event_date, event_year FROM gallery_items WHERE event_name = :n AND id <> :id
                                ORDER BY event_year DESC, event_date DESC LIMIT 1');
            $d->execute(['n' => $new, 'id' => $id]);
            $ev = $d->fetch() ?: [];
            if ($ev) {
                $pdo->prepare('UPDATE gallery_items SET event_name = :n, event_date = :d, event_year = :y WHERE id = :id')
                    ->execute(['n' => $new, 'd' => $ev['event_date'] ?: null, 'y' => $ev['event_year'] ?: null, 'id' => $id]);
            } else {
                // A new event name: the photo keeps its year, the old event date is cleared.
                $pdo->prepare('UPDATE gallery_items SET event_name = :n, event_date = NULL WHERE id = :id')
                    ->execute(['n' => $new, 'id' => $id]);
            }
            flash_set('success', 'Moved to the event "' . $new . '".');
        }
    }
    redirect(base_url() . '/admin/manage-gallery.php' . (isset($_GET['type']) ? '?type=' . urlencode((string) $_GET['type']) : ''));
}

$typeFilter = $_GET['type'] ?? '';
$sql = 'SELECT * FROM gallery_items';
$params = [];
if (in_array($typeFilter, ['image', 'video'], true)) {
    $sql .= ' WHERE media_type = :type';
    $params['type'] = $typeFilter;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$pageTitle = 'Manage Gallery — Admin Panel';
$activePage = 'manage-gallery';
$base = base_url();
include __DIR__ . '/../includes/admin-header.php';
?>

<h1>Manage Gallery</h1>
<p class="muted">Each event is one album on the Photos page, with its name written on the photos.
Change an event's name or date here, or move a photo to another event.</p>

<?php
$events = [];
foreach ($pdo->query('SELECT * FROM gallery_items ORDER BY created_at DESC')->fetchAll() as $row) {
    $n = gallery_event_name($row);
    $y = gallery_event_year($row);
    $k = $y . '|' . $n;
    $events[$k] ??= ['name' => $n, 'year' => $y, 'date' => $row['event_date'] ?? null, 'photos' => 0, 'videos' => 0];
    $events[$k]['date'] = $events[$k]['date'] ?: ($row['event_date'] ?? null);
    $events[$k][$row['media_type'] === 'image' ? 'photos' : 'videos']++;
}
krsort($events); // newest year first
?>
<div class="adm-card">
    <div class="adm-card-head">Events (albums), by year</div>
    <div class="adm-card-body">
        <?php foreach ($events as $ev): $name = $ev['name']; ?>
            <form method="post" action="" class="inline-form" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px;">
                <input type="hidden" name="action" value="rename_event">
                <input type="hidden" name="old_name" value="<?= e($name) ?>">
                <input type="hidden" name="old_year" value="<?= (int) $ev['year'] ?>">
                <?= csrf_field() ?>
                <input type="text" name="event_name" value="<?= e($name) ?>" required maxlength="150" style="flex:1 1 260px;margin:0;">
                <select name="event_year" required style="width:auto;margin:0;" aria-label="Year">
                    <?php foreach (gallery_year_choices() as $yc): ?>
                        <option value="<?= $yc ?>"<?= $yc === (int) $ev['year'] ? ' selected' : '' ?>><?= $yc ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="event_date" value="<?= e((string) $ev['date']) ?>" style="width:auto;margin:0;">
                <span class="muted" style="font-size:0.85rem;"><?= (int) $ev['photos'] ?> photos<?= $ev['videos'] ? ', ' . (int) $ev['videos'] . ' videos' : '' ?></span>
                <button type="submit" class="btn btn-primary btn-small">Save</button>
            </form>
        <?php endforeach; ?>
    </div>
</div>
<datalist id="known-events">
    <?php foreach (array_unique(array_column($events, 'name')) as $name): ?><option value="<?= e($name) ?>"><?php endforeach; ?>
</datalist>

<div class="filter-tabs">
    <a href="<?= e($base) ?>/admin/manage-gallery.php" class="<?= $typeFilter === '' ? 'active' : '' ?>">All</a>
    <a href="<?= e($base) ?>/admin/manage-gallery.php?type=image" class="<?= $typeFilter === 'image' ? 'active' : '' ?>">Photos</a>
    <a href="<?= e($base) ?>/admin/manage-gallery.php?type=video" class="<?= $typeFilter === 'video' ? 'active' : '' ?>">Videos</a>
</div>

<?php if (empty($items)): ?>
    <p class="empty-state">No gallery items found.</p>
<?php else: ?>
    <div class="slides-grid">
        <?php foreach ($items as $item): ?>
            <div class="slide-item">
                <?php if ($item['media_type'] === 'image'): ?>
                    <img src="<?= e($base) ?>/<?= e(optimized_image($item['file_path'], 600)) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                <?php else: ?>
                    <video src="<?= e($base) ?>/<?= e($item['file_path']) ?>" muted preload="metadata"></video>
                <?php endif; ?>
                <div class="slide-item-body">
                    <form method="post" action="" style="display:flex;gap:6px;margin:0 0 8px;">
                        <input type="hidden" name="action" value="set_event">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <?= csrf_field() ?>
                        <input type="text" name="event_name" list="known-events" value="<?= e(gallery_event_name($item)) ?>" required maxlength="150" style="margin:0;font-size:0.85rem;" aria-label="Event">
                        <button type="submit" class="btn btn-outline btn-small">Move</button>
                    </form>
                    <p class="slide-caption">
                        <?= e($item['title']) ?>
                        <span class="badge badge-outline"><?= $item['media_type'] === 'image' ? 'Photo' : 'Video' ?></span>
                    </p>
                    <p class="muted" style="font-size:0.8rem;margin:0 0 8px;">
                        <?= e(format_file_size((int) $item['file_size'])) ?> &middot; <?= e(format_date($item['created_at'])) ?>
                    </p>
                    <div class="slide-actions">
                        <?php if (is_admin_role()): ?>
                            <form method="post" action="" class="inline-form" onsubmit="return confirm('Delete this gallery item? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
