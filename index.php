<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Two websites share this file (see includes/site.php): the Student Portal has its own home page.
if (is_portal()) {
    require __DIR__ . '/includes/portal-home.php';
    return;
}

// ---------------- School website home page ----------------

// Homepage slideshow images, managed from admin/manage-slides.php.
// If none have been uploaded yet, the slider shows the school photos.
$slides = [];
try {
    $slides = $pdo->query('SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC')->fetchAll();
} catch (Exception $e) {
    // hero_slides table not created yet (older installs) — fail quietly, school photos show instead.
}

// News & Announcements block at the bottom of the home page (like atc.ac.tz), from admin/manage-announcements.php:
// left = Latest News (types News / Event, with a photo), right = Announcements (types Announcement / Admission).
$latestNews = [];
$latestNotices = [];
try {
    $latestNews = $pdo->query("SELECT * FROM announcements WHERE is_published = 1 AND category IN ('news','event') ORDER BY published_on DESC, id DESC LIMIT 3")->fetchAll();
    $latestNotices = $pdo->query("SELECT * FROM announcements WHERE is_published = 1 AND category IN ('announcement','admission') ORDER BY published_on DESC, id DESC LIMIT 4")->fetchAll();
} catch (Throwable $e) {
    // Table not ready yet.
}
// Photo of a news card: its image attachment, otherwise one of the school photos.
$newsPhoto = static function (array $item, int $i) use (&$base): string {
    $path = (string) ($item['attachment_path'] ?? '');
    if ($path !== '' && preg_match('/\.(jpe?g|png|webp)$/i', $path)) {
        return $base . '/' . $path;
    }
    $fallback = ['students-red.jpg', 'assembly.jpg', 'ceremony.jpg'];
    return $base . '/assets/images/school/' . $fallback[$i % count($fallback)];
};
// Always-current notice: the newest NECTA results the site knows about (updated automatically, see includes/necta-results.php).
$nectaState = necta_state();
$nectaLatest = [];
foreach (NECTA_EXAMS as $form => $exam) {
    if ($state = $nectaState[$form]) {
        $nectaLatest[] = $exam['exam'] . ' ' . max($state);
    }
}

// Latest photos from the Gallery.
$latestPhotos = [];
try {
    $latestPhotos = $pdo->query("SELECT id, title, event_name, file_path FROM gallery_items WHERE media_type = 'image' ORDER BY COALESCE(event_date, created_at) DESC, id DESC LIMIT 6")->fetchAll();
} catch (Exception $e) {
    // Gallery not ready yet.
}

// Head of School: photo in assets/images/staff/, shown with his message right under the slideshow (owner 2026-11-08).
$head = ['photo' => 'mtesigwa.jpg', 'name' => 'Mr. Mtesigwa N. Missana', 'role' => 'Head of School'];

// Quick links for parents, students and visitors.
$quickLinks = [
    ['https://results.namangasecondary.sc.tz', 'results', 'Student Results', 'Term and examination results for parents and students. results.namangasecondary.sc.tz'],
    [site_link('admissions.php'), 'students', 'Admissions', 'Joining Form One: selection, reporting and requirements.'],
    [other_site_home(), 'notes', 'Student Portal', 'Free notes, past papers and practicals. masomo.namangasecondary.sc.tz'],
    [site_link('results.php'), 'star', 'NECTA Results', 'Our Form Two (FTNA) and Form Four (CSEE) results.'],
];

$pageTitle = 'Namanga Secondary School | Longido, Arusha, Tanzania';
$pageDescription = 'Official website of Namanga Secondary School (NECTA S2911), a government co-educational secondary school in Namanga, Longido District, Arusha, Tanzania. News, admissions, academics and results.';
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
                    <p>Namanga Secondary School is a government, co-educational O-Level school in Namanga, Longido District, Arusha Region, right on the Tanzania&ndash;Kenya border. We serve boys and girls from Namanga and the surrounding Longido communities, including many Maasai families, from Form 1 to Form 4.</p>
                    <div class="head-message-more">
                        <p>Our motto, <em>&ldquo;Education for Liberty and Prosperity&rdquo;</em>, guides everything we do. We believe that a good education frees a young person to think, to make good choices and to build a better life for themselves, their families and their community.</p>
                        <p>Our dedicated teachers follow the national curriculum and prepare our students carefully for the Form Two and Form Four national examinations set by NECTA. Beyond the classroom, we value discipline, respect, hard work, honesty and responsibility, and we encourage every student to take part in sports, clubs, cultural activities and service to the community.</p>
                        <p>We give special attention to the education of girls. In 2024 the school opened a new girls&rsquo; dormitory, built with support from the Legal Services Facility (LSF) and Luxembourg&rsquo;s North&ndash;South Cooperation, giving our girls a safe place to live and study.</p>
                        <p>This website is part of our commitment to learning. Parents can follow the life of the school and check their child&rsquo;s results through our <a href="https://results.namangasecondary.sc.tz" target="_blank" rel="noopener">Student Result System</a>, students can download free notes and past papers from our <a href="<?= e(other_site_home()) ?>">Student Portal</a>, and everyone can learn more about Namanga Secondary School.</p>
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

<section class="quick-links" aria-label="Quick links">
    <div class="container">
        <div class="quick-grid">
            <?php foreach ($quickLinks as [$href, $icon, $title, $text]): ?>
                <a class="quick-tile" href="<?= e($href) ?>">
                    <span class="quick-icon"><?= svg_icon($icon, 'icon-svg') ?></span>
                    <span class="quick-title"><?= e($title) ?></span>
                    <span class="quick-text"><?= e($text) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="school-glance" aria-label="The school at a glance">
    <div class="container">
        <div class="facts-band">
            <div class="fact"><span class="fact-value">Longido</span><span class="fact-label">District, Arusha Region</span></div>
            <div class="fact"><span class="fact-value">S2911</span><span class="fact-label">NECTA centre number</span></div>
            <div class="fact"><span class="fact-value">Form 1&ndash;4</span><span class="fact-label">O-Level, boys &amp; girls</span></div>
            <div class="fact"><span class="fact-value"><?= school_subject_count() ?></span><span class="fact-label">Subjects taught</span></div>
        </div>
    </div>
</section>


<section class="home-about">
    <div class="container">
        <div class="school-section split">
            <div>
                <span class="eyebrow">About the school</span>
                <h2 class="school-h2">A school at the gateway of Tanzania</h2>
                <p><strong>Namanga Secondary School</strong> is a government, co-educational secondary school in
                Namanga ward, Longido District, Arusha Region, right on the Tanzania&ndash;Kenya border. We serve
                boys and girls from Namanga and the surrounding Longido communities, including many Maasai families,
                offering O-Level education from Form 1 to Form 4.</p>
                <p>In 2024 the school opened a new girls&rsquo; dormitory, built with support from the Legal Services
                Facility (LSF) and Luxembourg&rsquo;s North&ndash;South Cooperation, giving girls a safe place to live and study.</p>
                <div class="btn-row">
                    <a href="<?= e(site_link('about.php')) ?>" class="btn btn-gold">More about us</a>
                    <a href="<?= e(site_link('academics.php')) ?>" class="btn btn-outline">Academics</a>
                </div>
            </div>
            <div class="split-media">
                <div class="photo-frame">
                    <img src="<?= e($base) ?>/assets/images/school/assembly.jpg" alt="Namanga Secondary School students at a school assembly" loading="lazy">
                    <span class="photo-caption">Students at a school assembly</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($latestPhotos): ?>
<section class="home-photos" aria-labelledby="home-photos-title">
    <div class="container">
        <h2 class="section-title" id="home-photos-title">Life at Namanga</h2>
        <p class="section-subtitle">Recent photos from the school</p>
        <div class="home-photo-grid">
            <?php foreach ($latestPhotos as $photo): ?>
                <a href="<?= e(site_link('gallery.php?type=image')) ?>" class="home-photo">
                    <img src="<?= e($base) ?>/<?= e(optimized_image($photo['file_path'], 600)) ?>" alt="<?= e($photo['event_name'] ?: $photo['title']) ?>" loading="lazy">
                </a>
            <?php endforeach; ?>
        </div>
        <p class="center-link"><a class="btn btn-outline" href="<?= e(site_link('gallery.php?type=image')) ?>">Open the gallery</a></p>
    </div>
</section>
<?php endif; ?>

<section class="home-location" aria-labelledby="home-location-title">
    <div class="container">
        <h2 class="section-title" id="home-location-title">Find Us</h2>
        <div class="location-grid">
            <div class="location-info">
                <h3>Namanga Secondary School</h3>
                <p>P.O. Box 6560, Namanga<br>Longido District, Arusha Region, Tanzania</p>
                <p><strong>Phone:</strong> <a href="tel:+255695389537">+255 695 389 537</a><br>
                <strong>Email:</strong> <a href="mailto:namanga@longido.dc.go.tz">namanga@longido.dc.go.tz</a></p>
                <a class="btn btn-gold" href="https://www.google.com/maps/dir/?api=1&amp;destination=Namanga+Secondary+School%2C+Namanga%2C+Tanzania" target="_blank" rel="noopener">Get directions</a>
            </div>
            <div class="map-frame">
                <iframe title="Map: Namanga Secondary School" src="https://www.google.com/maps?q=Namanga+Secondary+School,+Namanga,+Tanzania&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>

<section class="home-newsroom" aria-labelledby="home-newsroom-title">
    <div class="container">
        <h2 class="section-title" id="home-newsroom-title">News &amp; Announcements</h2>
        <div class="newsroom-grid">
            <div class="newsroom-news">
                <h3 class="newsroom-head">Latest News</h3>
                <?php if ($latestNews): ?>
                    <div class="newsroom-cards">
                        <?php foreach ($latestNews as $i => $item): ?>
                            <?php $link = site_link('announcements.php?id=' . (int) $item['id']); ?>
                            <article class="newsroom-card">
                                <a href="<?= e($link) ?>" class="newsroom-card-img"><img src="<?= e($newsPhoto($item, $i)) ?>" alt="" loading="lazy"></a>
                                <div class="newsroom-card-body">
                                    <time datetime="<?= e($item['published_on']) ?>"><?= e(date('d M Y', strtotime($item['published_on']))) ?></time>
                                    <h4><a href="<?= e($link) ?>"><?= e($item['title']) ?></a></h4>
                                    <p><?= e(announcement_excerpt($item['body'], 110)) ?></p>
                                    <a class="news-more" href="<?= e($link) ?>">Read more</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="newsroom-empty">School news will appear here.</p>
                <?php endif; ?>
                <a class="btn btn-outline newsroom-all" href="<?= e(site_link('announcements.php?type=news')) ?>">More news</a>
            </div>

            <aside class="newsroom-notices" aria-label="Announcements">
                <h3 class="newsroom-head">Announcements</h3>
                <ul class="notice-list">
                    <?php if ($nectaLatest): ?>
                        <li>
                            <a href="<?= e(site_link('results.php')) ?>">
                                <span class="notice-date notice-date-icon"><?= svg_icon('results', 'icon-svg') ?></span>
                                <span class="notice-text"><strong>NECTA results</strong> <?= e(implode(' and ', $nectaLatest)) ?> are available. New results appear here automatically.</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php foreach ($latestNotices as $item): ?>
                        <li>
                            <a href="<?= e(site_link('announcements.php?id=' . (int) $item['id'])) ?>">
                                <span class="notice-date"><b><?= e(date('d', strtotime($item['published_on']))) ?></b><?= e(strtoupper(date('M', strtotime($item['published_on'])))) ?></span>
                                <span class="notice-text"><?= e($item['title']) ?><?= $item['attachment_path'] ? ' <em>(download)</em>' : '' ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="https://results.namangasecondary.sc.tz">
                            <span class="notice-date notice-date-icon"><?= svg_icon('students', 'icon-svg') ?></span>
                            <span class="notice-text"><strong>Parents:</strong> check your child&rsquo;s term results in the Student Result System.</span>
                        </a>
                    </li>
                </ul>
                <a class="btn btn-outline newsroom-all" href="<?= e(site_link('announcements.php')) ?>">All announcements</a>
            </aside>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
