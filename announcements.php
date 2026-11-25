<?php
require_once __DIR__ . '/includes/bootstrap.php';
$base = base_url();

// One announcement (?id=) or the list (optionally ?type=news|event|...).
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$item = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = :id AND is_published = 1');
    $stmt->execute(['id' => $id]);
    $item = $stmt->fetch() ?: null;
    if (!$item) {
        http_response_code(404);
    }
}

$type = (string) ($_GET['type'] ?? '');
if (!isset(ANNOUNCEMENT_CATEGORIES[$type])) {
    $type = '';
}
$list = [];
if (!$item) {
    $sql = 'SELECT * FROM announcements WHERE is_published = 1' . ($type ? ' AND category = :type' : '') . ' ORDER BY published_on DESC, id DESC LIMIT 100';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($type ? ['type' => $type] : []);
    $list = $stmt->fetchAll();
}

if ($item) {
    $pageTitle = $item['title'] . ' | Namanga Secondary School';
    $pageDescription = announcement_excerpt($item['body'], 155);
} else {
    $pageTitle = 'News & Announcements | Namanga Secondary School';
    $pageDescription = 'News, events and announcements from Namanga Secondary School, Longido, Arusha.';
}
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1><?= $item ? 'News &amp; Announcements' : 'News &amp; Announcements' ?></h1>
        <p>Namanga Secondary School</p>
    </div>
</section>

<section class="page-content">
    <div class="container narrow">
        <?php if ($item): ?>
            <article class="news-article">
                <div class="news-meta">
                    <span class="news-cat"><?= e(announcement_category_label($item['category'])) ?></span>
                    <time datetime="<?= e($item['published_on']) ?>"><?= e(date('l, d F Y', strtotime($item['published_on']))) ?></time>
                </div>
                <h2 class="news-article-title"><?= e($item['title']) ?></h2>
                <div class="news-article-body"><?= announcement_body_html($item['body']) ?></div>
                <?php if ($item['attachment_path']): ?>
                    <p><a class="btn btn-gold" href="<?= e($base . '/' . $item['attachment_path']) ?>" download="<?= e($item['attachment_name'] ?: 'attachment') ?>">Download: <?= e($item['attachment_name'] ?: 'attachment') ?></a></p>
                <?php endif; ?>
            </article>
            <p><a href="<?= e($base) ?>/announcements.php" class="btn btn-outline">&larr; All news &amp; announcements</a></p>
        <?php else: ?>
            <?php if ($id): ?>
                <div class="alert alert-error">That announcement could not be found. It may have been removed.</div>
            <?php endif; ?>
            <div class="filter-tabs">
                <a href="<?= e($base) ?>/announcements.php" class="<?= $type === '' ? 'active' : '' ?>">All</a>
                <?php foreach (ANNOUNCEMENT_CATEGORIES as $key => $label): ?>
                    <a href="<?= e($base) ?>/announcements.php?type=<?= e($key) ?>" class="<?= $type === $key ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
            <?php if (!$list): ?>
                <p class="empty-state">There are no announcements here yet. Please check again soon.</p>
            <?php else: ?>
                <div class="news-list">
                    <?php foreach ($list as $row): ?>
                        <a class="news-row" href="<?= e($base) ?>/announcements.php?id=<?= (int) $row['id'] ?>">
                            <time datetime="<?= e($row['published_on']) ?>"><?= e(date('d M Y', strtotime($row['published_on']))) ?></time>
                            <span class="news-row-title"><?= e($row['title']) ?>
                                <small><?= e(announcement_excerpt($row['body'], 120)) ?></small>
                            </span>
                            <span class="news-row-cat"><?= e(announcement_category_label($row['category'])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
