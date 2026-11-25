<?php
/**
 * NECTA results of Namanga Secondary School (centre S2911), updated automatically (owner 2026-11-25).
 *
 * The list of years that have results is kept in uploads/.necta-results.json.
 * cron/necta-check.php (run every 3 hours by cron on the VPS) asks the NECTA website whether a newer
 * year has been published. When it finds one it adds the year and posts an announcement on the home page.
 * Nobody needs to edit any file when new results come out.
 */

const NECTA_CENTRE = 'S2911';
const NECTA_FIRST_YEAR = 2020;
const NECTA_STATE_FILE = __DIR__ . '/../uploads/.necta-results.json';

// form key => exam details. NECTA's addresses: CSEE uses "s2911.htm", FTNA uses "S2911.htm".
const NECTA_EXAMS = [
    'form4' => ['label' => 'Form 4', 'exam' => 'CSEE', 'name' => 'Certificate of Secondary Education Examination', 'folder' => 'csee', 'file' => 's2911'],
    'form2' => ['label' => 'Form 2', 'exam' => 'FTNA', 'name' => 'Form Two National Assessment', 'folder' => 'ftna', 'file' => 'S2911'],
];

// Years checked by hand on 2026-11-25 (always shown, even before the first automatic check).
const NECTA_VERIFIED_YEARS = [
    'form4' => [2022, 2023, 2024, 2025],
    'form2' => [2022, 2023, 2024, 2025],
];

function necta_result_url(string $form, int $year): string
{
    $e = NECTA_EXAMS[$form];
    return 'https://onlinesys.necta.go.tz/results/' . $year . '/' . $e['folder'] . '/results/' . $e['file'] . '.htm';
}

/** ['form4' => [years...], 'form2' => [...], 'checked_at' => timestamp|null] */
function necta_state(): array
{
    $state = ['form4' => NECTA_VERIFIED_YEARS['form4'], 'form2' => NECTA_VERIFIED_YEARS['form2'], 'checked_at' => null];
    $saved = is_file(NECTA_STATE_FILE) ? json_decode((string) file_get_contents(NECTA_STATE_FILE), true) : null;
    if (is_array($saved)) {
        foreach (array_keys(NECTA_EXAMS) as $form) {
            $state[$form] = array_values(array_unique(array_map('intval', array_merge($state[$form], (array) ($saved[$form] ?? [])))));
        }
        $state['checked_at'] = isset($saved['checked_at']) ? (int) $saved['checked_at'] : null;
    }
    foreach (array_keys(NECTA_EXAMS) as $form) {
        rsort($state[$form]);
    }
    return $state;
}

/** Years to list on the results page, newest first: up to this year, back to NECTA_FIRST_YEAR. */
function necta_years_to_show(array $state): array
{
    $newest = max((int) date('Y'), ...array_merge($state['form4'], $state['form2']));
    return range($newest, NECTA_FIRST_YEAR);
}

/** True when NECTA has published the school's page for that exam and year. */
function necta_results_published(string $form, int $year): bool
{
    $ch = curl_init(necta_result_url($form, $year));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; NamangaSecondarySchool/1.0; +https://namangasecondary.sc.tz)',
    ]);
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    // The page must really be ours (NECTA answers some missing pages with an error page).
    return $code === 200 && stripos($body, 'NAMANGA') !== false;
}

/**
 * Looks for results newer than the ones we know (this year and last year only), saves them,
 * and posts an announcement for each new one. Returns the list of new [form, year] found.
 */
function necta_check_for_new_results(PDO $pdo, ?callable $log = null): array
{
    $log = $log ?? static function (string $line): void {};
    $state = necta_state();
    $found = [];
    $thisYear = (int) date('Y');
    foreach (NECTA_EXAMS as $form => $exam) {
        foreach ([$thisYear, $thisYear - 1] as $year) {
            if (in_array($year, $state[$form], true)) {
                continue;
            }
            $published = necta_results_published($form, $year);
            $log($exam['exam'] . " $year: " . ($published ? 'PUBLISHED' : 'not yet'));
            if ($published) {
                $state[$form][] = $year;
                $found[] = [$form, $year];
            }
        }
        rsort($state[$form]);
    }
    $state['checked_at'] = time();
    file_put_contents(NECTA_STATE_FILE, json_encode($state, JSON_PRETTY_PRINT), LOCK_EX);

    foreach ($found as [$form, $year]) {
        $exam = NECTA_EXAMS[$form];
        $title = $exam['label'] . ' (' . $exam['exam'] . ') ' . $year . ' results are out';
        $exists = $pdo->prepare('SELECT COUNT(*) FROM announcements WHERE title = :t');
        $exists->execute(['t' => $title]);
        if ((int) $exists->fetchColumn() > 0) {
            continue;
        }
        $body = 'The National Examinations Council of Tanzania (NECTA) has published the ' . $exam['name'] . ' (' . $exam['exam'] . ') '
            . $year . ' results for Namanga Secondary School (' . NECTA_CENTRE . ").\n\n"
            . "Open the official results on the NECTA website:\n" . necta_result_url($form, $year) . "\n\n"
            . 'All our NECTA results: ' . SCHOOL_URL . '/results.php?form=' . $form;
        $pdo->prepare("INSERT INTO announcements (title, body, category, published_on, is_published) VALUES (:t, :b, 'news', CURDATE(), 1)")
            ->execute(['t' => $title, 'b' => $body]);
        $log("announcement posted: $title");
    }
    return $found;
}
