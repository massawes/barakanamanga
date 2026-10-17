<?php
/**
 * AI assistant helpers, used by api/chat.php and api/search_resources.php.
 *
 *  - ai_config()             real keys/settings, read only on the server
 *  - ai_rate_limit_check()   per-session and per-IP request limits
 *  - ai_search_resources()   searches the site's own files (resources table
 *                            plus the school/NECTA exam lists) and returns
 *                            only real rows with real links
 *  - ai_web_search()         optional internet search (Brave or Tavily)
 *  - ai_chat_completion()    calls the Groq chat API with cURL
 *
 * Nothing in this file is ever sent to the browser except the search
 * results and the AI's reply text. The API keys never leave the server.
 */

require_once __DIR__ . '/school-exams.php';
require_once __DIR__ . '/national-exams.php';

// ------------------------------------------------------------
// Configuration
// ------------------------------------------------------------

/**
 * Settings, merged in this order (later wins):
 *   1. the defaults below
 *   2. the PHP file named by the NAMANGA_AI_CONFIG environment variable
 *      (use this to keep the file outside the web root on your own server),
 *      otherwise config/ai.php (git-ignored, blocked by config/.htaccess)
 *   3. environment variables (GROQ_API_KEY, GROQ_MODEL, WEB_SEARCH_PROVIDER,
 *      WEB_SEARCH_API_KEY)
 * See config/ai.example.php for every setting.
 */
function ai_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $config = [
        'groq_api_key'          => '',
        // Chosen after comparing Kiswahili answers (see README "AI Assistant").
        'groq_model'            => 'openai/gpt-oss-120b',
        'groq_fallback_model'   => 'qwen/qwen3.8-27b',
        'groq_endpoint'         => 'https://api.groq.com/openai/v1/chat/completions',
        'web_search_provider'   => '',   // '', 'brave' or 'tavily'
        'web_search_api_key'    => '',
        'limit_session_per_min' => 8,
        'limit_ip_per_min'      => 30,   // a whole computer lab can share one IP
        'limit_ip_per_day'      => 600,
    ];

    $file = getenv('NAMANGA_AI_CONFIG') ?: __DIR__ . '/../config/ai.php';
    if (is_file($file)) {
        $fromFile = require $file;
        if (is_array($fromFile)) {
            $config = array_merge($config, $fromFile);
        }
    }

    $envMap = [
        'GROQ_API_KEY'        => 'groq_api_key',
        'GROQ_MODEL'          => 'groq_model',
        'WEB_SEARCH_PROVIDER' => 'web_search_provider',
        'WEB_SEARCH_API_KEY'  => 'web_search_api_key',
    ];
    foreach ($envMap as $env => $key) {
        $value = getenv($env);
        if ($value !== false && $value !== '') {
            $config[$key] = $value;
        }
    }

    $config['web_search_provider'] = strtolower(trim((string) $config['web_search_provider']));
    return $config;
}

function ai_is_configured(): bool
{
    return trim((string) ai_config()['groq_api_key']) !== '';
}

function ai_web_search_enabled(): bool
{
    $c = ai_config();
    return in_array($c['web_search_provider'], ['brave', 'tavily'], true)
        && trim((string) $c['web_search_api_key']) !== '';
}

// ------------------------------------------------------------
// Small JSON helpers for the api/ endpoints
// ------------------------------------------------------------

function ai_json_response(array $data, int $status = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    exit;
}

/** Visitor-facing text in the chosen language ('en' or 'sw'). */
function ai_text(string $key, string $lang): string
{
    static $texts = [
        'empty'       => ['en' => 'Please type a message first.',
                          'sw' => 'Tafadhali andika ujumbe kwanza.'],
        'too_long'    => ['en' => 'Your message is too long. Please keep it under 1,000 characters.',
                          'sw' => 'Ujumbe wako ni mrefu sana. Tafadhali usizidi herufi 1,000.'],
        'rate_limit'  => ['en' => 'You are sending messages too quickly. Please wait a minute and try again.',
                          'sw' => 'Unatuma ujumbe haraka sana. Tafadhali subiri dakika moja kisha ujaribu tena.'],
        'bad_request' => ['en' => 'Sorry, that request could not be understood. Please refresh the page and try again.',
                          'sw' => 'Samahani, ombi hilo halikueleweka. Tafadhali onyesha upya ukurasa na ujaribu tena.'],
        'ai_off'      => ['en' => 'The AI assistant is not available right now, but here is what I found on this website:',
                          'sw' => 'Msaidizi wa AI haupatikani kwa sasa, lakini hiki ndicho nilichopata kwenye tovuti hii:'],
        'ai_off_none' => ['en' => 'The AI assistant is not available right now, and nothing on this website matched your question. Please try other words, or browse the menu (Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Other Subjects).',
                          'sw' => 'Msaidizi wa AI haupatikani kwa sasa, na sikupata kitu kwenye tovuti hii kinacholingana na swali lako. Jaribu maneno mengine, au tumia menyu (Notes, Summary, Theory Past Papers, Form 4 Practical, Software, Other Subjects).'],
        'ai_busy'     => ['en' => 'The AI assistant is very busy right now. Please try again in a minute. Here is what I found on this website:',
                          'sw' => 'Msaidizi wa AI una kazi nyingi sasa hivi. Tafadhali jaribu tena baada ya dakika moja. Hiki ndicho nilichopata kwenye tovuti hii:'],
        'server'      => ['en' => 'Sorry, something went wrong on our side. Please try again.',
                          'sw' => 'Samahani, kuna hitilafu upande wetu. Tafadhali jaribu tena.'],
    ];
    $lang = $lang === 'sw' ? 'sw' : 'en';
    return $texts[$key][$lang] ?? $texts['server'][$lang];
}

// ------------------------------------------------------------
// Rate limiting
// ------------------------------------------------------------

function ai_client_ip(): string
{
    // REMOTE_ADDR only: X-Forwarded-For is trivially faked by the client.
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Records one request for $bucket ('chat' or 'search') and returns false
 * if the visitor is over a limit. Per-session counts live in $_SESSION;
 * per-IP counts live in a small table (only a hash of the IP is stored).
 */
function ai_rate_limit_check(PDO $pdo, string $bucket): bool
{
    $c = ai_config();
    $now = time();
    $sessionLimit = (int) $c['limit_session_per_min'] * ($bucket === 'search' ? 3 : 1);
    $ipMinLimit = (int) $c['limit_ip_per_min'] * ($bucket === 'search' ? 3 : 1);
    $ipDayLimit = (int) $c['limit_ip_per_day'] * ($bucket === 'search' ? 3 : 1);

    // ---- Per session ----
    $key = 'ai_rl_' . $bucket;
    $stamps = array_filter($_SESSION[$key] ?? [], static fn($t) => is_int($t) && $t > $now - 60);
    if (count($stamps) >= $sessionLimit) {
        return false;
    }
    $stamps[] = $now;
    $_SESSION[$key] = array_values($stamps);

    // ---- Per IP ----
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS ai_rate_limits (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_hash CHAR(64) NOT NULL,
            bucket VARCHAR(16) NOT NULL,
            created_at INT UNSIGNED NOT NULL,
            INDEX idx_ip_bucket_time (ip_hash, bucket, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $ipHash = hash('sha256', ai_client_ip() . '|namanga-ai');

    $stmt = $pdo->prepare(
        'SELECT SUM(created_at > :minute) AS last_minute, COUNT(*) AS last_day
         FROM ai_rate_limits
         WHERE ip_hash = :ip AND bucket = :bucket AND created_at > :day'
    );
    $stmt->execute(['minute' => $now - 60, 'ip' => $ipHash, 'bucket' => $bucket, 'day' => $now - 86400]);
    $row = $stmt->fetch() ?: ['last_minute' => 0, 'last_day' => 0];
    if ((int) $row['last_minute'] >= $ipMinLimit || (int) $row['last_day'] >= $ipDayLimit) {
        return false;
    }

    $stmt = $pdo->prepare('INSERT INTO ai_rate_limits (ip_hash, bucket, created_at) VALUES (:ip, :bucket, :t)');
    $stmt->execute(['ip' => $ipHash, 'bucket' => $bucket, 't' => $now]);

    // Housekeeping: now and then, drop rows older than a day.
    if (random_int(1, 50) === 1) {
        $stmt = $pdo->prepare('DELETE FROM ai_rate_limits WHERE created_at < :old');
        $stmt->execute(['old' => $now - 86400]);
    }

    return true;
}

// ------------------------------------------------------------
// Website resource search
// ------------------------------------------------------------

/**
 * Turns a question into search terms: the Form it mentions, the resource
 * types it mentions, and the remaining keywords (with Swahili/short
 * subject names expanded to the words used in file names).
 */
function ai_parse_query(string $query): array
{
    $text = mb_strtolower(trim($query), 'UTF-8');

    $formKeywords = [
        'form1' => ['form 1', 'form1', 'form one', 'kidato cha kwanza', 'kidato 1', 'f1'],
        'form2' => ['form 2', 'form2', 'form two', 'kidato cha pili', 'kidato 2', 'f2'],
        'form3' => ['form 3', 'form3', 'form three', 'kidato cha tatu', 'kidato 3', 'f3'],
        'form4' => ['form 4', 'form4', 'form four', 'kidato cha nne', 'kidato 4', 'f4'],
    ];
    $typeKeywords = [
        'notes'     => ['notes', 'note', 'maelezo', 'kitabu', 'vitabu', 'book', 'books'],
        'summary'   => ['summary', 'summaries', 'muhtasari'],
        'theory'    => ['past paper', 'past papers', 'theory', 'nadharia', 'mtihani', 'mitihani', 'exam', 'exams', 'paper', 'papers'],
        'practical' => ['practical', 'practicals', 'vitendo'],
        'software'  => ['software', 'programu', 'program', 'application'],
    ];

    $form = null;
    $types = [];
    $consumed = [];
    foreach ($formKeywords as $f => $phrases) {
        foreach ($phrases as $p) {
            if (preg_match('/(?<![\p{L}0-9])' . preg_quote($p, '/') . '(?![\p{L}0-9])/u', $text)) {
                $form = $f;
                $consumed[] = $p;
                break 2;
            }
        }
    }
    foreach ($typeKeywords as $t => $phrases) {
        foreach ($phrases as $p) {
            if (preg_match('/(?<![\p{L}0-9])' . preg_quote($p, '/') . '(?![\p{L}0-9])/u', $text)) {
                $types[] = $t;
                $consumed[] = $p;
                break;
            }
        }
    }

    $stripped = $text;
    foreach ($consumed as $p) {
        $stripped = preg_replace('/(?<![\p{L}0-9])' . preg_quote($p, '/') . '(?![\p{L}0-9])/u', ' ', $stripped);
    }

    $stopwords = [
        'the', 'a', 'an', 'is', 'are', 'do', 'does', 'you', 'have', 'has', 'for', 'me', 'my', 'please', 'can',
        'want', 'need', 'give', 'show', 'find', 'get', 'download', 'with', 'from', 'this', 'that', 'there',
        'how', 'where', 'what', 'which', 'who', 'why', 'when', 'any', 'all', 'of', 'to', 'and', 'or', 'some',
        'about', 'website', 'site', 'school', 'namanga', 'secondary', 'explain', 'tell', 'help', 'hello', 'thanks',
        'nipe', 'nina', 'nataka', 'tafadhali', 'naomba', 'ninahitaji', 'nahitaji', 'kwa', 'ya', 'za', 'na', 'wa',
        'la', 'ni', 'je', 'kuna', 'iko', 'zipo', 'vipi', 'gani', 'wapi', 'nini', 'pakua', 'tafuta', 'nisaidie',
        'habari', 'asante', 'eleza', 'nieleze', 'kuhusu', 'hii', 'hiyo', 'shule', 'tovuti', 'yote', 'zote',
        'link', 'links', 'file', 'files', 'faili', 'form', 'kidato', 'simple', 'words', 'maneno', 'rahisi',
        'name', 'jina', 'available', 'through',
    ];
    $synonyms = [
        'ict'        => ['ics', 'computer'],
        'ics'        => ['ics', 'computer'],
        'computer'   => ['computer', 'ics'],
        'kompyuta'   => ['computer', 'ics'],
        'tehama'     => ['computer', 'ics'],
        'hisabati'   => ['math', 'mathematics'],
        'maths'      => ['math', 'mathematics'],
        'math'       => ['math', 'mathematics'],
        'fizikia'    => ['physics'],
        'kemia'      => ['chemistry'],
        'baiolojia'  => ['biology'],
        'biolojia'   => ['biology'],
        'jiografia'  => ['geography', 'geog'],
        'geography'  => ['geography', 'geog'],
        'historia'   => ['history', 'historia'],
        'kiingereza' => ['english'],
        'kingereza'  => ['english'],
        'uraia'      => ['civics'],
        'biashara'   => ['business', 'commerce'],
        'chakula'    => ['food', 'nutrition'],
        'lishe'      => ['nutrition', 'food'],
        'ms'         => ['ms', 'marking'],
        'majibu'     => ['marking', 'ms', 'solutions'],
        'solutions'  => ['marking', 'ms', 'solutions'],
    ];

    $keywords = [];
    foreach (preg_split('/[^\p{L}0-9]+/u', $stripped, -1, PREG_SPLIT_NO_EMPTY) as $word) {
        if (isset($synonyms[$word])) {
            array_push($keywords, ...$synonyms[$word]);
            continue;
        }
        if (mb_strlen($word) < 3 || in_array($word, $stopwords, true) || ctype_digit($word) && mb_strlen($word) !== 4) {
            continue;
        }
        $keywords[] = $word;
    }

    // Is the visitor actually looking for a file? ("Explain photosynthesis"
    // is not; "notes za form 2" or "where can I download…" is.)
    $wantsFiles = $types !== [] || (bool) preg_match(
        '/(?<![\p{L}])(download|pakua|file|files|faili|link|links|resource|resources|find|search|tafuta|nipe|give me|do you have|is there|are there|where can|kuna|zipo|iko wapi|nitapata wapi)(?![\p{L}])/u',
        $text
    );

    return [
        'form'        => $form,
        'types'       => array_values(array_unique($types)),
        'keywords'    => array_slice(array_values(array_unique($keywords)), 0, 6),
        'wants_files' => $wantsFiles,
    ];
}

/** True when a chat message is asking for files rather than an explanation. */
function ai_wants_resources(string $message): bool
{
    return ai_parse_query($message)['wants_files'];
}

/**
 * Searches the website's own files. Returns at most $limit items, each:
 *   title, description, type, form, year, view_url, download_url, source
 * Every URL is built exactly the way the site's own pages build it, so
 * nothing here can point anywhere that does not exist on the site.
 */
function ai_search_resources(PDO $pdo, string $query, int $limit = 6): array
{
    $q = ai_parse_query($query);
    if ($q['keywords'] === [] && $q['types'] === [] && $q['form'] === null) {
        return [];
    }

    $results = [];
    $base = base_url();

    // ---- 1. The resources table (uploaded files) ----
    if ($q['keywords'] !== [] || $q['types'] !== [] || $q['form'] !== null) {
        $conditions = [];
        $params = [];
        $score = [];

        if ($q['types'] !== []) {
            $ph = [];
            foreach ($q['types'] as $i => $type) {
                $ph[] = ":type$i";
                $params["type$i"] = $type;
            }
            $conditions[] = 'resource_type IN (' . implode(',', $ph) . ')';
        }
        if ($q['form'] !== null) {
            $conditions[] = 'form_level = :form';
            $params['form'] = $q['form'];
        }
        if ($q['keywords'] !== []) {
            $kw = [];
            foreach ($q['keywords'] as $i => $word) {
                // Subjects are not a column: they show up in the title,
                // the description or the folder name inside file_path.
                $kw[] = "(title LIKE :t$i OR description LIKE :d$i OR file_path LIKE :p$i)";
                $score[] = "(title LIKE :st$i OR file_path LIKE :sp$i)";
                $like = '%' . $word . '%';
                $params["t$i"] = $params["d$i"] = $params["p$i"] = $params["st$i"] = $params["sp$i"] = $like;
            }
            $conditions[] = '(' . implode(' OR ', $kw) . ')';
        }

        $scoreSql = $score === [] ? '0' : implode(' + ', $score);
        $sql = "SELECT id, title, description, resource_type, form_level, year, file_name, ($scoreSql) AS score
                FROM resources
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY score DESC, year DESC, created_at DESC
                LIMIT " . (int) $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        foreach ($stmt->fetchAll() as $r) {
            // Skip rows whose only title is a random upload name like
            // "171b5967edb7a95…" unless the visitor is browsing a whole
            // category: they mean nothing to a student.
            $isHashTitle = (bool) preg_match('/^[0-9a-f]{24,}$/i', (string) $r['title']);
            if ($isHashTitle && $q['keywords'] !== []) {
                continue;
            }
            $id = (int) $r['id'];
            $results[] = [
                'title'        => $r['title'],
                'description'  => (string) ($r['description'] ?? ''),
                'type'         => type_label($r['resource_type']),
                'form'         => $r['form_level'] ? form_label($r['form_level']) : '',
                'year'         => $r['year'] ? (string) $r['year'] : '',
                'view_url'     => $base . '/resource-view.php?id=' . $id,
                'download_url' => $base . '/download.php?id=' . $id,
                'source'       => 'site',
                'score'        => (int) $r['score'] + 1,
            ];
        }
    }

    // ---- 2. School exam papers (static files in /exams/, listed in
    //         school-exams-data.php, shown on theory.php / practical.php) ----
    $wantsExams = $q['types'] === [] || array_intersect($q['types'], ['theory', 'practical']) !== [];
    if ($wantsExams && $q['keywords'] !== []) {
        foreach (school_exams() as $form => $subjects) {
            if ($q['form'] !== null && $q['form'] !== $form) {
                continue;
            }
            foreach ($subjects as $subjectKey => $papers) {
                $subjectName = SCHOOL_EXAM_SUBJECTS[$form][$subjectKey] ?? $subjectKey;
                foreach ($papers as [$title, $year, $file, $kind, $isPractical]) {
                    if ($q['types'] === ['practical'] && !$isPractical) {
                        continue;
                    }
                    $haystack = mb_strtolower($title . ' ' . $subjectName . ' ' . $subjectKey . ' ' . $file . ' ' . $year, 'UTF-8');
                    $hits = 0;
                    foreach ($q['keywords'] as $word) {
                        if (str_contains($haystack, $word)) {
                            $hits++;
                        }
                    }
                    if ($hits === 0) {
                        continue;
                    }
                    // Rank theory papers first for "past papers", practicals for "practical".
                    if ($q['types'] !== [] && in_array('practical', $q['types'], true) === (bool) $isPractical) {
                        $hits++;
                    }
                    $url = $base . '/' . str_replace('%2F', '/', rawurlencode($file));
                    $results[] = [
                        'title'        => $title . ($kind === 'ms' && stripos($title, 'marking') === false ? ' (Marking Scheme)' : ''),
                        'description'  => $subjectName . ' — school exam' . ($isPractical ? ' (practical)' : ''),
                        'type'         => $isPractical ? 'Practical Exam' : 'School Exam',
                        'form'         => form_label($form),
                        'year'         => $year ? (string) $year : '',
                        'view_url'     => $url,
                        'download_url' => $url,
                        'source'       => 'site',
                        'score'        => $hits,
                    ];
                }
            }
        }
    }

    // ---- 3. NECTA national past papers (external TETEA links listed in
    //         national-exams-data.php; only shown when exams are asked for) ----
    $asksForExams = array_intersect($q['types'], ['theory', 'practical']) !== [];
    if ($asksForExams && $q['keywords'] !== []) {
        // FTNA is the Form 2 exam, CSEE the Form 4 exam.
        $levelForm = ['ftna' => 'form2', 'csee' => 'form4'];
        foreach (national_exams() as $level => $exam) {
            if ($q['form'] !== null && ($levelForm[$level] ?? null) !== $q['form']) {
                continue;
            }
            foreach ($exam['subjects'] as $subjectKey => $subject) {
                $haystack = mb_strtolower($subject['name'] . ' ' . $subjectKey, 'UTF-8');
                $hits = 0;
                foreach ($q['keywords'] as $word) {
                    if (str_contains($haystack, $word)) {
                        $hits++;
                    }
                }
                if ($hits === 0) {
                    continue;
                }
                foreach ($subject['blocks'] as [$blockTitle, $isPractical, $papers]) {
                    if ((bool) $isPractical !== in_array('practical', $q['types'], true)) {
                        continue;
                    }
                    foreach (array_slice($papers, 0, 2) as [$year, $examUrl, $solutionUrl]) {
                        $results[] = [
                            'title'        => strtoupper($level) . ' ' . $subject['name'] . ' ' . $year . ($blockTitle !== '' ? ' — ' . $blockTitle : ''),
                            'description'  => 'NECTA national past paper' . ($solutionUrl ? ' (solutions available)' : ''),
                            'type'         => 'NECTA Past Paper',
                            'form'         => $level === 'ftna' ? 'Form 2' : 'Form 4',
                            'year'         => (string) $year,
                            'view_url'     => $examUrl,
                            'download_url' => $solutionUrl ?? $examUrl,
                            'source'       => 'necta',
                            'score'        => $hits + 2, // official national papers first
                        ];
                    }
                }
            }
        }
    }

    usort($results, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
    $results = array_slice($results, 0, $limit);
    foreach ($results as &$r) {
        unset($r['score']);
    }
    return $results;
}

// ------------------------------------------------------------
// Optional internet search
// ------------------------------------------------------------

/**
 * Returns up to $limit [title, url, snippet] results from the configured
 * search API, or [] when web search is disabled or fails. Only URLs that
 * the search API itself returned are ever passed on.
 */
function ai_web_search(string $query, int $limit = 4): array
{
    if (!ai_web_search_enabled()) {
        return [];
    }
    $c = ai_config();
    $query = mb_substr($query, 0, 300);

    if ($c['web_search_provider'] === 'brave') {
        $res = ai_http_request(
            'https://api.search.brave.com/res/v1/web/search?' . http_build_query(['q' => $query, 'count' => $limit, 'safesearch' => 'strict']),
            null,
            ['Accept: application/json', 'X-Subscription-Token: ' . $c['web_search_api_key']],
            12
        );
        $items = $res['json']['web']['results'] ?? [];
        $map = static fn(array $i): array => ['title' => $i['title'] ?? '', 'url' => $i['url'] ?? '', 'snippet' => strip_tags($i['description'] ?? '')];
    } else {
        $res = ai_http_request(
            'https://api.tavily.com/search',
            ['query' => $query, 'max_results' => $limit, 'search_depth' => 'basic'],
            ['Content-Type: application/json', 'Authorization: Bearer ' . $c['web_search_api_key']],
            12
        );
        $items = $res['json']['results'] ?? [];
        $map = static fn(array $i): array => ['title' => $i['title'] ?? '', 'url' => $i['url'] ?? '', 'snippet' => mb_substr((string) ($i['content'] ?? ''), 0, 300)];
    }

    if ($res['status'] !== 200) {
        error_log('Namanga AI: web search failed with HTTP ' . $res['status']);
        return [];
    }

    $out = [];
    foreach ($items as $item) {
        $row = $map((array) $item);
        if ($row['title'] !== '' && preg_match('#^https?://#i', $row['url'])) {
            $out[] = $row;
        }
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

// ------------------------------------------------------------
// Groq chat API
// ------------------------------------------------------------

/**
 * One HTTPS request with cURL. $jsonBody null = GET.
 * Returns ['status' => int (0 = network error), 'json' => ?array].
 */
function ai_http_request(string $url, ?array $jsonBody, array $headers, int $timeout = 30): array
{
    if (!function_exists('curl_init')) {
        error_log('Namanga AI: the PHP cURL extension is not enabled.');
        return ['status' => 0, 'json' => null];
    }
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];
    if ($jsonBody !== null) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($jsonBody, JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($body === false) {
        error_log('Namanga AI: cURL error: ' . curl_error($ch));
        $status = 0;
    }
    curl_close($ch);

    $json = is_string($body) ? json_decode($body, true) : null;
    return ['status' => $status, 'json' => is_array($json) ? $json : null];
}

/**
 * Sends the conversation to Groq. Tries the main model, then the fallback
 * model if the first is unavailable or overloaded.
 * Returns ['ok' => bool, 'reply' => string, 'error' => ''|'not_configured'|'auth'|'busy'|'failed'].
 */
function ai_chat_completion(array $messages): array
{
    if (!ai_is_configured()) {
        return ['ok' => false, 'reply' => '', 'error' => 'not_configured'];
    }
    $c = ai_config();
    $error = 'failed';

    foreach (array_unique([$c['groq_model'], $c['groq_fallback_model']]) as $model) {
        if ($model === '') {
            continue;
        }
        $body = [
            'model'                 => $model,
            'messages'              => $messages,
            'temperature'           => 0.4,
            'max_completion_tokens' => 900,
        ];
        // Answer directly instead of "thinking" first: faster and cheaper.
        if (str_starts_with($model, 'qwen/')) {
            $body['reasoning_effort'] = 'none';
        } elseif (str_starts_with($model, 'openai/gpt-oss')) {
            $body['reasoning_effort'] = 'low';
        }

        $res = ai_http_request(
            $c['groq_endpoint'],
            $body,
            ['Content-Type: application/json', 'Authorization: Bearer ' . $c['groq_api_key']],
            30
        );

        $reply = trim((string) ($res['json']['choices'][0]['message']['content'] ?? ''));
        // Some models wrap private reasoning in <think> tags; never show it.
        $reply = trim(preg_replace('#<think>.*?</think>#s', '', $reply));

        if ($res['status'] === 200 && $reply !== '') {
            return ['ok' => true, 'reply' => $reply, 'error' => ''];
        }

        $apiMessage = (string) ($res['json']['error']['message'] ?? '');
        error_log('Namanga AI: Groq HTTP ' . $res['status'] . ' for ' . $model . ($apiMessage !== '' ? ': ' . mb_substr($apiMessage, 0, 200) : ''));

        if ($res['status'] === 401 || $res['status'] === 403) {
            return ['ok' => false, 'reply' => '', 'error' => 'auth']; // the other model would fail too
        }
        $error = $res['status'] === 429 || $res['status'] >= 500 ? 'busy' : 'failed';
    }

    return ['ok' => false, 'reply' => '', 'error' => $error];
}

/**
 * The instructions sent to the model with every request, including the
 * real search results it is allowed to mention.
 */
function ai_system_prompt(string $lang, array $resources, array $webResults): string
{
    $language = $lang === 'sw'
        ? 'Kiswahili (clear, simple, standard Tanzanian Kiswahili)'
        : 'English (clear and simple)';
    $style = $lang === 'sw'
        ? 'KISWAHILI STYLE: Write natural, correct standard Kiswahili as used in Tanzanian schools. Tanzanian secondary schools teach in English, so keep subject and scientific terms in English (for example photosynthesis, chlorophyll, glucose, carbon dioxide, algorithm) and explain them in simple Kiswahili. Never invent new Kiswahili words.'
        : '';

    $prompt = <<<TXT
You are "Namanga Assistant", the study helper on the official website of Namanga Secondary School (NECTA centre S2911), Namanga, Longido District, Arusha, Tanzania. Motto: "Education for Liberty and Prosperity". Phone/WhatsApp: +255 695 389 537.

Your users are Form 1-4 secondary school students (about 13-19 years old), their parents and their teachers.

LANGUAGE: Always reply in {$language}, even if the user writes in another language.
{$style}

WHAT YOU DO:
- Explain school subjects (ICT/Computer Studies, Mathematics, Physics, Chemistry, Biology, Geography, History, English, Kiswahili, Civics, Business, etc.) simply and step by step, following the Tanzanian secondary syllabus where you can.
- Give study and exam-revision tips.
- Help people find files on this website using ONLY the WEBSITE RESOURCES listed below.
- Website sections: Notes, Summary, Theory Past Papers (NECTA FTNA/CSEE and school exams), Form 4 Practical, Software, Other Subjects, Upload Tasks, The Latest (photos and videos), Results (NECTA results links), About Us.

STRICT RULES:
- Never invent a resource, file, book title, exam, link or URL. Do NOT write any URL or web address in your reply. When WEBSITE RESOURCES lists files, they really exist on this website and are shown to the user as clickable cards under your answer: tell the user they are available and to use the cards below (or the Kiswahili equivalent).
- If WEBSITE RESOURCES says "Nothing found" and the user asked for a file, notes or a past paper, say clearly that this website does not have a matching file right now, and suggest the right menu section or other search words. Never mention "files below" when nothing was found. If the user only asked a general question, just answer it.
- Do not invent facts about the school (fees, dates, staff, admissions, results). If you do not know, tell the user to contact the school at +255 695 389 537.
- Be safe and age-appropriate. Politely refuse adult, violent, hateful, dangerous or illegal requests, and help with cheating on a live exam. Never ask for personal information.
- If you are not sure about an answer, say so honestly.
- Keep answers short (normally under 200 words). Use short paragraphs or "- " bullet lists. You may use **bold** for key words. No tables, no headings, no HTML.
TXT;

    $prompt .= "\n\nWEBSITE RESOURCES matching the latest question (from this website's database):\n";
    if ($resources === []) {
        $prompt .= "- Nothing found.\n";
    } else {
        foreach ($resources as $i => $r) {
            $meta = array_filter([
                'Type: ' . $r['type'],
                $r['form'] !== '' ? 'Form: ' . $r['form'] : '',
                $r['year'] !== '' ? 'Year: ' . $r['year'] : '',
                $r['description'] !== '' ? $r['description'] : '',
            ]);
            $prompt .= ($i + 1) . '. "' . $r['title'] . '" (' . implode('; ', $meta) . ")\n";
        }
    }

    if ($webResults !== []) {
        $prompt .= "\nINTERNET SEARCH RESULTS (shown to the user as links; you may summarise them, and say they come from other websites):\n";
        foreach ($webResults as $i => $w) {
            $prompt .= ($i + 1) . '. ' . $w['title'] . ' — ' . mb_substr($w['snippet'], 0, 250) . "\n";
        }
    }

    return $prompt;
}

/**
 * Removes any URL from the AI's reply that is not one of the real links
 * we gave it. A last line of defence against invented links.
 */
function ai_strip_unknown_urls(string $reply, array $allowedUrls): string
{
    return preg_replace_callback(
        '#\bhttps?://[^\s<>()\[\]"\']+|\bwww\.[^\s<>()\[\]"\']+#i',
        static function (array $m) use ($allowedUrls): string {
            $url = rtrim($m[0], '.,;:!?');
            return in_array($url, $allowedUrls, true) ? $m[0] : '';
        },
        $reply
    );
}
