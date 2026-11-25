<?php
/**
 * Checks the NECTA website for new results of Namanga Secondary School (S2911).
 * Run by cron on the VPS every 3 hours (file /etc/cron.d/namanga-necta), as the web user:
 *     php /var/www/namanga-website/cron/necta-check.php
 * New years appear on results.php and an announcement is posted on the home page.
 * Log: /var/log/namanga-necta.log
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$_SERVER['HTTP_HOST'] = 'namangasecondary.sc.tz';
$_SERVER['SCRIPT_NAME'] = '/cron/necta-check.php';
require __DIR__ . '/../includes/bootstrap.php';

$found = necta_check_for_new_results($pdo, static function (string $line): void {
    echo '[' . date('Y-m-d H:i') . "] $line\n";
});
echo '[' . date('Y-m-d H:i') . '] done, new: ' . count($found) . "\n";
