<?php
/**
 * Photos on the homepage resource cards and the Notes Form cards
 * (assets/images/cards/<key>.jpg). They come from Wikimedia Commons under
 * CC BY-SA 4.0, which requires crediting the photographer — see
 * render_card_photo_credits().
 */

const CARD_PHOTOS = [
    'notes'     => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Jangwani_students_during_Kiwix_Presentation.jpg'],
    'summary'   => ['Seemannaufland', 'https://commons.wikimedia.org/wiki/File:Students_in_classroom_Tanzania.JPG'],
    'theory'    => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3313IMG_3314IMG_3318.jpg'],
    'practical' => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_Practising_Kiwix_Installation_at_Jangwani_Secondary_School.jpg'],
    'software'  => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Students_Using_kiwix_to_learn.jpg'],
    'others'    => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_during_Kiwix_Presentation_at_Jangwani_Secondary_School-Tanzania.jpg'],
    'gallery'   => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Students_and_Kiwix_Team_at_Jangwani_Secondary_School.jpg'],
    'form1'     => ['Mpemba01', 'https://commons.wikimedia.org/wiki/File:Students_practising_the_Kiwix_Tool_at_Kajengwa_Primary_School.jpg'],
    'form2'     => ['Pellagia Njau', 'https://commons.wikimedia.org/wiki/File:Kiwix_Phase_2_Training_with_students_at_Benjamin_Mkapa_Secondary_School_Tanzania_IMG_3319.jpg'],
    'form3'     => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:Students_Learning_how_use_kiwix.jpg'],
    'form4'     => ['Abdulai Ibrahim', 'https://commons.wikimedia.org/wiki/File:A_trainer_guiding_students_on_how_to_use_kiwix.jpg'],
];

/** Address of a card photo, e.g. card_photo_url('notes'). */
function card_photo_url(string $key): string
{
    return base_url() . '/assets/images/cards/' . $key . '.jpg';
}

/**
 * One-line credit for the photos of $keys: each photographer once, followed
 * by numbered links to their photos' file pages.
 */
function render_card_photo_credits(array $keys): string
{
    $byAuthor = [];
    foreach ($keys as $key) {
        [$author, $page] = CARD_PHOTOS[$key];
        $byAuthor[$author][] = $page;
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
