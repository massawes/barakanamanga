<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/national-exams.php';
require_once __DIR__ . '/includes/school-exams.php';

$base = base_url();

// Views: ?subject=… shows that subject's NECTA (CSEE) practical papers,
// ?form=…&subject=… a subject's school (non-national) practical exam PDFs,
// ?view=internal the practical papers uploaded from the admin panel
// (Internal Exams), and the landing page lists all of them.
$selectedSubject = $_GET['subject'] ?? '';
$selectedForm = $_GET['form'] ?? '';
// Only Form 4 sits practical exams, so other Forms never appear here.
$schoolSubjectSelected = $selectedForm === 'form4'
    && isset(school_exam_subjects($selectedForm, true)[$selectedSubject]);
$subjectSelected = !$schoolSubjectSelected && isset(PRACTICAL_SUBJECTS[$selectedSubject]);
$internalSelected = !$subjectSelected && !$schoolSubjectSelected && ($_GET['view'] ?? '') === 'internal';

if ($schoolSubjectSelected) {
    $subjectName = SCHOOL_EXAM_SUBJECTS[$selectedForm][$selectedSubject];
    $schoolPapers = school_exam_papers($selectedForm, $selectedSubject, true);
    $pageTitle = form_label($selectedForm) . ' ' . $subjectName . ' Practical Exams & Marking Schemes | Namanga Secondary School';
    $pageDescription = form_label($selectedForm) . ' ' . $subjectName . ' practical mock and school exam papers with marking schemes.';
} elseif ($subjectSelected) {
    $subjectName = PRACTICAL_SUBJECTS[$selectedSubject];
    $blocks = national_exam_blocks('csee', $selectedSubject, true);
    $pageTitle = 'CSEE ' . $subjectName . ' Practical Past Papers & Marking Schemes | Namanga Secondary School';
    $pageDescription = 'NECTA Form Four (CSEE) ' . $subjectName . ' practical past papers with solutions (marking schemes), listed by year.';
} elseif ($internalSelected) {
    // Oldest first, so each paper keeps its letter when new ones are added.
    $practicals = $pdo->query(
        "SELECT id, title, year FROM resources WHERE resource_type = 'practical' ORDER BY created_at ASC, id ASC"
    )->fetchAll();
    $pageTitle = 'Form 4 Practical Internal Exams | Namanga Secondary School';
    $pageDescription = 'Namanga Secondary School Form 4 internal practical examination papers.';
} else {
    $internalCount = (int) $pdo->query("SELECT COUNT(*) FROM resources WHERE resource_type = 'practical'")->fetchColumn();
    try {
        $taskCount = (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE form_level = 'form4'")->fetchColumn();
    } catch (PDOException $e) {
        $taskCount = 0; // tasks table not created yet on this install
    }
    $schoolSubjects = [];
    foreach (['form4'] as $form) {
        if (($list = school_exam_subjects($form, true)) !== []) {
            $schoolSubjects[$form] = $list;
        }
    }
    $pageTitle = 'Form 4 Practical | Namanga Secondary School';
    $pageDescription = 'NECTA Form Four (CSEE) practical past papers with marking schemes for Biology, Chemistry, Physics, ICS and Food & Nutrition, plus school internal practical exams.';
}

/** 0 → A, 25 → Z, 26 → AA, 51 → AZ, … (spreadsheet-style column letters). */
function exam_letter_label(int $index): string
{
    $label = '';
    for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
        $label = chr(65 + ($n - 1) % 26) . $label;
    }
    return $label;
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Form 4 Practical</h1>
        <?php if ($schoolSubjectSelected): ?>
            <p><?= e(form_label($selectedForm) . ' — ' . $subjectName . ' practical exams and marking schemes. Click a paper to open it.') ?></p>
        <?php elseif ($subjectSelected): ?>
            <p><?= e('CSEE Exams — ' . $subjectName . ' practical papers. Click a year for the exam, or (Solutions) for its marking scheme.') ?></p>
        <?php elseif ($internalSelected): ?>
            <p>Internal Exams — click a paper to open it.</p>
        <?php else: ?>
            <p>Form 4 practical examination papers — national and school exams.</p>
        <?php endif; ?>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if ($schoolSubjectSelected): ?>

            <p><a href="<?= e($base) ?>/practical.php" class="back-link">&larr; Back to Form 4 Practical</a></p>
            <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?>: <?= e($subjectName) ?> Practicals</h2>

            <?php if ($schoolPapers['exam'] !== []): ?>
                <div class="exam-subject-group">
                    <h3 class="exam-group-title">Exams (<?= count($schoolPapers['exam']) ?>)</h3>
                    <?= render_school_exam_list($schoolPapers['exam']) ?>
                </div>
            <?php endif; ?>
            <?php if ($schoolPapers['ms'] !== []): ?>
                <div class="exam-subject-group">
                    <h3 class="exam-group-title">Marking Schemes (<?= count($schoolPapers['ms']) ?>)</h3>
                    <?= render_school_exam_list($schoolPapers['ms']) ?>
                </div>
            <?php endif; ?>

        <?php elseif ($subjectSelected): ?>

            <p><a href="<?= e($base) ?>/practical.php" class="back-link">&larr; Back to Form 4 Practical</a></p>
            <h2 class="section-title-left">CSEE Exams: <?= e($subjectName) ?> Practicals</h2>

            <?php if (empty($blocks)): ?>
                <p class="empty-state">No <?= e($subjectName) ?> practical past papers are currently available. Please check again later.</p>
            <?php else: ?>
                <?= render_national_exam_blocks($blocks, 'Practicals') ?>
            <?php endif; ?>

        <?php elseif ($internalSelected): ?>

            <p><a href="<?= e($base) ?>/practical.php" class="back-link">&larr; Back to Form 4 Practical</a></p>
            <div class="section-title-row">
                <h2 class="section-title-left">Past Non-National Exams: Internal Exams</h2>
                <?php if (!empty($practicals)): ?>
                    <a href="<?= e($base) ?>/download-all.php?category=practical" class="btn btn-outline btn-small">Download All</a>
                <?php endif; ?>
            </div>

            <?php if (empty($practicals)): ?>
                <p class="empty-state">No internal practical exams are currently available. Please check again later.</p>
            <?php else: ?>
                <div class="exam-block">
                    <ul class="exam-year-list">
                        <?php foreach ($practicals as $i => $resource): ?>
                            <li class="exam-year">
                                <a class="exam-year-link" href="<?= e($base) ?>/resource-view.php?id=<?= (int) $resource['id'] ?>"><?= e(exam_letter_label($i)) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <div class="exam-section">
                <h2 class="section-title-left">Past National Exams</h2>
                <div class="exam-subject-group">
                    <h3 class="exam-group-title">Form Four Exams (CSEE) — Practical Subjects</h3>
                    <ul class="exam-subject-list">
                        <?php foreach (PRACTICAL_SUBJECTS as $key => $name): ?>
                            <li><a href="<?= e($base) ?>/practical.php?subject=<?= e($key) ?>"><?= e($name) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="exam-section">
                <h2 class="section-title-left">Past Non-National Exams</h2>
                <?php foreach ($schoolSubjects as $form => $list): ?>
                    <div class="exam-subject-group">
                        <h3 class="exam-group-title"><?= e(form_label($form)) ?> Exams — Practical Subjects</h3>
                        <ul class="exam-subject-list">
                            <?php foreach ($list as $key => [$name, $total]): ?>
                                <li>
                                    <a href="<?= e($base) ?>/practical.php?form=<?= e($form) ?>&amp;subject=<?= e($key) ?>">
                                        <?= e($name) ?> <span class="card-count"><?= $total ?> paper<?= $total === 1 ? '' : 's' ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
                <ul class="exam-subject-list">
                    <li>
                        <a href="<?= e($base) ?>/practical.php?view=internal">
                            Internal Exams <span class="card-count"><?= $internalCount ?> paper<?= $internalCount === 1 ? '' : 's' ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e($base) ?>/tasks.php?form=form4">
                            Student Tasks <span class="card-count"><?= $taskCount ?> task<?= $taskCount === 1 ? '' : 's' ?></span>
                        </a>
                    </li>
                </ul>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
