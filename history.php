<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Our History | Namanga Secondary School';
$pageDescription = 'The history of Namanga Secondary School in Namanga ward, Longido District, Arusha, Tanzania.';
$base = base_url();
$img = $base . '/assets/images/school';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Our History</h1>
        <p>Growing together with the Namanga community</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php include __DIR__ . '/includes/our-school-nav.php'; ?>

        <div class="school-section split">
            <div>
                <span class="eyebrow">Our story</span>
                <h2 class="school-h2">A school for a border community</h2>
                <p class="lead">Namanga is a busy town on the main road between Arusha and Nairobi, right on
                the border between Tanzania and Kenya. Namanga Secondary School was established as a
                government school so that the children of Namanga ward and its surrounding villages could
                get a secondary education close to home.</p>
                <p>Over the years the school has grown to serve a large and diverse student body, including
                many children from Maasai pastoralist families across Longido District.</p>
            </div>
            <div class="split-media">
                <div class="photo-frame">
                    <img src="<?= e($img) ?>/students-red.jpg" alt="Namanga Secondary School students at an outdoor school event" loading="lazy">
                </div>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">Milestones</span>
                <h2 class="school-h2">Our journey</h2>
            </div>
            <div class="container narrow" style="padding:0">
                <div class="timeline">
                    <div class="timeline-item">
                        <span class="timeline-year">Beginnings</span>
                        <h3>A secondary school for Namanga</h3>
                        <p>Namanga Secondary School opens as a government school, giving the children of
                        Namanga ward a secondary school close to home.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-year">2007</span>
                        <h3>The new Longido District</h3>
                        <p>Longido District is created from part of Monduli District, and Namanga ward
                        becomes part of the new district.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-year">2024</span>
                        <h3>A new girls' dormitory</h3>
                        <p>The school inaugurates a new dormitory for girls, built with support from the Legal
                        Services Facility (LSF) and Luxembourg's North-South Cooperation, giving girls a safe
                        place to live and study.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-year">2026</span>
                        <h3>Our school goes online</h3>
                        <p>The school launches its official website, giving students free access to notes,
                        summaries, past papers, software and NECTA results.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-band">
            <div>
                <h2>See our school in pictures</h2>
                <p>Photos and videos from life at Namanga Secondary School.</p>
            </div>
            <a href="<?= e($base) ?>/gallery.php" class="btn btn-primary btn-large">Visit the gallery</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
