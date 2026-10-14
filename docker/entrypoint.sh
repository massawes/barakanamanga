#!/bin/sh
set -e

cd /var/www/html

# Write config/env.php from the container's environment, the same file
# the InfinityFree deploy generates. Its presence also switches on the
# one-time import of config/live-import.json into the empty database.
php -r '
    $out = "<?php\n";
    foreach (["DB_HOST", "DB_NAME", "DB_USER", "DB_PASS"] as $k) {
        $v = getenv($k);
        if ($v === false || $v === "") { fwrite(STDERR, "Missing environment variable $k\n"); exit(1); }
        $out .= "define(" . var_export($k, true) . ", " . var_export($v, true) . ");\n";
    }
    $debug = in_array(strtolower((string) getenv("APP_DEBUG")), ["1", "true", "yes"], true);
    $out .= "define(\x27APP_DEBUG\x27, " . var_export($debug, true) . ");\n";
    file_put_contents("config/env.php", $out);
'
chown www-data:www-data config/env.php
chmod 640 config/env.php

# uploads/ is a volume; make sure Apache can write to it.
mkdir -p uploads
find uploads ! -user www-data -exec chown www-data:www-data {} +

exec docker-php-entrypoint "$@"
