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

/**
 * Textbooks / notes of $subject for $form, ready to list: each has 'href',
 * 'title' and 'meta'. An entry in 'pdf' is one item or a list of items; an
 * item opens a Google Drive file ('id'), a TIE Online Library book ('tie'),
 * a TIE flipbook on FlipHTML5 ('flip') or any other address ('url').
 */
function notes_books(array $subject, string $form): array
{
    $entry = $subject['pdf'][$form] ?? [];
    $items = isset($entry[0]) ? $entry : ($entry === [] ? [] : [$entry]);
    $syllabus = notes_new_syllabus($form) ? 'New Syllabus' : 'Old Syllabus';
    $books = [];
    foreach ($items as $item) {
        $size = isset($item['size']) ? ' · ' . format_file_size($item['size']) : '';
        $by = isset($item['by']) ? ' · by ' . $item['by'] : '';
        if (isset($item['tie'])) {
            $href = 'https://ol.tie.go.tz/book-detail/' . $item['tie'];
            $meta = 'TIE Online Library · sign in free to read';
        } elseif (isset($item['flip'])) {
            $href = 'https://online.fliphtml5.com/rwbnv/' . $item['flip'] . '/';
            $meta = 'Official TIE online book · tap to read, no sign-in';
        } elseif (isset($item['id'])) {
            $href = 'https://drive.google.com/file/d/' . $item['id'] . '/view';
            $meta = 'PDF' . $size . $by . (!empty($item['view']) ? ' · tap to read online' : ' · tap to read or download');
        } else {
            $href = $item['url'];
            $meta = (str_ends_with($item['url'], '.pdf') ? 'PDF' . $size : 'Web page') . $by . ' · tap to open';
        }
        $label = $item['label'] ?? $subject['name'] . ' Book';
        $books[] = [
            'href' => $href,
            'title' => form_label($form) . ' ' . $label . (isset($item['by']) ? '' : ' (TIE — ' . $syllabus . ')'),
            'meta' => $meta,
        ];
    }
    return $books;
}
