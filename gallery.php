<?php
require_once __DIR__ . '/includes/bootstrap.php';

// The Latest: two pages chosen from the menu, no buttons on the page.
//   gallery.php?type=image            -> one album card per event
//   gallery.php?type=image&album=Name -> all photos of that event
//   gallery.php?type=video            -> videos only
// gallery.php without a type (old "All Media" link) shows the Photos page.
$type = ($_GET['type'] ?? '') === 'video' ? 'video' : 'image';
$albumName = trim((string) ($_GET['album'] ?? ''));
$base = base_url();

$items = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM gallery_items WHERE media_type = :type ORDER BY created_at DESC, id ASC');
    $stmt->execute(['type' => $type]);
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    // gallery_items table not created yet: the empty state shows.
}

$albums = $type === 'image' ? gallery_albums($items) : [];
$album = null;
if ($type === 'image' && $albumName !== '') {
    foreach ($albums as $a) {
        if ($a['name'] === $albumName) {
            $album = $a;
        }
    }
}

if ($type === 'video') {
    [$heading, $intro] = ['Videos', 'Videos from life at Namanga Secondary School.'];
} elseif ($album) {
    [$heading, $intro] = [$album['name'], trim(gallery_event_date($album['date']) . ' · ' . count($album['photos']) . ' photos', ' ·')];
} else {
    [$heading, $intro] = ['Photos', 'Photos from life at Namanga Secondary School, by event.'];
}
$pageTitle = $heading . ' | The Latest | Namanga Secondary School';
$pageDescription = $type === 'video' ? $intro : ($album ? $album['name'] . ' — photos from Namanga Secondary School.' : $intro);
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1><?= e($heading) ?></h1>
        <p><?= e($intro) ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (empty($items)): ?>
            <p class="empty-state">Nothing here yet. Please check back soon.</p>

        <?php elseif ($type === 'video'): ?>
            <div class="video-wall">
                <?php foreach ($items as $item): ?>
                    <figure class="video-tile">
                        <video src="<?= e($base) ?>/<?= e($item['file_path']) ?>" controls preload="metadata"></video>
                        <figcaption class="media-label">
                            <?php if ($d = gallery_event_date($item['event_date'] ?? null)): ?><span class="media-date"><?= e($d) ?></span><?php endif; ?>
                            <span class="media-title"><?= e(trim((string) ($item['event_name'] ?? '')) ?: $item['title']) ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>

        <?php elseif ($album): ?>
            <p class="album-back"><a href="<?= e($base) ?>/gallery.php?type=image">&larr; All photo albums</a></p>
            <div class="photo-wall">
                <?php foreach ($album['photos'] as $i => $item): ?>
                    <?php $caption = $album['name'] . ' — photo ' . ($i + 1) . ' of ' . count($album['photos']); ?>
                    <a href="<?= e($base) ?>/<?= e(optimized_image($item['file_path'], 2400)) ?>" target="_blank" rel="noopener"
                       class="photo-tile gallery-photo-link" data-title="<?= e($caption) ?>">
                        <img src="<?= e($base) ?>/<?= e(optimized_image($item['file_path'], 1200)) ?>"
                             alt="<?= e($caption) ?>" loading="lazy" decoding="async">
                        <span class="media-label">
                            <?php if ($d = gallery_event_date($album['date'])): ?><span class="media-date"><?= e($d) ?></span><?php endif; ?>
                            <span class="media-title"><?= e($album['name']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div id="lightbox" class="lightbox" hidden>
                <button type="button" class="lightbox-btn lightbox-close" aria-label="Close"><?= svg_icon('close', 'icon-svg') ?></button>
                <button type="button" class="lightbox-btn lightbox-prev" aria-label="Previous photo">&lsaquo;</button>
                <img src="" alt="">
                <button type="button" class="lightbox-btn lightbox-next" aria-label="Next photo">&rsaquo;</button>
                <p class="lightbox-caption"></p>
            </div>

        <?php else: ?>
            <?php if ($albumName !== ''): ?>
                <p class="empty-state">That album was not found. Here are all the photo albums.</p>
            <?php endif; ?>
            <div class="album-wall">
                <?php foreach ($albums as $a): ?>
                    <a href="<?= e($base) ?>/gallery.php?type=image&amp;album=<?= e(rawurlencode($a['name'])) ?>" class="album-card">
                        <img src="<?= e($base) ?>/<?= e(optimized_image($a['cover']['file_path'], 1200)) ?>"
                             alt="<?= e($a['name']) ?>" loading="lazy" decoding="async">
                        <span class="media-label">
                            <span class="media-date"><?= e(gallery_event_date($a['date']) ?: count($a['photos']) . (count($a['photos']) === 1 ? ' PHOTO' : ' PHOTOS')) ?></span>
                            <span class="media-title"><?= e($a['name']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
