<?php
/**
 * Helpers for the NECTA national past papers shown on theory.php and
 * practical.php. The papers themselves are external links (see
 * national-exams-data.php) — nothing is downloaded to or stored on this server.
 */

// Practical subjects listed on practical.php, in display order. Each maps
// to the CSEE subject key in national-exams-data.php.
const PRACTICAL_SUBJECTS = [
    'biology'       => 'Biology',
    'chemistry'     => 'Chemistry',
    'physics'       => 'Physics',
    'computers'     => 'Information & Computer Studies',
    'foodnutrition' => 'Food & Nutrition',
];

function national_exams(): array
{
    static $data = null;
    if ($data === null) {
        $data = require __DIR__ . '/national-exams-data.php';
    }
    return $data;
}

/**
 * Blocks (Paper 1, Practical 2A, Private Candidates…) of one subject.
 * $practical = false gives the theory papers, true the practical ones.
 */
function national_exam_blocks(string $level, string $subject, bool $practical): array
{
    $blocks = national_exams()[$level]['subjects'][$subject]['blocks'] ?? [];
    return array_values(array_filter($blocks, static fn(array $b): bool => $b[1] === $practical && $b[2] !== []));
}

function national_exam_paper_count(array $blocks): int
{
    $total = 0;
    foreach ($blocks as $block) {
        $total += count($block[2]);
    }
    return $total;
}

/**
 * Renders a subject's papers, each year linking to the exam and
 * "(Solutions)" linking to its marking scheme.
 */
function render_national_exam_blocks(array $blocks, string $defaultTitle): string
{
    $html = '';
    foreach ($blocks as [$title, , $papers]) {
        $html .= '<div class="exam-block">';
        $html .= '<h3 class="exam-block-title">' . e($title !== '' ? $title : $defaultTitle) . '</h3>';
        $html .= '<ul class="exam-year-list">';
        foreach ($papers as [$year, $examUrl, $solutionUrl]) {
            $html .= '<li class="exam-year">';
            $html .= '<a class="exam-year-link" href="' . e($examUrl) . '" target="_blank" rel="noopener" title="Open the ' . e($year) . ' exam paper">' . e($year) . '</a>';
            if ($solutionUrl !== null) {
                $html .= ' <a class="exam-solution-link" href="' . e($solutionUrl) . '" target="_blank" rel="noopener" title="Open the ' . e($year) . ' marking scheme">(Solutions)</a>';
            }
            $html .= '</li>';
        }
        $html .= '</ul></div>';
    }
    return $html;
}
