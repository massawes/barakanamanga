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
// If none have been uploaded yet, the slider shows the school photos.
$slides = [];
try {
    $slides = $pdo->query('SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC')->fetchAll();
} catch (Exception $e) {
    // hero_slides table not created yet (older installs) — fail quietly, school photos show instead.
}


// Resource cards: [page, title, text, count, count word, link text]; the
// card drawing comes from the key (see includes/card-photos.php).
$resourceCards = [
    'notes'     => ['notes.php', 'Notes', 'TIE textbooks, syllabus and past papers for every subject, Form 1 to Form 4.', $counts['notes'], 'file', 'View Notes'],
    'theory'    => ['theory.php', 'Theory Past Papers', 'Complete theory examination papers organised by Form and year.', $counts['theory'], 'file', 'View Papers'],
    'practical' => ['practical.php', 'Form 4 Practical', 'Complete Form 4 computer practical examination papers.', $counts['practical'], 'file', 'View Practicals'],
    'gallery'   => ['gallery.php', 'Gallery', 'Photos and videos from life at Namanga Secondary School.', $galleryCount, 'item', 'View Gallery'],
];

// "Our Great Faculties": [photo in assets/images/staff/, name, role].
// Add a row here for each new staff photo.
$faculty = [
    // Head of School. Photo: save it as assets/images/staff/mtesigwa.jpg (until then his initials show).
    ['mtesigwa.jpg', 'Mr. Mtesigwa N. Missana', 'Head of School'],
];

$pageTitle = 'Namanga Secondary School Website | Longido, Arusha, Tanzania';
$pageDescription = 'Official website of Namanga Secondary School, a government secondary school in Namanga, Longido District, Arusha, Tanzania. Free ICT & Computer Science notes, past papers and NECTA results for Form 1 to Form 4.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<?php // Slideshow of photos only (owner 2026-11-08): arrows, dots, changes by itself every 5 seconds. ?>
<?php
$slideUrls = [];
foreach ($slides as $slide) {
    $slideUrls[] = $base . '/' . optimized_image($slide['image_path'], 2400);
}
if (!$slideUrls) {
    // No slides uploaded yet: show the school photos instead.
    foreach (['assembly.jpg', 'ceremony.jpg', 'students-red.jpg'] as $photo) {
        $slideUrls[] = $base . '/assets/images/school/' . $photo;
    }
}
?>
<section class="slider" aria-label="School photos" aria-roledescription="carousel">
    <h1 class="visually-hidden">Namanga Secondary School</h1>
    <div class="slider-track">
        <?php foreach ($slideUrls as $i => $slideUrl): ?>
            <?php if ($i === 0): ?>
                <div class="slider-slide active" style="background-image: url('<?= e($slideUrl) ?>');" role="group" aria-label="Photo 1 of <?= count($slideUrls) ?>"></div>
            <?php else: ?>
                <?php // Later photos load just before they are shown (see script.js). ?>
                <div class="slider-slide" data-bg="<?= e($slideUrl) ?>" role="group" aria-label="Photo <?= $i + 1 ?> of <?= count($slideUrls) ?>"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php if (count($slideUrls) > 1): ?>
        <button type="button" class="slider-btn slider-prev" aria-label="Previous photo">&#10094;</button>
        <button type="button" class="slider-btn slider-next" aria-label="Next photo">&#10095;</button>
        <div class="slider-dots">
            <?php foreach ($slideUrls as $i => $slideUrl): ?>
                <button type="button" class="slider-dot<?= $i === 0 ? ' active' : '' ?>" aria-label="Show photo <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section id="resources" class="resource-cards">
    <div class="container">
        <h2 class="section-title">Resource Categories</h2>
        <p class="section-subtitle">Notes, past papers and more for every student</p>
        <div class="photo-cards-grid">
            <?php foreach ($resourceCards as $key => [$page, $title, $text, $count, $word, $linkText]): ?>
                <?= render_photo_card($base . '/' . $page, $key, $title, $text, count_label((int) $count, $word), $linkText) ?>
            <?php endforeach; ?>
        </div>
        <?= render_card_photo_credits(array_keys($resourceCards)) ?>
    </div>
</section>

<section class="home-intro">
    <div class="container">
        <h2 class="section-title">Who are we?</h2>
        <div class="intro-card">
            <p><strong>Namanga Secondary School</strong> is a government, co-educational secondary school in
            Namanga ward, Longido District, Arusha Region, Tanzania, right on the Tanzania&ndash;Kenya border.
            The school serves boys and girls from Namanga and the surrounding Longido communities, including
            many Maasai families, offering O-Level education from Form 1 to Form 4.</p>
            <p>In 2024 the school opened a new girls' dormitory, built with support from the Legal Services
            Facility (LSF) and Luxembourg's North-South Cooperation, giving girls a safe place to live and study.</p>
            <p>This website is the school's home online. Here students can download notes,
            theory and practical past papers, and find the school's
            <a href="<?= e($base) ?>/results.php">NECTA Form 2 and Form 4 results</a>.</p>
        </div>
        <ul class="feature-grid">
            <li class="feature-card">
                <span class="feature-icon"><?= svg_icon('school', 'icon-svg') ?></span>
                <h3 class="feature-title">Government School</h3>
                <p>Government school for boys and girls, Form 1 to Form 4</p>
            </li>
            <li class="feature-card">
                <span class="feature-icon"><?= svg_icon('pin', 'icon-svg') ?></span>
                <h3 class="feature-title">Our Location</h3>
                <p>Located in Namanga, Longido District, on the Tanzania&ndash;Kenya border</p>
            </li>
            <li class="feature-card">
                <span class="feature-icon"><?= svg_icon('notes', 'icon-svg') ?></span>
                <h3 class="feature-title">Free Resources</h3>
                <p>Free notes, past papers and NECTA results on this website</p>
            </li>
        </ul>
    </div>
</section>

<section class="faculty-section">
    <div class="container">
        <h2 class="section-title">Our Great Faculties</h2>
        <p class="section-subtitle">Well experienced staff</p>
        <div class="faculty-grid">
            <?php foreach ($faculty as [$photo, $name, $role]): ?>
                <div class="faculty-card">
                    <?php if (is_file(__DIR__ . '/assets/images/staff/' . $photo)): ?>
                        <img src="<?= e($base) ?>/assets/images/staff/<?= e($photo) ?>" alt="<?= e($name . ', ' . $role) ?>" loading="lazy" width="640" height="800">
                    <?php else: ?>
                        <?php // No photo yet: show a coloured box with the initials (Mtesigwa Missana = MM). ?>
                        <div class="faculty-initials" aria-hidden="true"><?= e(implode('', array_map(fn ($w) => $w[0], array_slice(array_values(array_filter(explode(' ', preg_replace('/^(Mr|Mrs|Ms|Dr)\.\s*/', '', $name)), fn ($w) => strlen($w) > 2)), 0, 2)))) ?></div>
                    <?php endif; ?>
                    <h3 class="faculty-name"><?= e($name) ?></h3>
                    <p class="faculty-role"><?= e($role) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
