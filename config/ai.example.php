<?php
/**
 * AI assistant settings: TEMPLATE. Copy this file to config/ai.php and put
 * your real keys there. config/ai.php is in .gitignore, so it is never
 * committed, and config/.htaccess blocks it from the web.
 *
 * On your own server you can instead keep the file OUTSIDE the web root
 * (e.g. /var/www/namanga-private/ai.php) and point to it with the Apache
 * line:   SetEnv NAMANGA_AI_CONFIG /var/www/namanga-private/ai.php
 * Or skip the file and set environment variables: GROQ_API_KEY, GROQ_MODEL,
 * WEB_SEARCH_PROVIDER, WEB_SEARCH_API_KEY.
 *
 * Only groq_api_key is required; every other line is optional.
 */
return [
    // Required. Free key from https://console.groq.com/keys (starts with "gsk_").
    'groq_api_key' => 'gsk_your_key_here',

    // Optional: which Groq models to use (list: https://console.groq.com/docs/models).
    // 'groq_model'          => 'openai/gpt-oss-120b',
    // 'groq_fallback_model' => 'qwen/qwen3.8-27b',

    // Optional internet search. Leave empty to switch it off.
    // 'brave'  -> key from https://api-dashboard.search.brave.com
    // 'tavily' -> key from https://app.tavily.com
    'web_search_provider' => '',
    'web_search_api_key'  => '',

    // Optional rate limits (requests).
    // 'limit_session_per_min' => 8,
    // 'limit_ip_per_min'      => 30,
    // 'limit_ip_per_day'      => 600,
];
