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

// Head of School: photo in assets/images/staff/, shown with his message right under the slideshow (owner 2026-11-08).
$head = ['photo' => 'mtesigwa.jpg', 'name' => 'Mr. Mtesigwa N. Missana', 'role' => 'Head of School'];

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

<section class="head-message" aria-labelledby="head-message-title">
    <div class="container">
        <div class="head-message-box">
            <h2 id="head-message-title" class="head-message-title">MESSAGE FROM THE HEAD OF SCHOOL</h2>
            <div class="head-message-grid">
                <div class="head-message-person">
                    <?php if (is_file(__DIR__ . '/assets/images/staff/' . $head['photo'])): ?>
                        <img src="<?= e($base) ?>/assets/images/staff/<?= e($head['photo']) ?>" alt="<?= e($head['name'] . ', ' . $head['role']) ?>" width="480" height="600">
                    <?php endif; ?>
                    <strong class="head-message-name"><?= e($head['name']) ?></strong>
                    <span class="head-message-role"><?= e($head['role']) ?></span>
                </div>
                <div class="head-message-text" id="head-message-text">
                    <p>Welcome to Namanga Secondary School. On behalf of our teachers, support staff and students, I am pleased to welcome you to our website. Whether you are a student, a parent or guardian, a former student or a friend of the school, we are glad that you are here.</p>
                    <p>Founded in 2007, Namanga Secondary School is a government, co-educational O-Level school in Namanga, Longido District, Arusha Region, right on the Tanzania&ndash;Kenya border. We serve boys and girls from Namanga and the surrounding Longido communities, including many Maasai families, from Form 1 to Form 4.</p>
                    <div class="head-message-more">
                        <p>Our motto, <em>&ldquo;Education for Liberty and Prosperity&rdquo;</em>, guides everything we do. We believe that a good education frees a young person to think, to make good choices and to build a better life for themselves, their families and their community.</p>
                        <p>Our dedicated teachers follow the national curriculum and prepare our students carefully for the Form Two and Form Four national examinations set by NECTA. Beyond the classroom, we value discipline, respect, hard work, honesty and responsibility, and we encourage every student to take part in sports, clubs, cultural activities and service to the community.</p>
                        <p>We give special attention to the education of girls. In 2024 the school opened a new girls&rsquo; dormitory, built with support from the Legal Services Facility (LSF) and Luxembourg&rsquo;s North&ndash;South Cooperation, giving our girls a safe place to live and study.</p>
                        <p>This website is part of our commitment to learning. Here students can download free notes and past papers, parents can follow the life of the school and check their child&rsquo;s results through our <a href="https://namangaresults.freepage.cc" target="_blank" rel="noopener">Student Result System</a>, and everyone can learn more about Namanga Secondary School.</p>
                        <p>Parents and guardians are our most important partners. I invite you to work closely with us: follow your child&rsquo;s progress, attend school meetings and keep in touch with the class teachers. Together we can help every student reach their full potential.</p>
                        <p>To our students: use your time at Namanga well. Attend every lesson, respect your teachers and fellow students, ask questions, read widely and never give up. Your future is built by the effort you make today.</p>
                        <p>Thank you for visiting our website. You are always welcome at Namanga Secondary School.</p>
                        <div class="head-message-sign">
                            <em>EDUCATION FOR LIBERTY AND PROSPERITY</em>
                            <strong><?= e($head['name']) ?></strong>
                            <span>HEAD OF SCHOOL</span>
                        </div>
                    </div>
                    <button type="button" class="head-message-toggle" aria-expanded="false" aria-controls="head-message-text">Read More</button>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('head-message-text');
    var btn = box && box.querySelector('.head-message-toggle');
    if (!btn) { return; }
    btn.addEventListener('click', function () {
        var open = box.classList.toggle('is-open');
        btn.textContent = open ? 'Read Less' : 'Read More';
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
});
</script>

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

<?php include __DIR__ . '/includes/footer.php'; ?>
