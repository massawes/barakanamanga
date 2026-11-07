<?php
/**
 * Colour cards used across the site (homepage categories, About Us and every
 * "choose a Form" page). Each card has a bright background colour, a big
 * title, a short text and a small drawing of a school thing (book, pencil,
 * flask, graduation cap ...). No photos of people.
 *
 * The colour comes from the card's place in the grid (orange, yellow, green,
 * sky blue, then again), see .photo-card in assets/css/style.css.
 * The drawing comes from the card key, see card_icon_name().
 */

/** Which drawing a card key gets, e.g. 'notes' => 'book'. */
function card_icon_name(string $key): string
{
    $byKey = [
        // Homepage categories
        'notes'          => 'book',
        'summary'        => 'bulb',
        'theory'         => 'exam',
        'practical'      => 'laptop',
        'software'       => 'code',
        'others'         => 'globe',
        'gallery'        => 'camera',
        // About Us
        'about-mission'  => 'target',
        'about-history'  => 'building',
        'about-students' => 'backpack',
        // Results and national exams
        'results-form2'  => 'trophy',
        'results-form4'  => 'gradcap',
        'theory-ftna'    => 'exam',
        'theory-csee'    => 'certificate',
    ];
    if (isset($byKey[$key])) {
        return $byKey[$key];
    }
    // Form cards on every page (form1, summary-form2, tasks-form3, theory-form4 ...)
    $byForm = ['form1' => 'book', 'form2' => 'pencils', 'form3' => 'flask', 'form4' => 'gradcap'];
    foreach ($byForm as $form => $icon) {
        if (str_ends_with($key, $form)) {
            return $icon;
        }
    }
    return 'book';
}

/** One flat drawing (inline SVG, 120 x 120). */
function card_icon_svg(string $name): string
{
    $n = '#1E1A4D'; // navy lines
    $r = '#E8492B'; // red
    $b = '#29ABE2'; // blue
    $y = '#FFC933'; // yellow
    $g = '#8DC63F'; // green
    $s = 'stroke="' . $n . '" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round"';

    $art = [
        'book' =>
            '<path d="M60 38Q37 26 12 32V94Q37 88 60 98Q83 88 108 94V32Q83 26 60 38Z" fill="#fff" ' . $s . '/>'
            . '<path d="M60 38V98" ' . $s . ' fill="none"/>'
            . '<path d="M22 48q14-4 28 2M22 60q14-4 28 2M22 72q14-4 28 2M70 50q14-6 28-2M70 62q14-6 28-2M70 74q14-6 28-2" stroke="' . $b . '" stroke-width="3.5" stroke-linecap="round" fill="none"/>'
            . '<path d="M84 22v22l6-5 6 5V22Z" fill="' . $r . '" ' . $s . '/>',
        'pencils' =>
            '<g transform="rotate(-35 60 60)"><rect x="49" y="10" width="22" height="100" rx="3" fill="#fff" ' . $s . '/>'
            . '<path d="M49 22h9M49 34h6M49 46h9M49 58h6M49 70h9M49 82h6M49 94h9" stroke="' . $n . '" stroke-width="3" stroke-linecap="round"/></g>'
            . '<g transform="rotate(35 60 60)"><rect x="51" y="12" width="18" height="68" fill="' . $y . '" ' . $s . '/>'
            . '<path d="M57 12v68M63 12v68" stroke="' . $n . '" stroke-width="2"/>'
            . '<rect x="51" y="4" width="18" height="10" rx="3" fill="' . $r . '" ' . $s . '/>'
            . '<path d="M51 80L60 104L69 80Z" fill="#F7D9B0" ' . $s . '/><path d="M56.5 95L60 104L63.5 95Z" fill="' . $n . '"/></g>',
        'flask' =>
            '<path d="M50 16V46L26 92a9 9 0 0 0 8 13h52a9 9 0 0 0 8-13L70 46V16Z" fill="#fff"/>'
            . '<path d="M38 72H82L92 92a7 7 0 0 1-6 10H34a7 7 0 0 1-6-10Z" fill="' . $b . '"/>'
            . '<circle cx="50" cy="88" r="5" fill="#fff"/><circle cx="66" cy="82" r="3.5" fill="#fff"/><circle cx="72" cy="94" r="3" fill="#fff"/>'
            . '<path d="M50 16V46L26 92a9 9 0 0 0 8 13h52a9 9 0 0 0 8-13L70 46V16" fill="none" ' . $s . '/>'
            . '<path d="M44 16h32" ' . $s . '/>'
            . '<circle cx="80" cy="10" r="5" fill="none" ' . $s . '/><circle cx="92" cy="22" r="3.5" fill="' . $r . '"/>',
        'gradcap' =>
            '<path d="M32 58v22c0 8 13 14 28 14s28-6 28-14V58" fill="' . $n . '"/>'
            . '<path d="M60 22L110 44L60 66L10 44Z" fill="' . $n . '" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/>'
            . '<path d="M60 44L94 54V84" stroke="' . $r . '" stroke-width="4" stroke-linecap="round" fill="none"/>'
            . '<path d="M88 84h12l-2 14h-8Z" fill="' . $r . '"/><circle cx="60" cy="44" r="5" fill="' . $r . '"/>',
        'exam' =>
            '<rect x="20" y="10" width="62" height="96" rx="6" fill="#fff" ' . $s . '/>'
            . '<path d="M32 28h38M32 42h38M32 56h26M32 70h32" stroke="' . $b . '" stroke-width="4" stroke-linecap="round"/>'
            . '<text x="34" y="96" font-family="Poppins, Arial, sans-serif" font-weight="700" font-size="22" fill="' . $r . '">A+</text>'
            . '<g transform="rotate(28 96 64)"><rect x="89" y="22" width="14" height="60" fill="' . $y . '" ' . $s . '/>'
            . '<rect x="89" y="16" width="14" height="9" rx="2" fill="' . $r . '" ' . $s . '/>'
            . '<path d="M89 82L96 100L103 82Z" fill="#F7D9B0" ' . $s . '/></g>',
        'laptop' =>
            '<rect x="22" y="22" width="76" height="54" rx="6" fill="' . $n . '"/>'
            . '<rect x="29" y="29" width="62" height="40" rx="2" fill="' . $b . '"/>'
            . '<path d="M38 40h26M38 50h40M38 60h18" stroke="#fff" stroke-width="4.5" stroke-linecap="round"/>'
            . '<path d="M10 82h100l-7 14H17Z" fill="#fff" ' . $s . '/><path d="M50 82h20" stroke="' . $n . '" stroke-width="3.5"/>',
        'code' =>
            '<rect x="12" y="14" width="96" height="68" rx="7" fill="#fff" ' . $s . '/>'
            . '<path d="M12 28h96" ' . $s . '/><circle cx="22" cy="21" r="2.5" fill="' . $r . '"/><circle cx="31" cy="21" r="2.5" fill="' . $y . '"/><circle cx="40" cy="21" r="2.5" fill="' . $g . '"/>'
            . '<path d="M44 42L32 55L44 68M76 42L88 55L76 68" stroke="' . $r . '" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
            . '<path d="M67 38L53 72" stroke="' . $b . '" stroke-width="5.5" stroke-linecap="round"/>'
            . '<path d="M50 82l-4 16h28l-4-16Z" fill="' . $n . '"/><path d="M36 102h48" stroke="' . $n . '" stroke-width="5" stroke-linecap="round"/>',
        'globe' =>
            '<circle cx="60" cy="52" r="38" fill="' . $b . '" ' . $s . '/>'
            . '<path d="M36 26c10-4 20 2 16 12s-14 6-12 16-6 14-12 8-6-30 8-36Z" fill="' . $g . '" stroke="' . $n . '" stroke-width="2.5"/>'
            . '<path d="M68 52c8-4 20-1 20 8s-8 16-14 12-12-16-6-20Z" fill="' . $g . '" stroke="' . $n . '" stroke-width="2.5"/>'
            . '<path d="M70 22c6 0 12 4 10 8s-10 2-10-8Z" fill="' . $g . '" stroke="' . $n . '" stroke-width="2.5"/>'
            . '<path d="M18 62a44 44 0 0 0 80 14" stroke="' . $n . '" stroke-width="5" stroke-linecap="round" fill="none"/>'
            . '<path d="M60 92v12M40 108h40" stroke="' . $n . '" stroke-width="6" stroke-linecap="round"/>',
        'camera' =>
            '<path d="M40 36l7-12h26l7 12" fill="' . $n . '"/>'
            . '<rect x="12" y="34" width="96" height="66" rx="12" fill="' . $n . '"/>'
            . '<circle cx="60" cy="67" r="24" fill="#fff"/><circle cx="60" cy="67" r="15" fill="' . $b . '"/>'
            . '<circle cx="54" cy="61" r="5" fill="#fff"/>'
            . '<rect x="86" y="42" width="14" height="8" rx="2" fill="' . $r . '"/><rect x="20" y="42" width="12" height="6" rx="2" fill="#fff"/>',
        'bulb' =>
            '<path d="M60 18a30 30 0 0 0-18 54c4 3 6 8 6 12h24c0-4 2-9 6-12a30 30 0 0 0-18-54Z" fill="#fff" ' . $s . '/>'
            . '<path d="M50 70l5-16 5 10 5-10 5 16" stroke="' . $r . '" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
            . '<rect x="47" y="86" width="26" height="8" rx="2" fill="' . $n . '"/><rect x="50" y="97" width="20" height="8" rx="3" fill="' . $n . '"/>'
            . '<path d="M60 3v7M22 20l6 6M98 20l-6 6M8 48h9M112 48h-9" stroke="' . $n . '" stroke-width="4.5" stroke-linecap="round"/>',
        'clipboard' =>
            '<rect x="24" y="18" width="72" height="92" rx="8" fill="#fff" ' . $s . '/>'
            . '<rect x="44" y="10" width="32" height="16" rx="4" fill="' . $n . '"/>'
            . '<path d="M36 46l5 5 9-10M36 68l5 5 9-10M36 90l5 5 9-10" stroke="' . $g . '" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
            . '<path d="M58 48h26M58 70h26M58 92h18" stroke="' . $n . '" stroke-width="4" stroke-linecap="round"/>',
        'target' =>
            '<circle cx="54" cy="66" r="42" fill="#fff" ' . $s . '/><circle cx="54" cy="66" r="29" fill="' . $r . '"/>'
            . '<circle cx="54" cy="66" r="17" fill="#fff"/><circle cx="54" cy="66" r="7" fill="' . $n . '"/>'
            . '<path d="M54 66L100 20" stroke="' . $n . '" stroke-width="5" stroke-linecap="round"/>'
            . '<path d="M96 24l2-16 14 2-2 14Z" fill="' . $b . '" ' . $s . '/>',
        'building' =>
            '<path d="M60 10L108 38H12Z" fill="' . $r . '" ' . $s . '/>'
            . '<circle cx="60" cy="28" r="6" fill="#fff" stroke="' . $n . '" stroke-width="2.5"/>'
            . '<rect x="20" y="38" width="80" height="60" fill="#fff" ' . $s . '/>'
            . '<rect x="29" y="50" width="16" height="16" rx="2" fill="' . $b . '"/><rect x="75" y="50" width="16" height="16" rx="2" fill="' . $b . '"/>'
            . '<rect x="29" y="74" width="16" height="14" rx="2" fill="' . $b . '"/><rect x="75" y="74" width="16" height="14" rx="2" fill="' . $b . '"/>'
            . '<path d="M51 98V70a9 9 0 0 1 18 0v28Z" fill="' . $n . '"/>'
            . '<rect x="10" y="98" width="100" height="10" rx="3" fill="' . $n . '"/>',
        'backpack' =>
            '<path d="M46 30v-6a14 14 0 0 1 28 0v6" stroke="' . $n . '" stroke-width="5" fill="none" stroke-linecap="round"/>'
            . '<rect x="26" y="28" width="68" height="82" rx="18" fill="' . $b . '" ' . $s . '/>'
            . '<path d="M26 56h68" ' . $s . '/>'
            . '<rect x="38" y="68" width="44" height="32" rx="8" fill="#fff" ' . $s . '/>'
            . '<path d="M38 80h44" ' . $s . '/><rect x="56" y="76" width="8" height="8" rx="2" fill="' . $y . '" stroke="' . $n . '" stroke-width="2"/>',
        'trophy' =>
            '<path d="M38 22H22v8a16 16 0 0 0 18 16M82 22h16v8a16 16 0 0 1-18 16" stroke="' . $n . '" stroke-width="5" fill="none" stroke-linecap="round"/>'
            . '<path d="M36 14h48v28a24 24 0 0 1-48 0Z" fill="#fff" ' . $s . '/>'
            . '<path d="M60 22l4.5 9 10 1.5-7 7 1.5 10L60 45l-9 4.5 1.5-10-7-7 10-1.5Z" fill="' . $r . '"/>'
            . '<rect x="54" y="66" width="12" height="14" fill="' . $n . '"/>'
            . '<rect x="40" y="80" width="40" height="10" rx="2" fill="' . $n . '"/><rect x="32" y="92" width="56" height="16" rx="3" fill="' . $n . '"/>',
        'certificate' =>
            '<rect x="8" y="16" width="86" height="64" rx="5" fill="#fff" ' . $s . '/>'
            . '<rect x="15" y="23" width="72" height="50" rx="3" fill="none" stroke="' . $b . '" stroke-width="2.5"/>'
            . '<path d="M28 38h46M28 50h46M28 62h24" stroke="' . $n . '" stroke-width="4" stroke-linecap="round"/>'
            . '<path d="M80 84l-8 26 12-6 8 9 4-24" fill="' . $b . '" ' . $s . '/>'
            . '<path d="M96 84l8 26-12-6-8 9-4-24" fill="' . $b . '" ' . $s . '/>'
            . '<circle cx="88" cy="78" r="16" fill="' . $r . '" ' . $s . '/>'
            . '<path d="M88 69l3 6 6.5 1-4.7 4.5 1.1 6.5L88 84l-5.9 3 1.1-6.5L78.5 76l6.5-1Z" fill="#fff"/>',
    ];

    return '<svg class="photo-card-art" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
        . ($art[$name] ?? $art['book']) . '</svg>';
}

/**
 * One colour card: big title, text, an optional small count line
 * ("14 files"), a blue button and a drawing at the bottom right.
 */
function render_photo_card(string $href, string $iconKey, string $title, string $text, ?string $count, string $linkText): string
{
    return '<a href="' . e($href) . '" class="photo-card">'
        . '<span class="photo-card-title">' . e($title) . '</span>'
        . '<span class="photo-card-text">' . e($text) . '</span>'
        . ($count !== null ? '<span class="photo-card-count">' . e($count) . '</span>' : '')
        . '<span class="photo-card-foot">'
        . '<span class="photo-card-link">' . e($linkText)
        . '<svg class="photo-card-chevrons" viewBox="0 0 20 16" aria-hidden="true" focusable="false"><path d="M3 2l6 6-6 6M11 2l6 6-6 6" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        . '</span>'
        . card_icon_svg(card_icon_name($iconKey))
        . '</span></a>';
}

/** "3 files", "1 file" … */
function count_label(int $count, string $word): string
{
    return $count . ' ' . $word . ($count === 1 ? '' : 's');
}

/**
 * Used to print the photo credits under the cards. The cards have no
 * photos now, so it prints nothing (kept so older pages still work).
 */
function render_card_photo_credits(array $keys): string
{
    return '';
}
