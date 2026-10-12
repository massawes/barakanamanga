<?php
/**
 * XML sitemap, generated on the fly from the live database so it always
 * reflects the resources that currently exist — no manual maintenance.
 * Linked from robots.txt; submit this URL in Google Search Console too.
 */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

// Always list the public live address, never localhost or http://.
$origin = SITE_URL;
$base = '';

// Static, always-present pages.
$staticPages = [
    ['loc' => '/', 'priority' => '1.0'],
    ['loc' => '/results.php', 'priority' => '0.8'],
    ['loc' => '/notes.php', 'priority' => '0.8'],
    ['loc' => '/summaries.php', 'priority' => '0.8'],
    ['loc' => '/theory.php', 'priority' => '0.8'],
    ['loc' => '/practical.php', 'priority' => '0.8'],
    ['loc' => '/software.php', 'priority' => '0.7'],
    ['loc' => '/others.php', 'priority' => '0.6'],
    ['loc' => '/gallery.php', 'priority' => '0.6'],
    ['loc' => '/about.php', 'priority' => '0.7'],
    ['loc' => '/mission-vision.php', 'priority' => '0.6'],
    ['loc' => '/history.php', 'priority' => '0.6'],
    ['loc' => '/our-students.php', 'priority' => '0.6'],
    ['loc' => '/contact.php', 'priority' => '0.5'],
];

// National past paper pages (FTNA / CSEE subjects and CSEE practicals).
require_once __DIR__ . '/includes/national-exams.php';
foreach (national_exams() as $examKey => $examLevel) {
    $staticPages[] = ['loc' => '/theory.php?exam=' . $examKey, 'priority' => '0.7'];
    foreach (array_keys($examLevel['subjects']) as $subjectKey) {
        if (national_exam_blocks($examKey, $subjectKey, false) !== []) {
            $staticPages[] = ['loc' => '/theory.php?exam=' . $examKey . '&subject=' . $subjectKey, 'priority' => '0.6'];
        }
    }
}
foreach (array_keys(PRACTICAL_SUBJECTS) as $subjectKey) {
    if (national_exam_blocks('csee', $subjectKey, true) !== []) {
        $staticPages[] = ['loc' => '/practical.php?subject=' . $subjectKey, 'priority' => '0.6'];
    }
}

// School (non-national) exam pages, one per Form and subject.
require_once __DIR__ . '/includes/school-exams.php';
foreach (VALID_FORMS as $form) {
    foreach ([false => 'theory', true => 'practical'] as $practical => $page) {
        if ($practical && $form !== 'form4') {
            continue; // only Form 4 has practical exams
        }
        foreach (array_keys(school_exam_subjects($form, (bool) $practical)) as $subjectKey) {
            $staticPages[] = ['loc' => '/' . $page . '.php?form=' . $form . '&subject=' . $subjectKey, 'priority' => '0.6'];
        }
    }
}

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
