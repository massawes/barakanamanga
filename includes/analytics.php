<?php
/**
 * TAKWIMU ZA WAGENI (visitor statistics) — owner 2026-11-26.
 *
 * FAILI HILI NI MOJA, NAKALA SAWA kwenye mifumo yote 4 (badilisha moja, nakili kwa mingine):
 *   barakanamanga   includes/analytics.php   (tovuti kuu + masomo)   -> admin/statistics.php
 *   namanga-results app/analytics.php                                -> staff/visitors.php
 *   namanga-form1   app/analytics.php                                -> visitors.php
 *   namanga-ssms    includes/analytics.php                           -> pages/visitors.php
 *   vicoba (Kikoba) app/Support/analytics.php                        -> /kikoba/msimamizi/takwimu
 *
 * Kila mfumo unaandika kila ukurasa unaofunguliwa kwenye database MOJA `site_analytics`, jedwali `hits`.
 * VPS: nenosiri la database liko /etc/namanga-analytics.php (si kwenye git). Local XAMPP: root bila nenosiri.
 * Hakuna IP inayohifadhiwa. Robots (Google bot, WhatsApp preview, curl ...) hazihesabiwi.
 * Kosa lolote hapa halivunji mfumo: kila kitu kiko ndani ya try/catch.
 */

const NVA_TZ = 'Africa/Dar_es_Salaam';
const NVA_SITES = [
    'website' => 'Tovuti ya Shule',
    'masomo'  => 'Masomo (Student Portal)',
    'results' => 'Matokeo (Results)',
    'form1'   => 'Kidato cha Kwanza',
    'ssms'    => 'SSMS',
    'kikoba'  => 'Kikoba (VICOBA)',
];

function nva_db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }
    $pdo = null;
    $c = ['host' => 'localhost', 'db' => 'site_analytics', 'user' => 'root', 'pass' => ''];
    $live = is_file('/etc/namanga-analytics.php');
    if ($live) {
        $c = array_merge($c, (array) (include '/etc/namanga-analytics.php'));
    }
    $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_TIMEOUT => 2];
    try {
        try {
            $pdo = new PDO("mysql:host={$c['host']};dbname={$c['db']};charset=utf8mb4", $c['user'], $c['pass'], $opt);
        } catch (PDOException $e) {
            if ($live || (int) ($e->errorInfo[1] ?? 0) !== 1049) {
                throw $e;
            }
            // Local tu: database haipo bado -> iunde.
            $pdo = new PDO("mysql:host={$c['host']};charset=utf8mb4", $c['user'], $c['pass'], $opt);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$c['db']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$c['db']}`");
        }
        $pdo->exec("SET time_zone = '+03:00'");
    } catch (Throwable $e) {
        error_log('analytics db: ' . $e->getMessage());
        $pdo = null;
    }
    return $pdo;
}

function nva_create_table(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS hits (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        site VARCHAR(20) NOT NULL,
        host VARCHAR(100) NOT NULL DEFAULT '',
        path VARCHAR(191) NOT NULL DEFAULT '',
        visitor CHAR(16) NOT NULL,
        is_new TINYINT(1) NOT NULL DEFAULT 0,
        visit_start TINYINT(1) NOT NULL DEFAULT 0,
        ref_host VARCHAR(100) NULL,
        device VARCHAR(10) NOT NULL DEFAULT '',
        browser VARCHAR(20) NOT NULL DEFAULT '',
        os VARCHAR(20) NOT NULL DEFAULT '',
        user_label VARCHAR(80) NULL,
        created_at DATETIME NOT NULL,
        KEY site_time (site, created_at),
        KEY time_only (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function nva_now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone(NVA_TZ));
}

/** Je, ni robot / programu (si mtu)? */
function nva_is_bot(string $ua): bool
{
    return $ua === '' || (bool) preg_match('~bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp/|telegram|curl|wget|python|go-http|java/|okhttp|axios|node-fetch|libwww|httpclient|headless|lighthouse|pagespeed|monitor|uptime|scan|check|feed|validator|php/|guzzle~i', $ua);
}

function nva_device(string $ua): array
{
    $device = preg_match('~iPad|Tablet|Android(?!.*Mobile)~i', $ua) ? 'tablet'
        : (preg_match('~Mobile|iPhone|iPod|Opera Mini|KAIOS|IEMobile|Android~i', $ua) ? 'mobile' : 'desktop');
    $browser = match (true) {
        (bool) preg_match('~Edg/|Edge/~', $ua) => 'Edge',
        (bool) preg_match('~OPR/|Opera~', $ua) => 'Opera',
        (bool) preg_match('~SamsungBrowser~', $ua) => 'Samsung',
        (bool) preg_match('~UCBrowser~', $ua) => 'UC Browser',
        (bool) preg_match('~Firefox|FxiOS~', $ua) => 'Firefox',
        (bool) preg_match('~Chrome|CriOS~', $ua) => 'Chrome',
        (bool) preg_match('~Safari~', $ua) => 'Safari',
        default => 'Nyingine',
    };
    $os = match (true) {
        (bool) preg_match('~KAIOS~i', $ua) => 'KaiOS',
        (bool) preg_match('~Android~', $ua) => 'Android',
        (bool) preg_match('~iPhone|iPad|iPod~', $ua) => 'iPhone/iPad',
        (bool) preg_match('~Windows~', $ua) => 'Windows',
        (bool) preg_match('~Mac OS X~', $ua) => 'Mac',
        (bool) preg_match('~CrOS~', $ua) => 'ChromeOS',
        (bool) preg_match('~Linux~', $ua) => 'Linux',
        default => 'Nyingine',
    };
    return [$device, $browser, $os];
}

/**
 * Ita mapema kwenye bootstrap (KABLA ya output yoyote, kwa sababu ya cookie).
 * $site  = ufunguo kutoka NVA_SITES.
 * $opt['skip'] = regex ya njia (path) zisizohesabiwa, mf. '~/admin/~'.
 * $opt['user'] = function inayorudisha jina la aliyeingia (au null). Inaitwa mwishoni mwa ukurasa.
 */
function nva_track(string $site, array $opt = []): void
{
    try {
        if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            return;
        }
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        if (nva_is_bot($ua) || !str_contains($accept, 'text/html')
            || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || preg_match('~prefetch|prerender~i', (string) ($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? ''))) {
            return;
        }
        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        if (!empty($opt['skip']) && preg_match($opt['skip'], $path)) {
            return;
        }
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $now = nva_now();
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        // Mgeni: cookie ya miaka 2. Bila cookie: kitambulisho cha siku (IP+kivinjari, hakihifadhi IP yenyewe).
        $visitor = (string) ($_COOKIE['nva'] ?? '');
        $isNew = 0;
        if (!preg_match('/^[a-f0-9]{16}$/', $visitor)) {
            $visitor = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $ua . '|' . $now->format('Y-m-d') . '|nva'), 0, 16);
            $isNew = 1;
        }
        // Ziara (visit): mpya kama hakuna shughuli kwa dakika 30.
        $visitStart = empty($_COOKIE['nva_s']) ? 1 : 0;
        if (!headers_sent()) {
            $c = ['path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax'];
            setcookie('nva', $visitor, $c + ['expires' => time() + 730 * 86400]);
            setcookie('nva_s', '1', $c + ['expires' => time() + 1800]);
        }

        $ref = null;
        $refHost = strtolower((string) parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
        if ($refHost !== '' && $refHost !== $host && !str_ends_with($refHost, 'namangasecondary.sc.tz')
            && !str_contains($refHost, '185-205-209-83') && !in_array($refHost, ['localhost', '127.0.0.1'], true)) {
            $ref = substr($refHost, 0, 100);
        }
        [$device, $browser, $os] = nva_device($ua);
        $row = [$site, substr($host, 0, 100), substr($path, 0, 191), $visitor, $isNew, $visitStart, $ref, $device, $browser, $os];
        $userFn = $opt['user'] ?? null;

        register_shutdown_function(function () use ($row, $userFn, $now): void {
            try {
                if (http_response_code() !== 200) {
                    return;
                }
                foreach (headers_list() as $h) {
                    if (stripos($h, 'content-type:') === 0 && stripos($h, 'text/html') === false) {
                        return; // faili (PDF, picha, JSON): si ukurasa
                    }
                }
                $user = null;
                if ($userFn) {
                    try {
                        $user = $userFn();
                    } catch (Throwable $e) {
                    }
                }
                $db = nva_db();
                if (!$db) {
                    return;
                }
                $row[] = $user !== null && $user !== '' ? mb_substr((string) $user, 0, 80) : null;
                $row[] = $now->format('Y-m-d H:i:s');
                $sql = 'INSERT INTO hits (site, host, path, visitor, is_new, visit_start, ref_host, device, browser, os, user_label, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
                try {
                    $db->prepare($sql)->execute($row);
                } catch (PDOException $e) {
                    if (($e->errorInfo[0] ?? '') !== '42S02') {
                        throw $e;
                    }
                    nva_create_table($db);
                    $db->prepare($sql)->execute($row);
                }
                if (mt_rand(1, 500) === 1) { // usafi: data ya zaidi ya miezi 26 inafutwa
                    $db->exec('DELETE FROM hits WHERE created_at < NOW() - INTERVAL 26 MONTH LIMIT 5000');
                }
            } catch (Throwable $e) {
                error_log('analytics: ' . $e->getMessage());
            }
        });
    } catch (Throwable $e) {
        error_log('analytics: ' . $e->getMessage());
    }
}

/* ======================================================================
 * UKURASA WA TAKWIMU (admin)
 * ====================================================================== */

function nva_h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function nva_n(int $n): string
{
    return number_format($n);
}

/** Mgeni alitoka wapi (kutoka jina la tovuti iliyomleta). */
function nva_source(?string $h): string
{
    if ($h === null || $h === '') {
        return 'Moja kwa moja (aliandika link / WhatsApp / SMS)';
    }
    $map = [
        'google' => 'Google', 'facebook' => 'Facebook', 'fb.' => 'Facebook', 'instagram' => 'Instagram',
        'whatsapp' => 'WhatsApp', 'bing' => 'Bing', 'yahoo' => 'Yahoo', 'duckduckgo' => 'DuckDuckGo', 'yandex' => 'Yandex',
        'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn', 't.co' => 'X (Twitter)', 'twitter' => 'X (Twitter)',
        'x.com' => 'X (Twitter)', 'chatgpt' => 'ChatGPT', 'openai' => 'ChatGPT', 'freepage.cc' => 'Tovuti ya zamani (freepage.cc)',
        'telegram' => 'Telegram', 't.me' => 'Telegram',
    ];
    foreach ($map as $k => $name) {
        if (str_contains($h, $k)) {
            return $name;
        }
    }
    return $h;
}

function nva_q(PDO $db, string $sql, array $p): array
{
    $st = $db->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
}

function nva_v(PDO $db, string $sql, array $p): int
{
    $st = $db->prepare($sql);
    $st->execute($p);
    return (int) $st->fetchColumn();
}

/**
 * Inarudisha HTML ya ukurasa mzima wa takwimu.
 * $allowed = funguo za NVA_SITES ambazo mtumiaji huyu anaruhusiwa kuona. Zaidi ya moja -> kuna kichupo "Zote".
 */
function nva_render(array $allowed): string
{
    $db = nva_db();
    if (!$db) {
        return '<div class="nva"><p class="nva-box">Takwimu hazipatikani sasa hivi (database ya takwimu haijaunganishwa).</p></div>' . nva_css();
    }
    try {
        nva_create_table($db);
    } catch (Throwable $e) {
    }

    $tabs = [];
    foreach ($allowed as $k) {
        $tabs[$k] = NVA_SITES[$k] ?? $k;
    }
    if (count($tabs) > 1) {
        $tabs = ['all' => 'Mifumo yote'] + $tabs;
    }
    $site = (string) ($_GET['mf'] ?? '');
    if (!isset($tabs[$site])) {
        $site = (string) array_key_first($tabs);
    }
    $sites = $site === 'all' ? $allowed : [$site];
    $in = implode(',', array_fill(0, count($sites), '?'));
    $W = "site IN ($in)";

    $now = nva_now();
    $today = $now->format('Y-m-d');
    $monthStart = $now->format('Y-m-01');
    $m = (string) ($_GET['m'] ?? '');
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m) || $m > $now->format('Y-m')) {
        $m = $now->format('Y-m');
    }
    $selFrom = $m . '-01 00:00:00';
    $selTo = (new DateTimeImmutable($m . '-01'))->modify('+1 month')->format('Y-m-d 00:00:00');
    $prevM = (new DateTimeImmutable($m . '-01'))->modify('-1 month')->format('Y-m');
    $nextM = (new DateTimeImmutable($m . '-01'))->modify('+1 month')->format('Y-m');
    $lastMonthFrom = (new DateTimeImmutable($monthStart))->modify('-1 month')->format('Y-m-d 00:00:00');

    $link = fn (array $p) => '?' . nva_h(http_build_query(array_merge(['mf' => $site, 'm' => $m], $p)));
    $count = function (string $from, ?string $to = null) use ($db, $W, $sites): array {
        $sql = "SELECT COUNT(DISTINCT visitor) v, SUM(visit_start) s, COUNT(*) p, SUM(is_new) n FROM hits WHERE $W AND created_at >= ?" . ($to ? ' AND created_at < ?' : '');
        $r = nva_q($db, $sql, array_merge($sites, [$from], $to ? [$to] : []))[0] ?? [];
        return ['v' => (int) ($r['v'] ?? 0), 's' => (int) ($r['s'] ?? 0), 'p' => (int) ($r['p'] ?? 0), 'n' => (int) ($r['n'] ?? 0)];
    };

    $online = nva_v($db, "SELECT COUNT(DISTINCT visitor) FROM hits WHERE $W AND created_at >= ?", array_merge($sites, [$now->modify('-5 minutes')->format('Y-m-d H:i:s')]));
    $cToday = $count($today . ' 00:00:00');
    $cYest = $count($now->modify('-1 day')->format('Y-m-d 00:00:00'), $today . ' 00:00:00');
    $cMonth = $count($monthStart . ' 00:00:00');
    $cLast = $count($lastMonthFrom, $monthStart . ' 00:00:00');
    $cAll = $count('2000-01-01 00:00:00');
    $first = nva_q($db, "SELECT MIN(created_at) f FROM hits WHERE $W", $sites)[0]['f'] ?? null;

    $monthNames = ['01' => 'Januari', '02' => 'Februari', '03' => 'Machi', '04' => 'Aprili', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Julai', '08' => 'Agosti', '09' => 'Septemba', '10' => 'Oktoba', '11' => 'Novemba', '12' => 'Desemba'];
    $mName = fn (string $ym) => $monthNames[substr($ym, 5, 2)] . ' ' . substr($ym, 0, 4);

    ob_start();
    echo nva_css();
    echo '<div class="nva">';

    // Vichupo vya mifumo
    if (count($tabs) > 1) {
        echo '<nav class="nva-tabs">';
        foreach ($tabs as $k => $label) {
            echo '<a class="' . ($k === $site ? 'on' : '') . '" href="' . $link(['mf' => $k]) . '">' . nva_h($label) . '</a>';
        }
        echo '</nav>';
    }

    // Kadi za juu
    $cards = [
        ['Wako mtandaoni sasa', $online, 'dakika 5 zilizopita', 'live'],
        ['Wageni leo', $cToday['v'], nva_n($cToday['p']) . ' kurasa zimefunguliwa', ''],
        ['Wageni jana', $cYest['v'], nva_n($cYest['p']) . ' kurasa', ''],
        ['Wageni mwezi huu', $cMonth['v'], nva_n($cMonth['s']) . ' ziara · ' . nva_n($cMonth['p']) . ' kurasa', 'gold'],
        ['Wageni mwezi uliopita', $cLast['v'], nva_n($cLast['s']) . ' ziara · ' . nva_n($cLast['p']) . ' kurasa', ''],
        ['Wageni wote', $cAll['v'], $first ? 'tangu ' . date('d/m/Y', strtotime($first)) : 'bado hakuna', ''],
    ];
    echo '<div class="nva-cards">';
    foreach ($cards as [$label, $num, $sub, $cls]) {
        echo '<div class="nva-card ' . $cls . '"><span class="l">' . ($cls === 'live' ? '<i class="dot"></i>' : '') . nva_h($label)
            . '</span><b>' . nva_n((int) $num) . '</b><small>' . nva_h($sub) . '</small></div>';
    }
    echo '</div>';

    // Ulinganisho wa mifumo (kichupo "Zote")
    if ($site === 'all') {
        $rows = nva_q($db, "SELECT site, COUNT(DISTINCT CASE WHEN created_at >= ? THEN visitor END) t,
                COUNT(DISTINCT CASE WHEN created_at >= ? THEN visitor END) mv,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) mp, COUNT(DISTINCT visitor) av
            FROM hits WHERE $W AND created_at >= ? GROUP BY site",
            array_merge([$today . ' 00:00:00', $monthStart . ' 00:00:00', $monthStart . ' 00:00:00'], $sites, [$lastMonthFrom]));
        $by = array_column($rows, null, 'site');
        echo '<section class="nva-box"><h3>Kila mfumo</h3><div class="nva-scroll"><table class="nva-t"><thead><tr><th>Mfumo</th><th>Wageni leo</th><th>Wageni mwezi huu</th><th>Kurasa mwezi huu</th></tr></thead><tbody>';
        foreach ($allowed as $k) {
            $r = $by[$k] ?? [];
            echo '<tr><td><a href="' . $link(['mf' => $k]) . '">' . nva_h(NVA_SITES[$k] ?? $k) . '</a></td><td>' . nva_n((int) ($r['t'] ?? 0))
                . '</td><td>' . nva_n((int) ($r['mv'] ?? 0)) . '</td><td>' . nva_n((int) ($r['mp'] ?? 0)) . '</td></tr>';
        }
        echo '</tbody></table></div></section>';
    }

    // Chati: siku 30 zilizopita
    $from30 = $now->modify('-29 days')->format('Y-m-d');
    $daily = [];
    foreach (nva_q($db, "SELECT DATE(created_at) d, COUNT(DISTINCT visitor) v, COUNT(*) p FROM hits WHERE $W AND created_at >= ? GROUP BY DATE(created_at)", array_merge($sites, [$from30 . ' 00:00:00'])) as $r) {
        $daily[$r['d']] = [(int) $r['v'], (int) $r['p']];
    }
    $days = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = $now->modify("-$i days")->format('Y-m-d');
        $days[$d] = $daily[$d] ?? [0, 0];
    }
    echo '<section class="nva-box"><h3>Wageni kila siku (siku 30 zilizopita)</h3>' . nva_chart($days)
        . '<p class="nva-legend"><span><i class="sw b"></i>Wageni</span><span><i class="sw a"></i>Kurasa zilizofunguliwa</span></p></section>';

    // Jedwali: miezi 12
    $from12 = (new DateTimeImmutable($monthStart))->modify('-11 months')->format('Y-m-d 00:00:00');
    $monthly = [];
    foreach (nva_q($db, "SELECT DATE_FORMAT(created_at, '%Y-%m') m, COUNT(DISTINCT visitor) v, SUM(is_new) n, SUM(visit_start) s, COUNT(*) p
            FROM hits WHERE $W AND created_at >= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m')", array_merge($sites, [$from12])) as $r) {
        $monthly[$r['m']] = $r;
    }
    $maxV = max(1, ...array_values(array_map(fn ($r) => (int) $r['v'], $monthly ?: [['v' => 1]])));
    echo '<section class="nva-box"><h3>Kila mwezi (miezi 12)</h3><div class="nva-scroll"><table class="nva-t"><thead><tr><th>Mwezi</th><th>Wageni</th><th>Wageni wapya</th><th>Ziara</th><th>Kurasa</th><th class="wide"></th></tr></thead><tbody>';
    for ($i = 0; $i < 12; $i++) {
        $ym = (new DateTimeImmutable($monthStart))->modify("-$i months")->format('Y-m');
        $r = $monthly[$ym] ?? ['v' => 0, 'n' => 0, 's' => 0, 'p' => 0];
        $pct = round(100 * (int) $r['v'] / $maxV);
        echo '<tr' . ($ym === $m ? ' class="sel"' : '') . '><td><a href="' . $link(['m' => $ym]) . '">' . nva_h($mName($ym)) . '</a></td><td><b>' . nva_n((int) $r['v'])
            . '</b></td><td>' . nva_n((int) $r['n']) . '</td><td>' . nva_n((int) $r['s']) . '</td><td>' . nva_n((int) $r['p'])
            . '</td><td class="wide"><span class="bar" style="width:' . $pct . '%"></span></td></tr>';
    }
    echo '</tbody></table></div><p class="nva-note">Bofya jina la mwezi kuona undani wake hapa chini.</p></section>';

    // Undani wa mwezi uliochaguliwa
    $p = array_merge($sites, [$selFrom, $selTo]);
    $M = "$W AND created_at >= ? AND created_at < ?";
    echo '<div class="nva-month"><a class="nva-btn" href="' . $link(['m' => $prevM]) . '">&lsaquo; ' . nva_h($mName($prevM)) . '</a><h2>' . nva_h($mName($m)) . '</h2>'
        . ($nextM <= $now->format('Y-m') ? '<a class="nva-btn" href="' . $link(['m' => $nextM]) . '">' . nva_h($mName($nextM)) . ' &rsaquo;</a>' : '<span></span>') . '</div>';

    $lists = [];
    $lists[] = ['Kurasa zinazotembelewa zaidi', ['Ukurasa', 'Mara', 'Wageni'],
        array_map(fn ($r) => [$r['path'] . ($site === 'all' ? '  · ' . (NVA_SITES[$r['site']] ?? $r['site']) : ''), $r['p'], $r['v']],
            nva_q($db, "SELECT site, path, COUNT(*) p, COUNT(DISTINCT visitor) v FROM hits WHERE $M GROUP BY site, path ORDER BY p DESC LIMIT 15", $p))];
    $src = [];
    foreach (nva_q($db, "SELECT ref_host, COUNT(*) s, COUNT(DISTINCT visitor) v FROM hits WHERE $M AND visit_start = 1 GROUP BY ref_host", $p) as $r) {
        $k = nva_source($r['ref_host']);
        $src[$k] = [$k, ($src[$k][1] ?? 0) + (int) $r['s'], ($src[$k][2] ?? 0) + (int) $r['v']];
    }
    usort($src, fn ($a, $b) => $b[1] <=> $a[1]);
    $lists[] = ['Wanatoka wapi', ['Chanzo', 'Ziara', 'Wageni'], array_slice($src, 0, 12)];
    $dev = ['mobile' => 'Simu', 'tablet' => 'Tablet', 'desktop' => 'Kompyuta'];
    $lists[] = ['Vifaa', ['Kifaa', 'Wageni', 'Kurasa'],
        array_map(fn ($r) => [$dev[$r['device']] ?? $r['device'], $r['v'], $r['p']],
            nva_q($db, "SELECT device, COUNT(DISTINCT visitor) v, COUNT(*) p FROM hits WHERE $M GROUP BY device ORDER BY v DESC", $p))];
    $lists[] = ['Mfumo wa simu/kompyuta', ['OS', 'Wageni', 'Kurasa'],
        array_map(fn ($r) => [$r['os'], $r['v'], $r['p']], nva_q($db, "SELECT os, COUNT(DISTINCT visitor) v, COUNT(*) p FROM hits WHERE $M GROUP BY os ORDER BY v DESC LIMIT 10", $p))];
    $lists[] = ['Browser', ['Browser', 'Wageni', 'Kurasa'],
        array_map(fn ($r) => [$r['browser'], $r['v'], $r['p']], nva_q($db, "SELECT browser, COUNT(DISTINCT visitor) v, COUNT(*) p FROM hits WHERE $M GROUP BY browser ORDER BY v DESC LIMIT 10", $p))];
    $users = nva_q($db, "SELECT user_label, site, COUNT(*) p, MAX(created_at) last FROM hits WHERE $M AND user_label IS NOT NULL GROUP BY user_label, site ORDER BY p DESC LIMIT 20", $p);
    if ($users) {
        $lists[] = ['Watumiaji walioingia (akaunti)', ['Akaunti', 'Kurasa', 'Mara ya mwisho'],
            array_map(fn ($r) => [$r['user_label'] . ($site === 'all' ? '  · ' . (NVA_SITES[$r['site']] ?? $r['site']) : ''), $r['p'], date('d/m/Y H:i', strtotime($r['last']))], $users)];
    }
    $hours = array_fill(0, 24, 0);
    foreach (nva_q($db, "SELECT HOUR(created_at) h, COUNT(*) p FROM hits WHERE $M GROUP BY HOUR(created_at)", $p) as $r) {
        $hours[(int) $r['h']] = (int) $r['p'];
    }

    echo '<div class="nva-grid">';
    foreach ($lists as [$title, $head, $rows]) {
        $max = max(1, ...array_values(array_map(fn ($r) => (int) $r[1], $rows ?: [[0, 1]])));
        echo '<section class="nva-box"><h3>' . nva_h($title) . '</h3>';
        if (!$rows) {
            echo '<p class="nva-note">Hakuna data kwa mwezi huu.</p></section>';
            continue;
        }
        echo '<table class="nva-t nva-list"><thead><tr><th>' . nva_h($head[0]) . '</th><th>' . nva_h($head[1]) . '</th><th>' . nva_h($head[2]) . '</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $pct = round(100 * (int) $r[1] / $max);
            echo '<tr><td><span class="lb" style="background-size:' . $pct . '% 100%">' . nva_h($r[0]) . '</span></td><td>' . nva_n((int) $r[1]) . '</td><td>'
                . (is_numeric($r[2]) ? nva_n((int) $r[2]) : nva_h($r[2])) . '</td></tr>';
        }
        echo '</tbody></table></section>';
    }
    echo '<section class="nva-box"><h3>Masaa yenye wageni wengi (kurasa kwa saa)</h3>' . nva_hours($hours) . '</section>';
    echo '</div>';

    echo '<p class="nva-note">Wageni = watu tofauti (kila simu/kompyuta inahesabiwa mara moja). Ziara = kila mara mtu anapoingia (baada ya dakika 30 bila shughuli ni ziara mpya). '
        . 'Kurasa = kila ukurasa uliofunguliwa. Robots (Google, WhatsApp preview n.k.) hazihesabiwi. Saa ni za Tanzania.</p>';
    echo '</div>';
    return (string) ob_get_clean();
}

/** Chati ya mihimili (SVG, haihitaji JavaScript). $days = ['Y-m-d' => [wageni, kurasa]]. */
function nva_chart(array $days): string
{
    $w = 760; $h = 230; $l = 40; $b = 28; $t = 12;
    $max = max(1, ...array_values(array_map(fn ($d) => $d[1], array_values($days))));
    $step = (int) ceil($max / 4);
    $mag = 10 ** max(0, strlen((string) $step) - 1);
    $step = (int) (ceil($step / $mag) * $mag);
    $top = $step * 4;
    $n = count($days);
    $cw = ($w - $l - 6) / $n;
    $y = fn ($v) => $t + ($h - $t - $b) * (1 - $v / $top);
    $s = '<svg class="nva-chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="Wageni kila siku">';
    for ($i = 0; $i <= 4; $i++) {
        $yy = round($y($step * $i), 1);
        $s .= '<line x1="' . $l . '" x2="' . $w . '" y1="' . $yy . '" y2="' . $yy . '" class="g"/><text x="' . ($l - 6) . '" y="' . ($yy + 4) . '" class="ax" text-anchor="end">' . nva_n($step * $i) . '</text>';
    }
    $pts = [];
    $i = 0;
    foreach ($days as $d => [$v, $p]) {
        $x = $l + $i * $cw;
        $bh = ($h - $t - $b) * $v / $top;
        $label = date('d/m', strtotime($d));
        $s .= '<g><title>' . $label . ': wageni ' . $v . ', kurasa ' . $p . '</title><rect x="' . round($x + $cw * 0.18, 1) . '" y="' . round($y($v), 1) . '" width="' . round($cw * 0.64, 1)
            . '" height="' . round(max($bh, $v ? 2 : 0), 1) . '" rx="3" class="bv"/><rect x="' . round($x, 1) . '" y="' . $t . '" width="' . round($cw, 1) . '" height="' . ($h - $t - $b) . '" fill="transparent"/></g>';
        $pts[] = round($x + $cw / 2, 1) . ',' . round($y($p), 1);
        if ($i % 5 === 4 || $i === $n - 1) {
            $s .= '<text x="' . round($x + $cw / 2, 1) . '" y="' . ($h - 8) . '" class="ax" text-anchor="middle">' . $label . '</text>';
        }
        $i++;
    }
    $s .= '<polyline points="' . implode(' ', $pts) . '" class="lp"/>';
    foreach ($pts as $pt) {
        [$px, $py] = explode(',', $pt);
        $s .= '<circle cx="' . $px . '" cy="' . $py . '" r="2.5" class="lpd"/>';
    }
    return $s . '</svg>';
}

function nva_hours(array $hours): string
{
    $max = max(1, ...$hours);
    $s = '<div class="nva-hours">';
    foreach ($hours as $hr => $v) {
        $s .= '<div title="Saa ' . sprintf('%02d:00', $hr) . ' - ' . nva_n($v) . ' kurasa"><span style="height:' . max(2, round(100 * $v / $max)) . '%"' . ($v ? '' : ' class="z"') . '></span><small>' . ($hr % 3 === 0 ? sprintf('%02d', $hr) : '') . '</small></div>';
    }
    return $s . '</div>';
}

function nva_css(): string
{
    return <<<'CSS'
<style>
.nva{--nb:#2f6fd6;--na:#f5a623;--line:rgba(127,140,160,.28);--soft:rgba(127,140,160,.08);font-size:15px;line-height:1.45}
.nva *{box-sizing:border-box}
.nva a{color:inherit}
.nva-tabs{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 16px}
.nva-tabs a{padding:8px 14px;border:2px solid var(--line);border-radius:10px;text-decoration:none;font-weight:600}
.nva-tabs a.on{border-color:var(--nb);color:var(--nb);background:rgba(47,111,214,.08)}
.nva-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:16px}
.nva-card{border:1px solid var(--line);border-radius:12px;padding:14px 16px;background:var(--soft);display:flex;flex-direction:column;gap:2px}
.nva-card .l{font-size:13px;font-weight:600;opacity:.75;display:flex;align-items:center;gap:6px}
.nva-card b{font-size:30px;line-height:1.15;font-weight:800}
.nva-card small{font-size:12.5px;opacity:.7}
.nva-card.gold{border-color:var(--na);box-shadow:inset 4px 0 0 var(--na)}
.nva-card.live b{color:#16a34a}
.nva .dot{width:9px;height:9px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.25);display:inline-block}
.nva-box{border:1px solid var(--line);border-radius:12px;padding:14px 16px;margin-bottom:16px;background:var(--soft);min-width:0}
.nva-box h3{margin:0 0 10px;font-size:16px;font-weight:700}
.nva-chart{width:100%;height:auto;display:block}
.nva-chart .g{stroke:var(--line);stroke-width:1}
.nva-chart .ax{font-size:11px;fill:currentColor;opacity:.65}
.nva-chart .bv{fill:var(--nb)}
.nva-chart g:hover .bv{opacity:.75}
.nva-chart .lp{fill:none;stroke:var(--na);stroke-width:2}
.nva-chart .lpd{fill:var(--na)}
.nva-legend{display:flex;gap:18px;margin:8px 0 0;font-size:13px;opacity:.85}
.nva-legend span{display:flex;align-items:center;gap:6px}
.nva .sw{width:12px;height:12px;border-radius:3px;display:inline-block}
.nva .sw.b{background:var(--nb)}.nva .sw.a{background:var(--na)}
.nva-scroll{overflow-x:auto}
.nva-t{width:100%;border-collapse:collapse;font-size:14px}
.nva-t th{text-align:left;font-size:12.5px;opacity:.7;font-weight:700;padding:6px 8px;border-bottom:1px solid var(--line);white-space:nowrap}
.nva-t td{padding:7px 8px;border-bottom:1px solid var(--line);vertical-align:middle}
.nva-t tr:last-child td{border-bottom:0}
.nva-t td:not(:first-child),.nva-t th:not(:first-child){text-align:right;white-space:nowrap}
.nva-t th.wide,.nva-t td.wide{width:30%;text-align:left}
.nva-t .bar{display:block;height:10px;border-radius:5px;background:var(--nb);min-width:2px}
.nva-t tr.sel td{background:rgba(245,166,35,.12)}
.nva-list td:first-child{word-break:break-all}
.nva-list .lb{display:inline-block;padding:1px 6px;border-radius:5px;background-image:linear-gradient(rgba(47,111,214,.16),rgba(47,111,214,.16));background-repeat:no-repeat}
.nva-month{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:8px 0 14px}
.nva-month h2{margin:0;font-size:20px;font-weight:800;text-align:center}
.nva-btn{padding:7px 12px;border:2px solid var(--nb);color:var(--nb)!important;border-radius:10px;text-decoration:none;font-weight:600;font-size:14px;white-space:nowrap}
.nva-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:0 16px}
.nva-hours{display:grid;grid-template-columns:repeat(24,1fr);gap:3px;height:140px;align-items:end}
.nva-hours div{height:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center}
.nva-hours span{width:100%;background:var(--nb);border-radius:3px 3px 0 0;display:block}
.nva-hours span.z{background:var(--line)}
.nva-hours small{font-size:10.5px;opacity:.65;height:14px}
.nva-note{font-size:12.5px;opacity:.7;margin:6px 0 0}
@media (max-width:560px){.nva-card b{font-size:24px}.nva-month h2{font-size:17px}.nva-grid{grid-template-columns:1fr}}
</style>
CSS;
}
