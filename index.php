<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Dashboard-style counts for the homepage (nice to have, cheap query).
$counts = ['notes' => 0, 'summary' => 0, 'theory' => 0, 'practical' => 0, 'software' => 0, 'others' => 0];
try {
    $stmt = $pdo->query("SELECT resource_type, COUNT(*) AS total FROM resources GROUP BY resource_type");
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['resource_type']] = (int) $row['total'];
    }
} catch (Exception $e) {
    // If the table doesn't exist yet (fresh install before SQL import), fail quietly.
}

$galleryCount = 0;
try {
    $galleryCount = (int) $pdo->query('SELECT COUNT(*) FROM gallery_items')->fetchColumn();
} catch (Exception $e) {
    // gallery_items table not created yet — fail quietly.
}

// Homepage slideshow images, managed from admin/manage-slides.php.
// If none have been uploaded yet, the hero falls back to a plain navy banner.
$slides = [];
try {
    $slides = $pdo->query('SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC')->fetchAll();
} catch (Exception $e) {
    // hero_slides table not created yet (older installs) — fail quietly, plain banner shows instead.
}

$pageTitle = 'Namanga Secondary School — Digital Resource Centre';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="hero <?= !empty($slides) ? 'hero-has-slides' : '' ?>">
    <div class="hero-inner">
        <div class="hero-content">
            <h1>NAMANGA SECONDARY SCHOOL WEBSITE</h1>
            <p>Made by Form 4 ICS 2026</p>
            <a href="#resources" class="btn btn-gold btn-large">Explore Resources</a>
        </div>
        <?php if (!empty($slides)): ?>
            <div class="hero-photo">
                <div class="hero-slideshow">
                    <?php foreach ($slides as $i => $slide): ?>
                        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
                             style="background-image: url('<?= e($base) ?>/<?= e($slide['image_path']) ?>');"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section id="resources" class="resource-cards">
    <div class="container">
        <h2 class="section-title">Resource Categories</h2>
        <div class="cards-grid">

            <a href="<?= e($base) ?>/notes.php" class="category-card">
                <div class="category-icon"><?= svg_icon('notes') ?></div>
                <h3>Notes</h3>
                <p>Access complete ICT and Computer Science notes for Form 1 to Form 4.</p>
                <span class="card-count"><?= $counts['notes'] ?> file<?= $counts['notes'] === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Notes</span>
            </a>

            <a href="<?= e($base) ?>/summaries.php" class="category-card">
                <div class="category-icon"><?= svg_icon('summary') ?></div>
                <h3>Summary</h3>
                <p>Quick revision summaries for Form 1 to Form 4 topics.</p>
                <span class="card-count"><?= $counts['summary'] ?> file<?= $counts['summary'] === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Summaries</span>
            </a>

            <a href="<?= e($base) ?>/theory.php" class="category-card">
                <div class="category-icon"><?= svg_icon('theory') ?></div>
                <h3>Theory Past Papers</h3>
                <p>Complete theory examination papers organised by Form and year.</p>
                <span class="card-count"><?= $counts['theory'] ?> file<?= $counts['theory'] === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Papers</span>
            </a>

            <a href="<?= e($base) ?>/practical.php" class="category-card">
                <div class="category-icon"><?= svg_icon('practical') ?></div>
                <h3>Form 4 Practical</h3>
                <p>Complete Form 4 computer practical examination papers.</p>
                <span class="card-count"><?= $counts['practical'] ?> file<?= $counts['practical'] === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Practicals</span>
            </a>

            <a href="<?= e($base) ?>/software.php" class="category-card">
                <div class="category-icon"><?= svg_icon('software') ?></div>
                <h3>Software</h3>
                <p>Educational and programming software used in ICT lessons.</p>
                <span class="card-count"><?= $counts['software'] ?> item<?= $counts['software'] === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Software</span>
            </a>

            <a href="<?= e($base) ?>/others.php" class="category-card">
                <div class="category-icon"><?= svg_icon('others') ?></div>
                <h3>Other Subjects</h3>
                <p>Resources for other subjects beyond ICT and Computer Science.</p>
                <span class="card-count"><?= $counts['others'] ?> file<?= $counts['others'] === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Other Subjects</span>
            </a>

            <a href="<?= e($base) ?>/gallery.php" class="category-card">
                <div class="category-icon"><?= svg_icon('gallery') ?></div>
                <h3>Gallery</h3>
                <p>Photos and videos from the ICT &amp; Computer Science department.</p>
                <span class="card-count"><?= $galleryCount ?> item<?= $galleryCount === 1 ? '' : 's' ?></span>
                <span class="btn btn-outline">View Gallery</span>
            </a>

        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
