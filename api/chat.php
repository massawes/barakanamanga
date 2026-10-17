<?php
/**
 * AI chat endpoint for the floating assistant (assets/js/assistant.js).
 *
 * POST, JSON body: { "message": "...", "lang": "en"|"sw",
 *                    "history": [{"role":"user"|"assistant","content":"..."}, ...],
 *                    "web": true|false }
 * Reply JSON:      { "ok": bool, "reply": "...", "resources": [...], "web": [...], "ai": bool }
 *
 * The browser never sees the API key: this file reads it from the server's
 * config (see includes/ai.php) and calls Groq itself with cURL.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/ai.php';

const AI_MAX_MESSAGE_CHARS = 1000;
const AI_MAX_HISTORY_ITEMS = 10;
const AI_MAX_HISTORY_CHARS = 2000;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    ai_json_response(['ok' => false, 'reply' => ai_text('bad_request', 'en')], 405);
}

// Only accept JSON. A plain HTML form on another website cannot send this
// content type without the browser blocking it first (CORS preflight).
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
    ai_json_response(['ok' => false, 'reply' => ai_text('bad_request', 'en')], 415);
}

$input = json_decode((string) file_get_contents('php://input', false, null, 0, 64000), true);
if (!is_array($input)) {
    ai_json_response(['ok' => false, 'reply' => ai_text('bad_request', 'en')], 400);
}

$lang = ($input['lang'] ?? '') === 'sw' ? 'sw' : 'en';
$message = is_string($input['message'] ?? null) ? trim($input['message']) : '';
// Strip control characters (keeps normal text, new lines and emoji).
$message = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', $message) ?? '';

if ($message === '') {
    ai_json_response(['ok' => false, 'reply' => ai_text('empty', $lang)], 400);
}
if (mb_strlen($message) > AI_MAX_MESSAGE_CHARS) {
    ai_json_response(['ok' => false, 'reply' => ai_text('too_long', $lang)], 400);
}

try {
    if (!ai_rate_limit_check($pdo, 'chat')) {
        ai_json_response(['ok' => false, 'reply' => ai_text('rate_limit', $lang)], 429);
    }
} catch (Throwable $e) {
    error_log('Namanga AI: rate limit check failed: ' . $e->getMessage());
}

// The session is only needed for rate limiting. Release its lock now so
// the visitor's other pages are not held up while the AI is answering.
session_write_close();

// ---- Conversation history (last 10 messages, sent by the browser) ----
$history = [];
if (is_array($input['history'] ?? null)) {
    foreach (array_slice($input['history'], -AI_MAX_HISTORY_ITEMS) as $item) {
        $role = $item['role'] ?? '';
        $content = $item['content'] ?? '';
        if (!in_array($role, ['user', 'assistant'], true) || !is_string($content) || trim($content) === '') {
            continue;
        }
        $history[] = ['role' => $role, 'content' => mb_substr(trim($content), 0, AI_MAX_HISTORY_CHARS)];
    }
}

try {
    // ---- 1. Search this website (real rows, real links), but only when
    //         the visitor is asking for files, so a general question like
    //         "explain photosynthesis" does not get unrelated cards ----
    $resources = ai_wants_resources($message) ? ai_search_resources($pdo, $message) : [];

    // ---- 2. Optional internet search ----
    $webResults = (!empty($input['web']) && ai_web_search_enabled()) ? ai_web_search($message) : [];

    // ---- 3. Ask the AI ----
    $messages = array_merge(
        [['role' => 'system', 'content' => ai_system_prompt($lang, $resources, $webResults)]],
        $history,
        [['role' => 'user', 'content' => $message]]
    );
    $result = ai_chat_completion($messages);

    $resourceCards = array_map(static fn(array $r): array => [
        'title'        => $r['title'],
        'description'  => $r['description'],
        'type'         => $r['type'],
        'form'         => $r['form'],
        'year'         => $r['year'],
        'view_url'     => $r['view_url'],
        'download_url' => $r['download_url'],
        'external'     => $r['source'] !== 'site',
    ], $resources);
    $webCards = array_map(static fn(array $w): array => [
        'title' => $w['title'],
        'url'   => $w['url'],
        'host'  => (string) parse_url($w['url'], PHP_URL_HOST),
    ], $webResults);

    if ($result['ok']) {
        $allowed = array_merge(
            array_column($resources, 'view_url'),
            array_column($resources, 'download_url'),
            array_column($webResults, 'url')
        );
        $reply = ai_strip_unknown_urls($result['reply'], $allowed);
    } else {
        // AI unavailable: still give the visitor the real search results.
        $hasResults = $resources !== [] || $webResults !== [];
        $reply = $result['error'] === 'busy' && $hasResults
            ? ai_text('ai_busy', $lang)
            : ai_text($hasResults ? 'ai_off' : 'ai_off_none', $lang);
    }

    ai_json_response([
        'ok'        => true,
        'ai'        => $result['ok'],
        'reply'     => $reply,
        'resources' => $resourceCards,
        'web'       => $webCards,
    ]);
} catch (Throwable $e) {
    error_log('Namanga AI: chat failed: ' . $e->getMessage());
    ai_json_response(['ok' => false, 'reply' => ai_text('server', $lang)], 500);
}
