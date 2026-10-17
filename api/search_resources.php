<?php
/**
 * Searches this website's own files and returns them as JSON.
 *
 * GET /api/search_resources.php?q=biology+form+2
 * Reply: { "ok": true, "query": "...", "count": n, "results": [
 *            { "title", "description", "type", "form", "year",
 *              "view_url", "download_url", "external" }, ... ] }
 *
 * Results come only from the resources table and the site's exam lists
 * (see ai_search_resources() in includes/ai.php). Links are built exactly
 * like the rest of the site builds them. Nothing is invented.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/ai.php';

$query = trim((string) ($_GET['q'] ?? ''));
$query = preg_replace('/[\x00-\x1F\x7F]/u', '', $query) ?? '';

if ($query === '') {
    ai_json_response(['ok' => false, 'error' => 'Please give a search word, e.g. ?q=biology form 2'], 400);
}
if (mb_strlen($query) > 200) {
    ai_json_response(['ok' => false, 'error' => 'Search text is too long (200 characters maximum).'], 400);
}

try {
    if (!ai_rate_limit_check($pdo, 'search')) {
        ai_json_response(['ok' => false, 'error' => 'Too many searches. Please wait a minute.'], 429);
    }
    session_write_close();

    $limit = max(1, min(20, (int) ($_GET['limit'] ?? 10)));
    $results = array_map(static fn(array $r): array => [
        'title'        => $r['title'],
        'description'  => $r['description'],
        'type'         => $r['type'],
        'form'         => $r['form'],
        'year'         => $r['year'],
        'view_url'     => $r['view_url'],
        'download_url' => $r['download_url'],
        'external'     => $r['source'] !== 'site',
    ], ai_search_resources($pdo, $query, $limit));

    ai_json_response(['ok' => true, 'query' => $query, 'count' => count($results), 'results' => $results]);
} catch (Throwable $e) {
    error_log('Namanga AI: search failed: ' . $e->getMessage());
    ai_json_response(['ok' => false, 'error' => 'Search is not available right now. Please try again.'], 500);
}
