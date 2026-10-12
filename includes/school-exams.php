<?php
/**
 * Helpers for the school (non-national) exam papers shown on theory.php and
 * practical.php. The files (PDF, a few Word) live in /exams/<form>/<subject>/ and are listed in
 * school-exams-data.php, one row per paper:
 * [title, year or null, file path, 'exam'|'ms', is practical, size in bytes].
 */

// Subjects shown for each Form, in display order: Forms 1-2 follow the new
// syllabus (same list as the FTNA exams), Forms 3-4 the CSEE list.
const SCHOOL_EXAM_SUBJECTS = [
    'form1' => [
        'historia-maadili' => 'Historia ya Tanzania na Maadili',
        'history'          => 'History',
        'geography'        => 'Geography',
        'kiswahili'        => 'Kiswahili',
        'english'          => 'English',
        'physics'          => 'Physics',
        'chemistry'        => 'Chemistry',
        'biology'          => 'Biology',
        'math'             => 'Mathematics',
        'business'         => 'Business Studies',
        'bookkeeping'      => 'Book-Keeping',
        'foodnutrition'    => 'Food & Nutrition',
        'ics'              => 'Information & Computer Studies',
        'kiislamu'         => 'Islamic Knowledge',
    ],
    'form2' => [
        'historia-maadili' => 'Historia ya Tanzania na Maadili',
        'history'          => 'History',
        'geography'        => 'Geography',
        'kiswahili'        => 'Kiswahili',
        'english'          => 'English',
        'physics'          => 'Physics',
        'chemistry'        => 'Chemistry',
        'biology'          => 'Biology',
        'math'             => 'Mathematics',
        'business'         => 'Business Studies',
        'bookkeeping'      => 'Book-Keeping',
        'foodnutrition'    => 'Food & Nutrition',
        'ics'              => 'Information & Computer Studies',
        'kiislamu'         => 'Islamic Knowledge',
    ],
    'form3' => [
        'civics'        => 'Civics',
        'history'       => 'History',
        'geography'     => 'Geography',
        'kiswahili'     => 'Kiswahili',
        'english'       => 'English',
        'physics'       => 'Physics',
        'chemistry'     => 'Chemistry',
        'biology'       => 'Biology',
        'math'          => 'Basic Mathematics',
        'bookkeeping'   => 'Book-Keeping',
        'commerce'      => 'Commerce',
        'foodnutrition' => 'Food & Nutrition',
        'literature'    => 'Literature in English',
        'kiislamu'      => 'Islamic Knowledge',
        'ics'           => 'Information & Computer Studies',
    ],
    'form4' => [
        'civics'        => 'Civics',
        'history'       => 'History',
        'geography'     => 'Geography',
        'kiswahili'     => 'Kiswahili',
        'english'       => 'English',
        'physics'       => 'Physics',
        'chemistry'     => 'Chemistry',
        'biology'       => 'Biology',
        'math'          => 'Basic Mathematics',
        'bookkeeping'   => 'Book-Keeping',
        'commerce'      => 'Commerce',
        'foodnutrition' => 'Food & Nutrition',
        'literature'    => 'Literature in English',
        'kiislamu'      => 'Islamic Knowledge',
        'ics'           => 'Information & Computer Studies',
    ],
];

function school_exams(): array
{
    static $data = null;
    if ($data === null) {
        $file = __DIR__ . '/school-exams-data.php';
        $data = is_file($file) ? require $file : [];
    }
    return $data;
}

/** Papers of one subject, split into ['exam' => [...], 'ms' => [...]], newest first. */
function school_exam_papers(string $form, string $subject, bool $practical): array
{
    $split = ['exam' => [], 'ms' => []];
    foreach (school_exams()[$form][$subject] ?? [] as $paper) {
        if ($paper[4] === $practical) {
            $split[$paper[3]][] = $paper;
        }
    }
    foreach ($split as &$papers) {
        usort($papers, static fn(array $a, array $b): int => [(int) $b[1], $a[0]] <=> [(int) $a[1], $b[0]]);
    }
    return $split;
}

/** Subjects of a Form that have at least one paper: key => [name, paper count]. */
function school_exam_subjects(string $form, bool $practical): array
{
    $subjects = [];
    foreach (SCHOOL_EXAM_SUBJECTS[$form] ?? [] as $key => $name) {
        $papers = school_exam_papers($form, $key, $practical);
        $count = count($papers['exam']) + count($papers['ms']);
        if ($count > 0) {
            $subjects[$key] = [$name, $count];
        }
    }
    return $subjects;
}

function school_exam_total(string $form, bool $practical): int
{
    return array_sum(array_column(school_exam_subjects($form, $practical), 1));
}

/** Renders a list of papers; each opens its file (PDF, or Word when it could not be converted) in a new tab. */
function render_school_exam_list(array $papers): string
{
    $base = base_url();
    $html = '<div class="resource-link-list">';
    foreach ($papers as [$title, $year, $file, , , $size]) {
        $html .= '<a class="resource-link-item" href="' . e($base . '/' . $file) . '" target="_blank" rel="noopener">';
        $html .= '<span class="resource-link-main">';
        $html .= '<span class="resource-link-title">' . e($title) . '</span>';
        $html .= '<span class="resource-link-meta">';
        if ($year !== null) {
            $html .= '<span class="badge badge-outline">' . e((string) $year) . '</span>';
        }
        $html .= '<span class="file-size">' . e(strtoupper(pathinfo($file, PATHINFO_EXTENSION))) . ' &middot; ' . e(format_file_size($size)) . '</span>';
        $html .= '</span></span><span class="resource-link-arrow">›</span></a>';
    }
    return $html . '</div>';
}
