<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Admission announcements (joining instructions etc.) are written in admin/manage-announcements.php
// with the type "Admission"; they appear here automatically.
$admissionNotices = [];
try {
    $admissionNotices = $pdo->query("SELECT * FROM announcements WHERE is_published = 1 AND category = 'admission' ORDER BY published_on DESC, id DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) {
    // Table not ready yet.
}

$pageTitle = 'Admissions | Namanga Secondary School';
$pageDescription = 'Joining Namanga Secondary School: Form One selection, joining instructions, reporting and transfers.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Admissions</h1>
        <p>Joining Namanga Secondary School</p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if ($admissionNotices): ?>
            <div class="school-section">
                <span class="eyebrow">Latest</span>
                <h2 class="school-h2">Admission notices &amp; joining instructions</h2>
                <div class="news-list">
                    <?php foreach ($admissionNotices as $item): ?>
                        <a class="news-row" href="<?= e(site_link('announcements.php?id=' . (int) $item['id'])) ?>">
                            <time datetime="<?= e($item['published_on']) ?>"><?= e(date('d M Y', strtotime($item['published_on']))) ?></time>
                            <span class="news-row-title"><?= e($item['title']) ?></span>
                            <?php if ($item['attachment_path']): ?><span class="news-row-file">Download</span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="school-section split">
            <div>
                <span class="eyebrow">Form One</span>
                <h2 class="school-h2">How students join Form One</h2>
                <p>Namanga Secondary School is a government school. Pupils who pass the Primary School Leaving
                Examination (PSLE) are selected to join Form One by the President&rsquo;s Office &ndash; Regional
                Administration and Local Government (<strong>PO-RALG / TAMISEMI</strong>).</p>
                <p>When the selection is announced, parents and guardians can check whether their child has been
                selected to Namanga Secondary School on the TAMISEMI website.</p>
                <div class="btn-row">
                    <a href="https://www.tamisemi.go.tz" class="btn btn-gold" target="_blank" rel="noopener">TAMISEMI website</a>
                </div>
            </div>
            <div class="split-media">
                <div class="photo-frame">
                    <img src="<?= e($base) ?>/assets/images/school/students-red.jpg" alt="Namanga Secondary School students" loading="lazy">
                    <span class="photo-caption">Our students</span>
                </div>
            </div>
        </div>

        <div class="school-section">
            <div style="text-align:center">
                <span class="eyebrow">Step by step</span>
                <h2 class="school-h2">After your child is selected</h2>
            </div>
            <ol class="steps">
                <li><strong>Get the joining instructions.</strong> They are published on this website under
                    <a href="<?= e(site_link('announcements.php')) ?>">News &amp; Announcements</a> and are available at the school office.</li>
                <li><strong>Prepare the requirements</strong> listed in the joining instructions (uniform, learning materials and other items).</li>
                <li><strong>Report to school</strong> on the reporting date with the documents asked for in the joining instructions.</li>
                <li><strong>Registration.</strong> The school registers every new student, and parents receive information on following their child&rsquo;s progress.</li>
            </ol>
        </div>

        <div class="school-section split reverse">
            <div>
                <span class="eyebrow">Transfers &amp; enquiries</span>
                <h2 class="school-h2">Questions about joining?</h2>
                <p>For transfers from another school, or any question about admission, please contact the school
                office. Transfers between government schools follow the procedures of the Ministry and TAMISEMI.</p>
                <p><strong>Phone:</strong> <a href="tel:+255755958326">+255 755 958 326</a><br>
                <strong>Email:</strong> <a href="mailto:namanga@longido.dc.go.tz">namanga@longido.dc.go.tz</a><br>
                <strong>Address:</strong> P.O. Box 6560, Namanga, Arusha &ndash; Tanzania</p>
                <a href="<?= e(site_link('contact.php')) ?>" class="btn btn-outline">Contact the school</a>
            </div>
            <div class="split-media">
                <div class="photo-frame frame-green">
                    <img src="<?= e($base) ?>/assets/images/school/ceremony.jpg" alt="A ceremony at Namanga Secondary School" loading="lazy">
                    <span class="photo-caption">A school ceremony</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
