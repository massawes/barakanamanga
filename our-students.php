<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Our Students | Namanga Secondary School';
$pageDescription = 'Student life at Namanga Secondary School, Longido, Arusha — academics, computer studies, assemblies, events and girls\' boarding.';
$base = base_url();
$img = $base . '/assets/images/school';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Our Students</h1>
        <p>Curious, hard-working and proud of Namanga</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php include __DIR__ . '/includes/our-school-nav.php'; ?>

        <div class="school-section split">
            <div>
                <span class="eyebrow">Who they are</span>
                <h2 class="school-h2">Boys and girls from across Longido</h2>
                <p class="lead">Our students come from Namanga town and the villages around it, including many
                Maasai families. Together they bring a rich mix of cultures, languages and dreams to the
                school.</p>
                <p>From Form 1 to Form 4 they study a full O-Level curriculum and prepare for the national
                Form Two (FTNA) and Form Four (CSEE) examinations.</p>
            </div>
            <div class="split-media">
                <div class="photo-frame">
                    <img src="<?= e($img) ?>/assembly.jpg" alt="Namanga Secondary School students at assembly" loading="lazy">
                    <span class="photo-caption">Morning assembly</span>
                </div>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">Student life</span>
                <h2 class="school-h2">Life at Namanga</h2>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <h3>Academics</h3>
                    <p>Sciences, mathematics, languages, humanities and business subjects, taught from
                    Form 1 to Form 4.</p>
                </div>
                <div class="value-card">
                    <h3>Computer Studies</h3>
                    <p>Hands-on lessons in the computer room, from basic skills to practical examinations.</p>
                </div>
                <div class="value-card">
                    <h3>Assemblies &amp; Events</h3>
                    <p>School gatherings, celebrations and visits from guests that bring the whole school together.</p>
                </div>
                <div class="value-card">
                    <h3>Girls' Boarding</h3>
                    <p>A dormitory opened in 2024 gives girls a safe place to live and focus on their studies.</p>
                </div>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">In the computer room</span>
                <h2 class="school-h2">Building digital skills</h2>
            </div>
            <div class="photo-pair">
                <div class="photo-frame tall frame-sky">
                    <img src="<?= e($img) ?>/ict-student-1.jpg" alt="A student studying with a laptop and notebook in the computer room" loading="lazy">
                </div>
                <div class="photo-frame tall frame-green">
                    <img src="<?= e($img) ?>/ict-student-2.jpg" alt="A student working on a laptop during an ICT lesson" loading="lazy">
                </div>
            </div>
        </div>

        <div class="cta-band">
            <div>
                <h2>Study resources for every student</h2>
                <p>Free notes, summaries and past papers for Form 1 to Form 4.</p>
            </div>
            <a href="<?= e($base) ?>/index.php#resources" class="btn btn-primary btn-large">Start learning</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
