<?php
/**
 * One-time importer that copies resource / gallery / slide / task
 * records from the local XAMPP site onto the live site.
 *
 * config/live-import.json is generated locally from the database and
 * deployed with the site. On the first page load after a deploy, any
 * record whose file is present on disk is inserted (or its metadata
 * refreshed if a row for that file already exists). A marker in
 * uploads/ remembers which version of the JSON has been applied, so
 * this costs one file_exists() + md5 check on every other request.
 */

function run_live_import(PDO $pdo): void
{
    $root = dirname(__DIR__);
    $jsonFile = $root . '/config/live-import.json';
    // config/env.php only exists on the deployed site (the workflow
    // writes it), so this never touches the local XAMPP database.
    if (!is_file($root . '/config/env.php') || !is_file($jsonFile)) {
        return;
    }

    $hash = 'v2:' . md5_file($jsonFile);
    $marker = $root . '/uploads/.live_import_done';
    if (is_file($marker) && trim((string) file_get_contents($marker)) === $hash) {
        return;
    }
    // Throttle attempts so parallel or repeated requests don't all import.
    $lock = $root . '/uploads/.live_import_try';
    if (is_file($lock) && time() - filemtime($lock) < 60) {
        return;
    }
    @touch($lock);

    $data = json_decode((string) file_get_contents($jsonFile), true);
    if (!is_array($data)) {
        return;
    }

    // table => column holding the file path
    $tables = [
        'resources'     => 'file_path',
        'gallery_items' => 'file_path',
        'hero_slides'   => 'image_path',
        'tasks'         => 'file_path',
    ];

    // A deploy uploads files one by one; wait until every file has
    // arrived so the import never runs against a half-finished upload.
    foreach ($tables as $table => $pathColumn) {
        foreach ($data[$table] ?? [] as $row) {
            if (!is_file($root . '/' . ltrim($row[$pathColumn] ?? '', '/'))) {
                return;
            }
        }
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tasks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            uploader_name VARCHAR(150) NOT NULL,
            title VARCHAR(255) NOT NULL,
            form_level ENUM('form1','form2','form3','form4') NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT UNSIGNED NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_form_level (form_level),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // The homepage slider and gallery must match the local site exactly,
    // so rows for those two that aren't in the JSON are dropped.
    $mirrored = ['gallery_items', 'hero_slides'];

    foreach ($tables as $table => $pathColumn) {
        if (in_array($table, $mirrored, true) && !empty($data[$table])) {
            $keep = array_column($data[$table], $pathColumn);
            $marks = implode(', ', array_fill(0, count($keep), '?'));
            $pdo->prepare("DELETE FROM `$table` WHERE `$pathColumn` NOT IN ($marks)")->execute($keep);
        }
        foreach ($data[$table] ?? [] as $row) {
            $path = $row[$pathColumn] ?? '';
            if ($path === '' || !is_file($root . '/' . ltrim($path, '/'))) {
                continue;
            }
            unset($row['id']);
            $columns = array_keys($row);

            $find = $pdo->prepare("SELECT id FROM `$table` WHERE `$pathColumn` = :p LIMIT 1");
            $find->execute(['p' => $path]);
            $existingId = $find->fetchColumn();

            if ($existingId) {
                $sets = implode(', ', array_map(fn ($c) => "`$c` = :$c", $columns));
                $pdo->prepare("UPDATE `$table` SET $sets WHERE id = :__id")
                    ->execute($row + ['__id' => $existingId]);
            } else {
                $cols = implode(', ', array_map(fn ($c) => "`$c`", $columns));
                $vals = implode(', ', array_map(fn ($c) => ":$c", $columns));
                $pdo->prepare("INSERT INTO `$table` ($cols) VALUES ($vals)")->execute($row);
            }
        }
    }

    @file_put_contents($marker, $hash);
}
