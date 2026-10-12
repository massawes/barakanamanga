<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Mission & Vision | Namanga Secondary School';
$pageDescription = 'The mission, vision and core values of Namanga Secondary School, Longido District, Arusha, Tanzania.';
$base = base_url();
$img = $base . '/assets/images/school';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Mission &amp; Vision</h1>
        <p>What we stand for and where we are going</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php include __DIR__ . '/includes/our-school-nav.php'; ?>

        <div class="motto-band">
            <img src="<?= e($base) ?>/assets/images/logo-navy.png" alt="Namanga Secondary School crest" class="motto-logo">
            <div>
                <span class="eyebrow">Our motto</span>
                <p class="motto-text">&ldquo;Education for Liberty and Prosperity&rdquo;</p>
            </div>
        </div>

        <div class="school-section statement-grid">
            <div class="statement-card statement-mission">
                <?= svg_icon('target') ?>
                <h2>Our Mission</h2>
                <p>To provide quality, inclusive secondary education to every boy and girl of Namanga and
                Longido, in a safe and caring environment that builds knowledge, skills, discipline and
                good character.</p>
            </div>
            <div class="statement-card statement-vision">
                <?= svg_icon('eye') ?>
                <h2>Our Vision</h2>
                <p>To be a leading secondary school in Longido District, producing confident, responsible
                and skilled young people who serve their families, their community and Tanzania.</p>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">What guides us</span>
                <h2 class="school-h2">Our core values</h2>
            </div>
            <div class="values-grid values-grid-3">
                <div class="value-card">
                    <h3>Discipline</h3>
                    <p>We respect school rules, use our time well and take responsibility for our actions.</p>
                </div>
                <div class="value-card">
                    <h3>Hard Work</h3>
                    <p>We believe effort and commitment are the key to success in class and in life.</p>
                </div>
                <div class="value-card">
                    <h3>Respect</h3>
                    <p>We value every student, teacher, parent and culture in our diverse community.</p>
                </div>
                <div class="value-card">
                    <h3>Integrity</h3>
                    <p>We are honest in our work, in examinations and in the way we treat others.</p>
                </div>
                <div class="value-card">
                    <h3>Inclusion</h3>
                    <p>Every child deserves an education &mdash; especially girls and pastoralist families.</p>
                </div>
                <div class="value-card">
                    <h3>Patriotism</h3>
                    <p>We are proud to be Tanzanian and prepare our students to serve the nation.</p>
                </div>
            </div>
        </div>

        <div class="school-section split reverse">
            <div>
                <span class="eyebrow">In action</span>
                <h2 class="school-h2">Living our values together</h2>
                <p>Our mission is lived out every day &mdash; at morning assembly, in the classroom, in the
                computer room and at school events where students, teachers, parents and guests come
                together as one school community.</p>
            </div>
            <div class="split-media">
                <div class="photo-frame frame-green">
                    <img src="<?= e($img) ?>/ceremony.jpg" alt="Guests and teachers addressing students at a Namanga Secondary School event" loading="lazy">
                    <span class="photo-caption">A school event</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
