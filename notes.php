<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/notes-subjects.php';

// ?form=formN shows that Form's subjects (Form 1-2 on the 2023 new syllabus,
// Form 3-4 on the old one);
// adding &subject=… shows one subject's online links.
$selectedForm = $_GET['form'] ?? '';
$formSelected = in_array($selectedForm, VALID_FORMS, true);

$subjects = $formSelected ? notes_subjects($selectedForm) : [];
$selectedSubject = $_GET['subject'] ?? '';
$subjectSelected = isset($subjects[$selectedSubject]);

$base = base_url();
$syllabusLabel = $formSelected && !notes_new_syllabus($selectedForm) ? 'old syllabus' : 'new syllabus';

if ($subjectSelected) {
    $subject = $subjects[$selectedSubject];
    // Form 1-2 sit the FTNA, Form 3-4 the CSEE.
    $examLevel = in_array($selectedForm, ['form1', 'form2'], true) ? 'ftna' : 'csee';

    $pageTitle = form_label($selectedForm) . ' ' . $subject['name'] . ' Notes | Namanga Secondary School';
    $pageDescription = form_label($selectedForm) . ' ' . $subject['name'] . ' (' . $syllabusLabel . '): TIE textbook, syllabus and past papers — Namanga Secondary School, Longido, Arusha.';
} elseif ($formSelected) {
    $pageTitle = form_label($selectedForm) . ' Notes | Namanga Secondary School';
    $pageDescription = form_label($selectedForm) . ' subjects (' . $syllabusLabel . ') with TIE textbooks, syllabus and past papers — Namanga Secondary School, Longido, Arusha.';
} else {
    $pageTitle = 'Notes | Namanga Secondary School';
    $pageDescription = 'Free notes for every subject, Form 1 to Form 4, from Namanga Secondary School (S.2911), Longido, Arusha, Tanzania. TIE textbooks, syllabus and past papers.';
}

if ($subjectSelected) {
    $headerText = form_label($selectedForm) . ' — ' . $subject['name'] . ' (' . $syllabusLabel . ').';
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
                <h3 class="exam-group-title">Textbook (Notes)</h3>
                <?php if (isset($subject['pdf'][$selectedForm])): ?>
                    <?php $book = $subject['pdf'][$selectedForm]; ?>
                    <div class="resource-link-list">
                        <a class="resource-link-item" href="<?= e(isset($book['tie']) ? 'https://ol.tie.go.tz/book-detail/' . $book['tie'] : 'https://drive.google.com/file/d/' . $book['id'] . '/view') ?>" target="_blank" rel="noopener">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e(form_label($selectedForm)) ?> <?= e($book['label'] ?? $subject['name'] . ' Book') ?> (TIE &mdash; <?= e(ucwords($syllabusLabel)) ?>)</span>
                                <?php if (isset($book['tie'])): ?>
                                    <span class="resource-link-meta"><span class="file-size">TIE Online Library &middot; sign in free to read</span></span>
                                <?php else: ?>
                                    <span class="resource-link-meta"><span class="file-size">PDF<?= isset($book['size']) ? ' &middot; ' . e(format_file_size($book['size'])) : '' ?> &middot; tap to read or download</span></span>
                                <?php endif; ?>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    </div>
                <?php else: ?>
                    <p class="empty-state">The <?= e(form_label($selectedForm) . ' ' . $subject['name']) ?> book is not available yet. Please check again later.</p>
                <?php endif; ?>
            </div>

            <div class="exam-subject-group">
                <h3 class="exam-group-title">Syllabus &amp; Past Papers</h3>
                <div class="resource-link-list">
                    <?php if (notes_new_syllabus($selectedForm) && isset($subject['syllabus'])): ?>
                        <a class="resource-link-item" href="<?= e(TIE_SYLLABUS_BASE . rawurlencode($subject['syllabus'])) ?>" target="_blank" rel="noopener">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($subject['name']) ?> Syllabus — New Syllabus (Form 1-4)</span>
                                <span class="resource-link-meta"><span class="file-size">PDF &middot; Tanzania Institute of Education (TIE)</span></span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isset($subject[$examLevel])): ?>
                        <a class="resource-link-item" href="<?= e($base) ?>/theory.php?exam=<?= e($examLevel) ?>&amp;subject=<?= e($subject[$examLevel]) ?>">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($subject['name']) ?> Past Papers (<?= $examLevel === 'ftna' ? 'Form Two — FTNA' : 'Form Four — CSEE' ?>)</span>
                                <span class="resource-link-meta"><span class="file-size">NECTA exams with solutions</span></span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endif; ?>
                    <?php if (!notes_new_syllabus($selectedForm) && isset($subject['old'])): ?>
                        <a class="resource-link-item" href="<?= e(TETEA_OLD_SYLLABUS_BASE . rawurlencode($subject['old'])) ?>" target="_blank" rel="noopener">
                            <span class="resource-link-main">
                                <span class="resource-link-title"><?= e($subject['name']) ?> Syllabus — Old Syllabus (Form 1-4)</span>
                                <span class="resource-link-meta"><span class="file-size">PDF &middot; the syllabus Form 3 &amp; 4 follow (TETEA)</span></span>
                            </span>
                            <span class="resource-link-arrow">&rsaquo;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($formSelected): ?>

            <p><a href="<?= e($base) ?>/notes.php" class="back-link">&larr; Back to Form selection</a></p>
            <h2 class="section-title-left"><?= e(form_label($selectedForm)) ?> Subjects (<?= e(ucwords($syllabusLabel)) ?>)</h2>

            <div class="exam-subject-group">
                <ul class="exam-subject-list">
                    <?php foreach ($subjects as $key => $subject): ?>
                        <li>
                            <a href="<?= e($base) ?>/notes.php?form=<?= e($selectedForm) ?>&amp;subject=<?= e($key) ?>">
                                <?= e($subject['name']) ?>
                                <?php if (isset($subject['pdf'][$selectedForm])): ?><span class="card-count">Book</span><?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php else: ?>

            <div class="cards-grid">
                <?php foreach (VALID_FORMS as $form): ?>
                    <a href="<?= e($base) ?>/notes.php?form=<?= e($form) ?>" class="category-card form-select-card">
                        <div class="category-icon"><?= svg_icon('notes') ?></div>
                        <h3 class="form-big-label"><?= e(strtoupper(form_label($form))) ?></h3>
                        <p>All <?= e(form_label($form)) ?> subjects &mdash; <?= notes_new_syllabus($form) ? 'new' : 'old' ?> syllabus.</p>
                        <span class="card-count"><?= count(notes_subjects($form)) ?> subjects</span>
                        <span class="btn btn-outline">View <?= e(form_label($form)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
