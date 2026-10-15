<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'About Us | Namanga Secondary School, Longido, Arusha';
$pageDescription = 'About Namanga Secondary School — a government co-educational secondary school in Namanga ward, Longido District, Arusha, on the Tanzania–Kenya border.';
$base = base_url();
$img = $base . '/assets/images/school';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>About Us</h1>
        <p>Namanga Secondary School &mdash; Longido District, Arusha, Tanzania</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php include __DIR__ . '/includes/our-school-nav.php'; ?>

        <div class="school-section split">
            <div>
                <img src="<?= e($base) ?>/assets/images/logo-navy.png" alt="Namanga Secondary School crest" class="about-crest">
                <span class="eyebrow">Welcome</span>
                <h2 class="school-h2">A school at the gateway of Tanzania</h2>
                <p class="lead"><strong>Namanga Secondary School</strong> is a government, co-educational
                secondary school in Namanga ward, Longido District, Arusha Region. Our school sits in the
                border town of Namanga, right where Tanzania meets Kenya.</p>
                <p>We serve boys and girls from Namanga and the surrounding Longido communities,
                including many Maasai families, and offer O-Level education from Form 1 to Form 4,
                preparing students for the national FTNA (Form Two) and CSEE (Form Four) examinations.</p>
                <p>Our motto, <strong>&ldquo;Education for Liberty and Prosperity&rdquo;</strong>, reflects our
                belief that a good education opens the door to a free and prosperous future.</p>
            </div>
            <div class="split-media">
                <div class="photo-frame">
                    <img src="<?= e($img) ?>/assembly.jpg" alt="Namanga Secondary School students gathered at a school assembly" loading="lazy">
                    <span class="photo-caption">Students at a school assembly</span>
                </div>
            </div>
        </div>

        <div class="facts-band">
            <div class="fact"><span class="fact-value">Form 1&ndash;4</span><span class="fact-label">O-Level secondary education</span></div>
            <div class="fact"><span class="fact-value">Boys &amp; Girls</span><span class="fact-label">Co-educational government school</span></div>
            <div class="fact"><span class="fact-value">Longido</span><span class="fact-label">District, Arusha Region</span></div>
            <div class="fact"><span class="fact-value">2024</span><span class="fact-label">New girls' dormitory opened</span></div>
        </div>

        <div class="school-section split reverse">
            <div>
                <span class="eyebrow">Learning</span>
                <h2 class="school-h2">Learning for today's world</h2>
                <p>Alongside the core subjects, our students learn Information &amp; Computer Studies in the
                school computer room, gaining practical digital skills they will use for life.</p>
                <p>This website is part of that effort. It is the school's home online, where students
                can download notes, revision summaries, past papers and educational software for free,
                and find the school's NECTA results.</p>
                <a href="<?= e($base) ?>/index.php#resources" class="btn btn-gold">Explore resources</a>
            </div>
            <div class="split-media">
                <div class="photo-frame frame-sky">
                    <img src="<?= e($img) ?>/ict-student-2.jpg" alt="A Namanga Secondary School student working on a laptop in the computer room" loading="lazy">
                    <span class="photo-caption">In the computer room</span>
                </div>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">Discover more</span>
                <h2 class="school-h2">Get to know our school</h2>
            </div>
            <div class="photo-cards-grid">
                <?= render_photo_card($base . '/mission-vision.php', 'about-mission', 'Mission & Vision', 'What we work for every day, and the values that guide us.', null, 'Read more') ?>
                <?= render_photo_card($base . '/history.php', 'about-history', 'Our History', 'How our school has grown with the Namanga community.', null, 'Read more') ?>
                <?= render_photo_card($base . '/our-students.php', 'about-students', 'Our Students', 'Student life, learning and activities at Namanga.', null, 'Read more') ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
