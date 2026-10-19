<?php
/**
 * Photo cards used across the site (homepage categories and every
 * "choose a Form" page). Photos live in assets/images/cards/<key>.jpg and
 * come from Wikimedia Commons under CC BY-SA 4.0, which requires crediting
 * the photographer — see render_card_photo_credits(). The school's own
 * photos (SCHOOL_CARD_PHOTOS) need no credit.
 */

const CARD_PHOTOS = [
    'notes'         => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Jangwani_students_during_Kiwix_Presentation.jpg'],
    'summary'       => ['Seemannaufland', 'https://commons.wikimedia.org/wiki/File:Students_in_classroom_Tanzania.JPG'],
    'theory'        => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3313IMG_3314IMG_3318.jpg'],
    'practical'     => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_Practising_Kiwix_Installation_at_Jangwani_Secondary_School.jpg'],
    'software'      => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Students_Using_kiwix_to_learn.jpg'],
    'others'        => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_during_Kiwix_Presentation_at_Jangwani_Secondary_School-Tanzania.jpg'],
    'gallery'       => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_and_Kiwix_Team_at_Jangwani_Secondary_School.jpg'],
    // Notes page
    'form1'         => ['Mpemba01', 'https://commons.wikimedia.org/wiki/File:Students_practising_the_Kiwix_Tool_at_Kajengwa_Primary_School.jpg'],
    'form2'         => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3319.jpg'],
    'form3'         => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Students_Learning_how_use_kiwix.jpg'],
    'form4'         => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:A_trainer_guiding_students_on_how_to_use_kiwix.jpg'],
    // Summary page
    'summary-form1' => ['Alhassan Abdul-Manan', 'https://commons.wikimedia.org/wiki/File:Kiwix_training_sessions_03.jpg'],
    'summary-form2' => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3313IMG_3314.jpg'],
    'summary-form3' => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Students_taking_through_on_how_to_use_kiwix.jpg'],
    'summary-form4' => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Training_students_on_how_to_install_Kiwix.jpg'],
    // Tasks page
    'tasks-form1'   => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Students_practicing_on_how_to_use_kiwix.jpg'],
    'tasks-form2'   => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3313IMG_3314IMG_3316.jpg'],
    'tasks-form3'   => ['Alhassan Abdul-Manan', 'https://commons.wikimedia.org/wiki/File:Kiwix_training_sessions_02.jpg'],
    'tasks-form4'   => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_practicing_Kiwix_use_in_Computer_Labaratory.jpg'],
    // Theory Past Papers page
    'theory-ftna'   => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3313IMG_3314IMG_3315.jpg'],
    'theory-csee'   => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Teacher_Helping_students_to_use_Kiwix.jpg'],
    'theory-form1'  => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Helping_students_to_launch_the_kiwix.jpg'],
    'theory-form2'  => ['Alhassan Abdul-Manan', 'https://commons.wikimedia.org/wiki/File:Kiwix_team_and_students_17.jpg'],
    'theory-form3'  => ['Alhassan Abdul-Manan', 'https://commons.wikimedia.org/wiki/File:Kiwix_team_and_students_09.jpg'],
    'theory-form4'  => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Team_and_Students_at_Jangwani_Secondary_School.jpg'],
];

// The school's own photos (assets/images/school/), used as card photos.
const SCHOOL_CARD_PHOTOS = [
    'about-mission'  => 'assembly.jpg',
    'about-history'  => 'ceremony.jpg',
    'about-students' => 'students-red.jpg',
    'results-form4'  => 'ict-student-1.jpg',
    'results-form2'  => 'ict-student-2.jpg',
];

/** Address of a card photo, e.g. card_photo_url('notes'). */
function card_photo_url(string $key): string
{
    if (isset(SCHOOL_CARD_PHOTOS[$key])) {
        return base_url() . '/assets/images/school/' . SCHOOL_CARD_PHOTOS[$key];
    }
    return base_url() . '/assets/images/cards/' . $key . '.jpg';
}

/**
 * One photo card: photo with a slanted bottom edge, title, text, an
 * optional small count line ("14 files") and an orange link line.
 */
function render_photo_card(string $href, string $photoKey, string $title, string $text, ?string $count, string $linkText): string
{
    return '<a href="' . e($href) . '" class="photo-card">'
        . '<span class="photo-card-img"><img src="' . e(card_photo_url($photoKey)) . '" alt="Students at school" loading="lazy" width="900" height="600"></span>'
        . '<span class="photo-card-body">'
        . '<span class="photo-card-title">' . e($title) . '</span>'
        . '<span class="photo-card-text">' . e($text) . '</span>'
        . ($count !== null ? '<span class="photo-card-count">' . e($count) . '</span>' : '')
        . '<span class="photo-card-link">' . e($linkText) . ' &rsaquo;</span>'
        . '</span></a>';
}

/** "3 files", "1 file" … */
function count_label(int $count, string $word): string
{
    return $count . ' ' . $word . ($count === 1 ? '' : 's');
}

/**
 * One-line credit for the online photos among $keys: each photographer
 * once, followed by numbered links to their photos' file pages. The
 * school's own photos are skipped; nothing is printed if none are left.
 */
function render_card_photo_credits(array $keys): string
{
    // Credits line hidden on the owner's request. Delete this line to show it again.
    return '';

    $byAuthor = [];
    foreach ($keys as $key) {
        if (!isset(CARD_PHOTOS[$key])) {
            continue;
        }
        [$author, $page] = CARD_PHOTOS[$key];
        $byAuthor[$author][] = $page;
    }
    if ($byAuthor === []) {
        return '';
    }
    $parts = [];
    foreach ($byAuthor as $author => $pages) {
        $links = [];
        foreach ($pages as $i => $page) {
            $links[] = '<a href="' . e($page) . '" target="_blank" rel="noopener">' . ($i + 1) . '</a>';
        }
        $parts[] = e($author) . ' (' . implode(', ', $links) . ')';
    }
    return '<p class="photo-credits">Photos: ' . implode(', ', $parts)
        . ' &middot; Wikimedia Commons, <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener">CC BY-SA 4.0</a></p>';
}
