<?php
/**
 * Central database configuration.
 *
 * Every other file that needs the database includes THIS file
 * and uses the $pdo connection it creates. Do not scatter
 * database credentials across other files.
 */

// ---- Edit these four lines for your XAMPP / server setup ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'namanga_resource_centre');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP's default MySQL root password is empty
// ---------------------------------------------------------------

// Application environment: set to false on a live/public server
// so raw PHP/database errors are never shown to visitors.
define('APP_DEBUG', true);

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die('Database connection error: ' . htmlspecialchars($e->getMessage()));
    }
    http_response_code(500);
    die('We are unable to connect to the database right now. Please try again later.');
}
