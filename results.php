<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Official NECTA results pages for Namanga Secondary School (S2911).
// The list of years updates itself: cron/necta-check.php asks NECTA every 3 hours and adds
// a new year as soon as it is published (see includes/necta-results.php). No file to edit.
$state = necta_state();
$years = necta_years_to_show($state);
$thisYear = (int) date('Y');
$exams = NECTA_EXAMS;

$selected = $_GET['form'] ?? '';
$examSelected = isset($exams[$selected]);

$base = base_url();

if ($examSelected) {
    $exam = $exams[$selected];
    $pageTitle = $exam['label'] . ' (' . $exam['exam'] . ') Results | Namanga Secondary School';
    $pageDescription = 'Namanga Secondary School (S2911) ' . $exam['exam'] . ' results by year, with direct links to the official NECTA pages.';
} else {
    $pageTitle = 'NECTA Results | Namanga Secondary School';
    $pageDescription = 'Namanga Secondary School (S2911) NECTA results: Form 4 CSEE and Form 2 FTNA results by year, with direct links to the official NECTA pages.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>NECTA Results</h1>
        <p><?= $examSelected ? e($exam['label'] . ' (' . $exam['exam'] . ') — official NECTA results for Namanga Secondary School.') : 'Choose a Form to see official NECTA results.' ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if (!$examSelected): ?>

            <div class="photo-cards-grid">
                <?php foreach ($exams as $key => $info): ?>
                    <?= render_photo_card($base . '/results.php?form=' . $key, 'results-' . $key, $info['label'],
                        $info['exam'] . ' — ' . $info['name'] . '.', count_label(count($state[$key]), 'year'), 'View ' . $info['label'] . ' Results') ?>
                <?php endforeach; ?>
            </div>
            <p class="field-hint" style="text-align:center;margin-top:18px;">New results are added here automatically as soon as NECTA publishes them.
                For term and school examination results, use the <a href="https://results.namangasecondary.sc.tz">Student Result System</a>.</p>

        <?php else: ?>

            <p><a href="<?= e($base) ?>/results.php" class="back-link">&larr; Back to Form selection</a></p>
            <div class="section-title-row">
                <h2 class="section-title-left"><?= e($exam['label']) ?> Results (<?= e($exam['exam']) ?>)</h2>
            </div>

            <div class="resource-link-list">
                <?php foreach ($years as $year): ?>
                    <?php if (in_array($year, $state[$selected], true)): ?>
                        <a class="resource-link-item" href="<?= e(necta_result_url($selected, $year)) ?>" target="_blank" rel="noopener">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($exam['exam']) ?> <?= $year ?> Results</span>
                                <span class="resource-link-meta">
                                    <span class="file-size">Opens on the NECTA website</span>
                                </span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php else: ?>
                        <div class="resource-link-item">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($exam['exam']) ?> <?= $year ?> Results</span>
                                <span class="resource-link-meta">
                                    <span class="file-size"><?= $year >= $thisYear - 1
                                        ? 'Not out yet — will appear here automatically when NECTA publishes them'
                                        : 'Not available on the NECTA website' ?></span>
                                </span>
                            </span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
