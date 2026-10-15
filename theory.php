<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/national-exams.php';
require_once __DIR__ . '/includes/school-exams.php';

$base = base_url();
$exams = national_exams();

// Views: Past National Exams (NECTA, external links) picked by
// ?exam=ftna|csee [&subject=…]; the Non-National Exams of a Form picked by
// ?form=form1…form4 [&subject=…] (school exam PDFs by subject plus papers
// uploaded from the admin panel); or the landing page showing both sections.
$selectedExam = $_GET['exam'] ?? '';
$examSelected = isset($exams[$selectedExam]);
$selectedSubject = $_GET['subject'] ?? '';
$subjectSelected = $examSelected && isset($exams[$selectedExam]['subjects'][$selectedSubject]);

$selectedForm = $_GET['form'] ?? '';
$formSelected = !$examSelected && in_array($selectedForm, VALID_FORMS, true);
$schoolSubjectSelected = $formSelected && isset(school_exam_subjects($selectedForm, false)[$selectedSubject]);

if ($subjectSelected) {
    $level = $exams[$selectedExam];
    $subjectName = $level['subjects'][$selectedSubject]['name'];
    $blocks = national_exam_blocks($selectedExam, $selectedSubject, false);
    $pageTitle = $level['title'] . ' ' . $subjectName . ' Past Papers & Marking Schemes | Namanga Secondary School';
    $pageDescription = 'NECTA ' . $level['title'] . ' ' . $subjectName . ' past papers with solutions (marking schemes), listed by year.';
} elseif ($examSelected) {
    $level = $exams[$selectedExam];
    $pageTitle = $level['title'] . ' Past Papers | Namanga Secondary School';
    $pageDescription = 'NECTA ' . $level['label'] . ' past papers and marking schemes for all subjects.';
} elseif ($schoolSubjectSelected) {
    $subjectName = SCHOOL_EXAM_SUBJECTS[$selectedForm][$selectedSubject];
    $schoolPapers = school_exam_papers($selectedForm, $selectedSubject, false);
    $pageTitle = form_label($selectedForm) . ' ' . $subjectName . ' Theory Exams & Marking Schemes | Namanga Secondary School';
    $pageDescription = form_label($selectedForm) . ' ' . $subjectName . ' mock, pre-mock and school exam papers with marking schemes.';
} elseif ($formSelected) {
    $schoolSubjects = school_exam_subjects($selectedForm, false);
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM resources WHERE resource_type = 'theory' AND form_level = :form");
    $countStmt->execute(['form' => $selectedForm]);
    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
    $pageNum = current_page_number($totalPages);
    $offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

    $stmt = $pdo->prepare(
        "SELECT * FROM resources WHERE resource_type = 'theory' AND form_level = :form
         ORDER BY year DESC, created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
    );
    $stmt->execute(['form' => $selectedForm]);
    $papers = $stmt->fetchAll();
    $pageTitle = form_label($selectedForm) . ' Theory Past Papers | Namanga Secondary School';
} else {
    // Counts per Form, shown on the Non-National Exams cards.
    $counts = ['form1' => 0, 'form2' => 0, 'form3' => 0, 'form4' => 0];
    $stmt = $pdo->query("SELECT form_level, COUNT(*) AS total FROM resources WHERE resource_type = 'theory' GROUP BY form_level");
    foreach ($stmt->fetchAll() as $row) {
        if (isset($counts[$row['form_level']])) {
            $counts[$row['form_level']] = (int) $row['total'];
        }
    }
    foreach ($counts as $form => $total) {
        $counts[$form] = $total + school_exam_total($form, false);
    }
    $pageTitle = 'Theory Past Papers | Namanga Secondary School';
    $pageDescription = 'NECTA national past papers (FTNA and CSEE) with marking schemes, plus Namanga Secondary School theory past papers for Form 1 to Form 4.';
}

if ($subjectSelected) {
    $headerText = $level['title'] . ' — ' . $subjectName . ' past papers. Click a year for the exam, or (Solutions) for its marking scheme.';
} elseif ($examSelected) {
    $headerText = $level['label'] . ' — choose a subject.';
} elseif ($schoolSubjectSelected) {
    $headerText = form_label($selectedForm) . ' — ' . $subjectName . ' theory exams and marking schemes. Click a paper to open it.';
} elseif ($formSelected) {
    $headerText = form_label($selectedForm) . ' — complete theory examination papers.';
} else {
    $headerText = 'Choose the national exams or a Form to see its theory examination papers.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Theory Past Papers</h1>
        <p><?= e($headerText) ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if ($subjectSelected): ?>

            <p><a href="<?= e($base) ?>/theory.php?exam=<?= e($selectedExam) ?>" class="back-link">&larr; Back to <?= e($level['title']) ?> subjects</a></p>
            <h2 class="section-title-left"><?= e($level['title']) ?>: <?= e($subjectName) ?></h2>

            <?php if (empty($blocks)): ?>
                <p class="empty-state">No <?= e($subjectName) ?> theory papers are currently available. Please check again later.</p>
            <?php else: ?>
                <?= render_national_exam_blocks($blocks, $level['title']) ?>
            <?php endif; ?>

        <?php elseif ($examSelected): ?>

            <p><a href="<?= e($base) ?>/theory.php" class="back-link">&larr; Back to Theory Past Papers</a></p>
            <h2 class="section-title-left"><?= e($level['title']) ?></h2>

            <?php foreach ($level['groups'] as [$groupName, $subjectKeys]): ?>
                <div class="exam-subject-group">
                    <h3 class="exam-group-title"><?= e($groupName) ?></h3>
                    <ul class="exam-subject-list">
                        <?php foreach ($subjectKeys as $key): ?>
                            <li>
                                <a href="<?= e($base) ?>/theory.php?exam=<?= e($selectedExam) ?>&amp;subject=<?= e($key) ?>">
                                    <?= e($level['subjects'][$key]['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

        <?php elseif ($schoolSubjectSelected): ?>

            <p><a href="<?= e($base) ?>/theory.php?form=<?= e($selectedForm) ?>" class="back-link">&larr; Back to <?= e(form_label($selectedForm)) ?> subjects</a></p>
            <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?>: <?= e($subjectName) ?></h2>

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

        <?php elseif ($formSelected): ?>

            <p><a href="<?= e($base) ?>/theory.php" class="back-link">&larr; Back to Theory Past Papers</a></p>

            <?php if ($schoolSubjects !== []): ?>
                <div class="exam-section">
                    <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> Exams by Subject</h2>
                    <ul class="exam-subject-list">
                        <?php foreach ($schoolSubjects as $key => [$name, $total]): ?>
                            <li>
                                <a href="<?= e($base) ?>/theory.php?form=<?= e($selectedForm) ?>&amp;subject=<?= e($key) ?>">
                                    <?= e($name) ?> <span class="card-count"><?= $total ?> paper<?= $total === 1 ? '' : 's' ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($schoolSubjects === [] || !empty($papers)): ?>
            <div class="section-title-row">
                <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> <?= $schoolSubjects === [] ? 'Theory Past Papers' : 'Other Theory Papers' ?></h2>
                <?php if (!empty($papers)): ?>
                    <a href="<?= e($base) ?>/download-all.php?category=theory&amp;form=<?= e($selectedForm) ?>" class="btn btn-outline btn-small">Download All</a>
                <?php endif; ?>
            </div>

            <?php if (empty($papers)): ?>
                <p class="empty-state">No <?= e(form_label($selectedForm)) ?> theory past papers are currently available. Please check again later.</p>
            <?php else: ?>
                <div class="resource-link-list">
                    <?php foreach ($papers as $resource): ?>
                        <a class="resource-link-item" href="<?= e($base) ?>/resource-view.php?id=<?= (int) $resource['id'] ?>">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($resource['title']) ?></span>
                                <span class="resource-link-meta">
                                    <?php if (!empty($resource['year'])): ?><span class="badge badge-outline"><?= e((string) $resource['year']) ?></span><?php endif; ?>
                                    <span class="file-size"><?= e(strtoupper(pathinfo($resource['file_name'], PATHINFO_EXTENSION))) ?> &middot; <?= e(format_file_size((int) $resource['file_size'])) ?></span>
                                </span>
                            </span>
                            <span class="resource-link-arrow">›</span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?= render_pagination($pageNum, $totalPages, 'theory.php?form=' . $selectedForm) ?>
            <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>

            <div class="exam-section">
                <h2 class="section-title-left">Past National Exams</h2>
                <div class="photo-cards-grid">
                    <?php foreach ($exams as $key => $examLevel): ?>
                        <?= render_photo_card($base . '/theory.php?exam=' . $key, 'theory-' . $key, $examLevel['label'],
                            'NECTA ' . $examLevel['full_name'] . ' past papers with marking schemes.', null, 'View ' . $examLevel['title']) ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="exam-section">
                <h2 class="section-title-left">Past Non-National Exams</h2>
                <div class="photo-cards-grid">
                    <?php foreach (VALID_FORMS as $form): ?>
                        <?= render_photo_card($base . '/theory.php?form=' . $form, 'theory-' . $form, form_label($form),
                            'Theory past papers for ' . form_label($form) . '.', count_label($counts[$form], 'paper'), 'View ' . form_label($form)) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?= render_card_photo_credits(array_merge(array_map(fn ($k) => 'theory-' . $k, array_keys($exams)), array_map(fn ($f) => 'theory-' . $f, VALID_FORMS))) ?>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
