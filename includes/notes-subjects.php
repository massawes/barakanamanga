<?php
/**
 * Subjects listed on notes.php for each Form (see notes-subjects-data.php).
 * The syllabus PDFs are external links — nothing is stored on this server.
 */

const TIE_SYLLABUS_BASE = 'https://www.tie.go.tz/uploads/documents/';
const TETEA_OLD_SYLLABUS_BASE = 'https://maktaba.tetea.org/study-aids/o-level/syllabi/';

/** Subjects taught in $form, keyed by subject slug, in display order. */
function notes_subjects(string $form): array
{
    static $all = null;
    if ($all === null) {
        $all = require __DIR__ . '/notes-subjects-data.php';
    }
    return array_filter($all, static fn(array $s): bool => !isset($s['forms']) || in_array($form, $s['forms'], true));
}
