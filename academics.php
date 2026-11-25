<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Academics | Namanga Secondary School';
$pageDescription = 'Academics at Namanga Secondary School: O-Level Form 1 to Form 4, the subjects we teach, national examinations (FTNA and CSEE) and support for students.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Academics</h1>
        <p>O-Level education from Form 1 to Form 4 &middot; <?= school_subject_count() ?> subjects</p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <div class="school-section split">
            <div>
                <span class="eyebrow">Our programme</span>
                <h2 class="school-h2">Ordinary Level, Form 1 to Form 4</h2>
                <p>Namanga Secondary School follows Tanzania&rsquo;s national curriculum for Ordinary Level
                secondary education, prepared by the Tanzania Institute of Education (TIE). Students study for four
                years, from Form One to Form Four.</p>
                <p>Every student sits two national examinations set by the National Examinations Council of
                Tanzania (NECTA): the <strong>Form Two National Assessment (FTNA)</strong> and the
                <strong>Certificate of Secondary Education Examination (CSEE)</strong> at the end of Form Four.
                Our NECTA centre number is <strong>S2911</strong>.</p>
                <div class="btn-row">
                    <a href="<?= e(site_link('results.php')) ?>" class="btn btn-gold">Our NECTA results</a>
                    <a href="https://results.namangasecondary.sc.tz" class="btn btn-outline">Student Result System</a>
                </div>
            </div>
            <div class="split-media">
                <div class="photo-frame frame-sky">
                    <img src="<?= e($base) ?>/assets/images/school/ict-student-1.jpg" alt="A Namanga Secondary School student in the computer room" loading="lazy">
                    <span class="photo-caption">Learning in the computer room</span>
                </div>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">Subjects</span>
                <h2 class="school-h2">The subjects we teach</h2>
            </div>
            <div class="subject-groups">
                <?php foreach (SCHOOL_SUBJECTS as $group => $subjects): ?>
                    <div class="subject-group">
                        <h3><?= e($group) ?></h3>
                        <ul>
                            <?php foreach ($subjects as $subject): ?>
                                <li><?= e($subject) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">How we follow progress</span>
                <h2 class="school-h2">Assessment and reports</h2>
            </div>
            <ul class="feature-grid">
                <li class="feature-card">
                    <span class="feature-icon"><?= svg_icon('task', 'icon-svg') ?></span>
                    <h3 class="feature-title">School examinations</h3>
                    <p>Students sit midterm and terminal / annual examinations every term, set by their teachers.</p>
                </li>
                <li class="feature-card">
                    <span class="feature-icon"><?= svg_icon('results', 'icon-svg') ?></span>
                    <h3 class="feature-title">Results online</h3>
                    <p>Marks, grades, divisions and term reports are kept in our <a href="https://results.namangasecondary.sc.tz">Student Result System</a>, where parents can see their child&rsquo;s results.</p>
                </li>
                <li class="feature-card">
                    <span class="feature-icon"><?= svg_icon('star', 'icon-svg') ?></span>
                    <h3 class="feature-title">National examinations</h3>
                    <p>FTNA in Form Two and CSEE in Form Four, set and marked by NECTA.</p>
                </li>
            </ul>
        </div>

        <div class="cta-band">
            <div>
                <h2>Free study materials for our students</h2>
                <p>Notes, theory past papers and Form 4 practicals for every subject are on our Student Portal.</p>
            </div>
            <a href="<?= e(other_site_home()) ?>" class="btn btn-gold">Open the Student Portal</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
