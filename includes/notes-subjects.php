<?php
/**
 * Subjects listed on notes.php for each Form (see notes-subjects-data.php).
 * The syllabus PDFs are external links — nothing is stored on this server.
 */

const TIE_SYLLABUS_BASE = 'https://www.tie.go.tz/uploads/documents/';
const TETEA_OLD_SYLLABUS_BASE = 'https://maktaba.tetea.org/study-aids/o-level/syllabi/';

/** Form 1-2 follow the 2023 new syllabus; Form 3-4 still follow the old one. */
function notes_new_syllabus(string $form): bool
{
    return $form === 'form1' || $form === 'form2';
}

/**
 * Subjects taught in $form, keyed by subject slug, in display order. On the
 * old syllabus (Form 3-4) a subject shows its old name ('old_name').
 */
function notes_subjects(string $form): array
{
    static $all = null;
    if ($all === null) {
        $all = require __DIR__ . '/notes-subjects-data.php';
    }
    $subjects = [];
    foreach ($all as $key => $subject) {
        if (isset($subject['forms']) && !in_array($form, $subject['forms'], true)) {
            continue;
        }
        if (!notes_new_syllabus($form) && isset($subject['old_name'])) {
            $subject['name'] = $subject['old_name'];
        }
        $subjects[$key] = $subject;
    }
    return $subjects;
}
