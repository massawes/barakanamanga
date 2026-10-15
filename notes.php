<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/notes-subjects.php';

// ?form=formN shows that Form's subjects (2023 new curriculum) plus the notes
// uploaded by teachers; adding &subject=… shows one subject's online links.
$selectedForm = $_GET['form'] ?? '';
$formSelected = in_array($selectedForm, VALID_FORMS, true);

$subjects = $formSelected ? notes_subjects($selectedForm) : [];
$selectedSubject = $_GET['subject'] ?? '';
$subjectSelected = isset($subjects[$selectedSubject]);

$base = base_url();

if ($subjectSelected) {
    $subject = $subjects[$selectedSubject];
    // Form 1-2 sit the FTNA, Form 3-4 the CSEE.
    $examLevel = in_array($selectedForm, ['form1', 'form2'], true) ? 'ftna' : 'csee';

    // Teachers' notes for this Form whose title mentions the subject.
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE resource_type = 'notes' AND form_level = :form ORDER BY created_at DESC");
    $stmt->execute(['form' => $selectedForm]);
    $items = array_values(array_filter($stmt->fetchAll(), static function (array $row) use ($subject): bool {
        $title = ' ' . strtolower($row['title']) . ' ';
        foreach ($subject['keywords'] as $keyword) {
            if (str_contains($title, $keyword)) {
                return true;
            }
        }
        return false;
    }));

    $pageTitle = form_label($selectedForm) . ' ' . $subject['name'] . ' Notes | Namanga Secondary School';
    $pageDescription = form_label($selectedForm) . ' ' . $subject['name'] . ' (new syllabus): official TIE syllabus, textbooks, past papers and notes — Namanga Secondary School, Longido, Arusha.';
} elseif ($formSelected) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM resources WHERE resource_type = 'notes' AND form_level = :form");
    $countStmt->execute(['form' => $selectedForm]);
    $totalItems = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalItems / RESOURCES_PER_PAGE));
    $pageNum = current_page_number($totalPages);
    $offset = ($pageNum - 1) * RESOURCES_PER_PAGE;

    $stmt = $pdo->prepare(
        "SELECT * FROM resources WHERE resource_type = 'notes' AND form_level = :form
         ORDER BY created_at DESC LIMIT " . RESOURCES_PER_PAGE . " OFFSET " . $offset
    );
    $stmt->execute(['form' => $selectedForm]);
    $items = $stmt->fetchAll();
    $pageTitle = form_label($selectedForm) . ' Notes | Namanga Secondary School';
    $pageDescription = form_label($selectedForm) . ' subjects (new syllabus) with official TIE syllabus, textbooks, past papers and teachers\' notes — Namanga Secondary School, Longido, Arusha.';
} else {
    // Counts per Form, shown on the selection menu.
    $counts = ['form1' => 0, 'form2' => 0, 'form3' => 0, 'form4' => 0];
    $stmt = $pdo->query("SELECT form_level, COUNT(*) AS total FROM resources WHERE resource_type = 'notes' GROUP BY form_level");
    foreach ($stmt->fetchAll() as $row) {
        if (isset($counts[$row['form_level']])) {
            $counts[$row['form_level']] = (int) $row['total'];
        }
    }
    $pageTitle = 'Notes | Namanga Secondary School';
    $pageDescription = 'Free notes for every subject, Form 1 to Form 4 (new syllabus), from Namanga Secondary School (S.2911), Longido, Arusha, Tanzania. Official TIE syllabus, textbooks and past papers.';
}

if ($subjectSelected) {
    $headerText = form_label($selectedForm) . ' — ' . $subject['name'] . ' (new syllabus).';
} elseif ($formSelected) {
    $headerText = form_label($selectedForm) . ' — choose a subject.';
} else {
    $headerText = 'Choose a Form to see its subjects.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Notes</h1>
        <p><?= e($headerText) ?></p>
    </div>
</section>

<section class="page-content">
    <div class="container">

        <?php if ($subjectSelected): ?>

            <p><a href="<?= e($base) ?>/notes.php?form=<?= e($selectedForm) ?>" class="back-link">&larr; Back to <?= e(form_label($selectedForm)) ?> subjects</a></p>
            <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?>: <?= e($subject['name']) ?></h2>

            <div class="exam-subject-group">
                <h3 class="exam-group-title">Online resources</h3>
                <div class="resource-link-list">
                    <?php if (isset($subject['syllabus'])): ?>
                        <a class="resource-link-item" href="<?= e(TIE_SYLLABUS_BASE . rawurlencode($subject['syllabus'])) ?>" target="_blank" rel="noopener">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($subject['name']) ?> Syllabus — New Curriculum (Form 1-4)</span>
                                <span class="resource-link-meta"><span class="file-size">PDF &middot; Tanzania Institute of Education (TIE)</span></span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endif; ?>
                    <a class="resource-link-item" href="https://ol.tie.go.tz/" target="_blank" rel="noopener">
                        <span class="resource-link-main">
                            <span class="resource-link-title"><?= e(form_label($selectedForm)) ?> <?= e($subject['name']) ?> Textbook — TIE Online Library</span>
                            <span class="resource-link-meta"><span class="file-size">Official textbooks &middot; free account, then search &ldquo;<?= e($subject['name']) ?>&rdquo;</span></span>
                        </span>
                        <span class="resource-link-arrow">&rsaquo;</span>
                    </a>
                    <?php if (isset($subject[$examLevel])): ?>
                        <a class="resource-link-item" href="<?= e($base) ?>/theory.php?exam=<?= e($examLevel) ?>&amp;subject=<?= e($subject[$examLevel]) ?>">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($subject['name']) ?> Past Papers (<?= $examLevel === 'ftna' ? 'Form Two — FTNA' : 'Form Four — CSEE' ?>)</span>
                                <span class="resource-link-meta"><span class="file-size">NECTA exams with solutions</span></span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endif; ?>
                    <?php if ($selectedForm === 'form4' && isset($subject['old'])): ?>
                        <a class="resource-link-item" href="<?= e(TETEA_OLD_SYLLABUS_BASE . rawurlencode($subject['old'])) ?>" target="_blank" rel="noopener">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($subject['name']) ?> Syllabus — Old Curriculum</span>
                                <span class="resource-link-meta"><span class="file-size">PDF &middot; for Form 4 students who started Form 1 before 2024 (TETEA)</span></span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="exam-subject-group">
                <h3 class="exam-group-title">Notes from our teachers</h3>
                <?php if (empty($items)): ?>
                    <p class="empty-state">No <?= e(form_label($selectedForm) . ' ' . $subject['name']) ?> notes have been uploaded yet. Please check again later.</p>
                <?php else: ?>
                    <div class="resource-link-list">
                        <?php foreach ($items as $resource): ?>
                            <?php include __DIR__ . '/includes/notes-link-item.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($formSelected): ?>

            <p><a href="<?= e($base) ?>/notes.php" class="back-link">&larr; Back to Form selection</a></p>
            <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> Subjects (New Syllabus)</h2>

            <div class="exam-subject-group">
                <ul class="exam-subject-list">
                    <?php foreach ($subjects as $key => $subject): ?>
                        <li>
                            <a href="<?= e($base) ?>/notes.php?form=<?= e($selectedForm) ?>&amp;subject=<?= e($key) ?>">
                                <?= e($subject['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="section-title-row">
                <h2 class="section-title-left">Notes from our teachers</h2>
                <?php if (!empty($items)): ?>
                    <a href="<?= e($base) ?>/download-all.php?category=notes&amp;form=<?= e($selectedForm) ?>" class="btn btn-outline btn-small">Download All</a>
                <?php endif; ?>
            </div>

            <?php if (empty($items)): ?>
                <p class="empty-state">No <?= e(form_label($selectedForm)) ?> notes have been uploaded yet. Please check again later.</p>
            <?php else: ?>
                <div class="resource-link-list">
                    <?php foreach ($items as $resource): ?>
                        <?php include __DIR__ . '/includes/notes-link-item.php'; ?>
                    <?php endforeach; ?>
                </div>
                <?= render_pagination($pageNum, $totalPages, 'notes.php?form=' . $selectedForm) ?>
            <?php endif; ?>

        <?php else: ?>

            <div class="cards-grid">
                <?php foreach (VALID_FORMS as $form): ?>
                    <a href="<?= e($base) ?>/notes.php?form=<?= e($form) ?>" class="category-card form-select-card">
                        <div class="category-icon"><?= svg_icon('notes') ?></div>
                        <h3 class="form-big-label"><?= e(strtoupper(form_label($form))) ?></h3>
                        <p>All <?= e(form_label($form)) ?> subjects &mdash; new syllabus.</p>
                        <span class="card-count"><?= $counts[$form] ?> teacher file<?= $counts[$form] === 1 ? '' : 's' ?></span>
                        <span class="btn btn-outline">View <?= e(form_label($form)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
