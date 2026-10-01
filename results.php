<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Official NECTA results pages for Namanga Secondary School (S2911).
// Only links verified to open the school's page for that year belong here;
// years missing from a list are shown as "not available".
$resultsLinks = [
    'form4' => [
        ['year' => 2025, 'url' => 'https://onlinesys.necta.go.tz/results/2025/csee/results/s2911.htm'],
        ['year' => 2024, 'url' => 'https://onlinesys.necta.go.tz/results/2024/csee/results/s2911.htm'],
        ['year' => 2023, 'url' => 'https://onlinesys.necta.go.tz/results/2023/csee/results/s2911.htm'],
        ['year' => 2022, 'url' => 'https://onlinesys.necta.go.tz/results/2022/csee/results/s2911.htm'],
    ],
    'form2' => [
        ['year' => 2025, 'url' => 'https://onlinesys.necta.go.tz/results/2025/ftna/results/S2911.htm'],
        ['year' => 2024, 'url' => 'https://onlinesys.necta.go.tz/results/2024/ftna/results/S2911.htm'],
        ['year' => 2023, 'url' => 'https://onlinesys.necta.go.tz/results/2023/ftna/results/S2911.htm'],
        ['year' => 2022, 'url' => 'https://onlinesys.necta.go.tz/results/2022/ftna/results/S2911.htm'],
    ],
];

// Years listed on the page, newest first.
const RESULTS_YEARS = [2025, 2024, 2023, 2022, 2021, 2020];

$exams = [
    'form4' => ['label' => 'Form 4', 'exam' => 'CSEE', 'name' => 'Certificate of Secondary Education Examination'],
    'form2' => ['label' => 'Form 2', 'exam' => 'FTNA', 'name' => 'Form Two National Assessment'],
];

$selected = $_GET['form'] ?? '';
$examSelected = isset($exams[$selected]);

$base = base_url();

if ($examSelected) {
    $exam = $exams[$selected];
    $linksByYear = array_column($resultsLinks[$selected], 'url', 'year');
    $pageTitle = $exam['label'] . ' Results | Namanga Secondary School';
} else {
    $pageTitle = 'Results | Namanga Secondary School';
    $pageDescription = 'Namanga Secondary School (S2911) NECTA results — Form 4 CSEE and Form 2 FTNA results by year, with direct links to the official NECTA pages.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Results</h1>
        <p><?= $examSelected ? e($exam['label'] . ' (' . $exam['exam'] . ') — official NECTA results for Namanga Secondary School.') : 'Choose a Form to see official NECTA results.' ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if (!$examSelected): ?>

            <div class="cards-grid">
                <?php foreach ($exams as $key => $info): ?>
                    <?php $count = count($resultsLinks[$key]); ?>
                    <a href="<?= e($base) ?>/results.php?form=<?= e($key) ?>" class="category-card form-select-card">
                        <div class="category-icon"><?= svg_icon('theory') ?></div>
                        <h3 class="form-big-label"><?= e(strtoupper($info['label'])) ?></h3>
                        <p><?= e($info['exam']) ?> — <?= e($info['name']) ?>.</p>
                        <span class="card-count"><?= $count ?> year<?= $count === 1 ? '' : 's' ?></span>
                        <span class="btn btn-outline">View <?= e($info['label']) ?> Results</span>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <p><a href="<?= e($base) ?>/results.php" class="back-link">&larr; Back to Form selection</a></p>
            <div class="section-title-row">
                <h2 class="section-title-left"><?= e($exam['label']) ?> Results (<?= e($exam['exam']) ?>)</h2>
            </div>

            <div class="resource-link-list">
                <?php foreach (RESULTS_YEARS as $year): ?>
                    <?php if (isset($linksByYear[$year])): ?>
                        <a class="resource-link-item" href="<?= e($linksByYear[$year]) ?>" target="_blank" rel="noopener">
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
                                    <span class="file-size">Not available on the NECTA website</span>
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
