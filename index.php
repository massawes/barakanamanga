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


// Resource cards: [page, title, text, count, count word, link text]; the
// card photo is assets/images/cards/<key>.jpg (see includes/card-photos.php).
$resourceCards = [
    'notes'     => ['notes.php', 'Notes', 'TIE textbooks, syllabus and past papers for every subject, Form 1 to Form 4.', $counts['notes'], 'file', 'View Notes'],
    'summary'   => ['summaries.php', 'Summary', 'Quick revision summaries for Form 1 to Form 4 topics.', $counts['summary'], 'file', 'View Summaries'],
    'theory'    => ['theory.php', 'Theory Past Papers', 'Complete theory examination papers organised by Form and year.', $counts['theory'], 'file', 'View Papers'],
    'practical' => ['practical.php', 'Form 4 Practical', 'Complete Form 4 computer practical examination papers.', $counts['practical'], 'file', 'View Practicals'],
    'software'  => ['software.php', 'Software', 'Educational and programming software used in ICT lessons.', $counts['software'], 'item', 'View Software'],
    'others'    => ['others.php', 'Other Subjects', 'Resources for other subjects beyond ICT and Computer Science.', $counts['others'], 'file', 'View Other Subjects'],
    'gallery'   => ['gallery.php', 'Gallery', 'Photos and videos from life at Namanga Secondary School.', $galleryCount, 'item', 'View Gallery'],
];

// "Our Great Faculties": [photo in assets/images/staff/, name, role].
// Add a row here for each new staff photo.
$faculty = [
    ['massawe.jpg', 'Mr. Massawe', 'ICT Teacher'],
];

$pageTitle = 'Namanga Secondary School Website | Longido, Arusha, Tanzania';
$pageDescription = 'Official website of Namanga Secondary School, a government secondary school in Namanga, Longido District, Arusha, Tanzania. Free ICT & Computer Science notes, summaries, past papers, NECTA results and software for Form 1 to Form 4.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <?php // Slideshow photos fill the whole hero, behind the text. ?>
    <?php if (!empty($slides)): ?>
        <div class="hero-slideshow" aria-hidden="true">
            <?php foreach ($slides as $i => $slide): ?>
                <?php $slideUrl = $base . '/' . optimized_image($slide['image_path'], 2400); ?>
                <?php if ($i === 0): ?>
                    <div class="hero-slide active" style="background-image: url('<?= e($slideUrl) ?>');"></div>
                <?php else: ?>
                    <?php // Later slides load just before they are shown (see script.js). ?>
                    <div class="hero-slide" data-bg="<?= e($slideUrl) ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="hero-shade" aria-hidden="true"></div>

    <div class="hero-inner">
        <div class="hero-content">
            <span class="hero-badge">Welcome to</span>
            <h1 class="hero-title">
                <span class="hero-title-white">Namanga</span>
                <span class="hero-title-gold">Secondary School</span>
            </h1>
            <p class="hero-motto">&ldquo;Education for Liberty and Prosperity&rdquo;</p>
            <p class="hero-text">A center of excellence in education, nurturing knowledge, discipline and future leaders.</p>
            <div class="hero-buttons">
                <a href="#resources" class="btn btn-hero btn-hero-gold">Explore Resources <span aria-hidden="true">&rarr;</span></a>
                <a href="<?= e($base) ?>/about.php" class="btn btn-hero btn-hero-outline">About Us</a>
            </div>
        </div>

        <ul class="hero-card">
            <li>
                <span class="hero-card-icon"><?= svg_icon('school', 'icon-svg') ?></span>
                <span><strong>Quality Education</strong><small>For a Brighter Future</small></span>
            </li>
            <li>
                <span class="hero-card-icon"><?= svg_icon('students', 'icon-svg') ?></span>
                <span><strong>Supportive Community</strong><small>Students &middot; Teachers &middot; Parents</small></span>
            </li>
            <li>
                <span class="hero-card-icon"><?= svg_icon('star', 'icon-svg') ?></span>
                <span><strong>Our Goal</strong><small>Knowledge &middot; Discipline &middot; Success</small></span>
            </li>
        </ul>
    </div>
</section>

<section id="resources" class="resource-cards">
    <div class="container">
        <h2 class="section-title">Resource Categories</h2>
        <p class="section-subtitle">Notes, past papers and more for every student</p>
        <div class="photo-cards-grid">
            <?php foreach ($resourceCards as $key => [$page, $title, $text, $count, $word, $linkText]): ?>
                <a href="<?= e($base) ?>/<?= e($page) ?>" class="photo-card">
                    <span class="photo-card-img">
                        <img src="<?= e(card_photo_url($key)) ?>" alt="Students studying" loading="lazy" width="900" height="600">
                    </span>
                    <span class="photo-card-body">
                        <span class="photo-card-title"><?= e($title) ?></span>
                        <span class="photo-card-text"><?= e($text) ?></span>
                        <span class="photo-card-count"><?= (int) $count ?> <?= e($word) ?><?= $count === 1 ? '' : 's' ?></span>
                        <span class="photo-card-link"><?= e($linkText) ?> &rsaquo;</span>
                    </span>
                </a>
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
            <p>This website is the school's home online. Here students can download notes, revision summaries,
            theory and practical past papers and educational software, and find the school's
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
                <p>Free notes, summaries, past papers and NECTA results on this website</p>
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
                    <img src="<?= e($base) ?>/assets/images/staff/<?= e($photo) ?>" alt="<?= e($name . ', ' . $role) ?>" loading="lazy" width="640" height="800">
                    <h3 class="faculty-name"><?= e($name) ?></h3>
                    <p class="faculty-role"><?= e($role) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
