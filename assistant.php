<?php
/**
 * Offline site-assistant endpoint. Public — no login required, since
 * students use this too. Accepts a typed message and returns a JSON
 * reply built entirely from this site's own database (see
 * assistant_answer() in includes/functions.php). No internet connection
 * or external AI/API service is used anywhere in this file.
 */
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$message = trim($_POST['message'] ?? $_GET['message'] ?? '');

// Keep questions to a sane length — this is a search box, not a document
// upload, and it protects the LIKE queries in assistant_answer() from
// being handed something huge.
if (mb_strlen($message) > 300) {
    $message = mb_substr($message, 0, 300);
}

try {
    $answer = assistant_answer($pdo, $message);
} catch (Throwable $e) {
    $answer = [
        'reply'      => 'Samahani, kuna hitilafu ndogo. Tafadhali jaribu tena.',
        'resources'  => [],
        'gallery'    => [],
        'search_url' => null,
    ];
}

echo json_encode($answer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
