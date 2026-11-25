<?php
// Temporary one-time database copy for the VPS move. Uploaded by fetch-live-copy.yml with a random
// name and key, called once, then deleted by the same workflow. Never deployed by deploy.yml.
if (!hash_equals('__KEY__', (string) ($_GET['k'] ?? ''))) { http_response_code(404); exit; }
@set_time_limit(0);
require __DIR__ . '/config/database.php';
header('Content-Type: text/plain; charset=utf-8');
echo "-- Namanga website: full copy for the VPS move, " . date('Y-m-d H:i:s') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";
foreach ($pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM) as $r) {
    $t = str_replace('`', '', $r[0]);
    echo "DROP TABLE IF EXISTS `$t`;\n" . $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1] . ";\n";
    $st = $pdo->query("SELECT * FROM `$t`");
    $batch = []; $n = 0;
    while ($row = $st->fetch(PDO::FETCH_NUM)) {
        $batch[] = '(' . implode(',', array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row)) . ')';
        $n++;
        if (count($batch) === 200) { echo "INSERT INTO `$t` VALUES\n" . implode(",\n", $batch) . ";\n"; $batch = []; }
    }
    if ($batch) { echo "INSERT INTO `$t` VALUES\n" . implode(",\n", $batch) . ";\n"; }
    echo "-- $t: $n rows\n\n";
}
echo "SET FOREIGN_KEY_CHECKS = 1;\n-- END OF COPY\n";
