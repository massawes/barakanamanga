<?php
/**
 * Facts about the school used on several pages of the school website.
 * Subjects: the 18 subjects in the school's Results System (results.namangasecondary.sc.tz), 2026.
 * Change them here and every page updates.
 */

const SCHOOL_SUBJECTS = [
    'Languages' => [
        'Kiswahili',
        'English Language',
        'Literature in English',
    ],
    'Science & Mathematics' => [
        'Basic Mathematics',
        'Physics',
        'Chemistry',
        'Biology',
    ],
    'Humanities' => [
        'Civics',
        'History',
        'Geography',
        'History of Tanzania and Moral Education',
        'Elimu ya Dini (EDK)',
    ],
    'Business' => [
        'Business Studies',
        'Commerce',
        'Book-Keeping',
    ],
    'Technology & Life Skills' => [
        'Computer Science',
        'Information and Computer Studies',
        'Food and Human Nutrition',
    ],
];

/** Number of subjects taught (all groups). */
function school_subject_count(): int
{
    return array_sum(array_map('count', SCHOOL_SUBJECTS));
}
