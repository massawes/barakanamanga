<?php
/**
 * XML sitemap, generated on the fly from the live database so it always
 * reflects the resources that currently exist — no manual maintenance.
 * Linked from robots.txt; submit this URL in Google Search Console too.
 */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$origin = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$base = base_url();

// Static, always-present pages.
$staticPages = [
    ['loc' => '/index.php', 'priority' => '1.0'],
    ['loc' => '/notes.php', 'priority' => '0.8'],
    ['loc' => '/summaries.php', 'priority' => '0.8'],
    ['loc' => '/theory.php', 'priority' => '0.8'],
    ['loc' => '/practical.php', 'priority' => '0.8'],
    ['loc' => '/software.php', 'priority' => '0.7'],
    ['loc' => '/others.php', 'priority' => '0.6'],
    ['loc' => '/gallery.php', 'priority' => '0.6'],
    ['loc' => '/about.php', 'priority' => '0.5'],
    ['loc' => '/contact.php', 'priority' => '0.5'],
];

// Individual resources, so each notes/past-paper page can be found and
// indexed directly by search engines too.
$resources = [];
try {
    $resources = $pdo->query(
        'SELECT id, updated_at, created_at FROM resources ORDER BY id ASC'
    )->fetchAll();
} catch (Exception $e) {
    // Table not ready yet — sitemap just skips resource URLs this run.
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($staticPages as $page): ?>
    <url>
        <loc><?= htmlspecialchars($origin . $base . $page['loc'], ENT_XML1, 'UTF-8') ?></loc>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>
<?php foreach ($resources as $resource): ?>
    <url>
        <loc><?= htmlspecialchars($origin . $base . '/resource-view.php?id=' . $resource['id'], ENT_XML1, 'UTF-8') ?></loc>
<?php if (!empty($resource['updated_at'])): ?>
        <lastmod><?= date('Y-m-d', strtotime($resource['updated_at'])) ?></lastmod>
<?php elseif (!empty($resource['created_at'])): ?>
        <lastmod><?= date('Y-m-d', strtotime($resource['created_at'])) ?></lastmod>
<?php endif; ?>
        <priority>0.6</priority>
    </url>
<?php endforeach; ?>
</urlset>
