<?php
/**
 * Alumni register (owner 2026-11-26, idea from ilboru.ac.tz): former students sign up on alumni.php.
 * Their details are PRIVATE: only Admins see them in admin/manage-alumni.php (list, delete, CSV export).
 */

const ALUMNI_MAX_PER_SESSION = 3; // simple spam limit per browser session

function ensure_alumni_table(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS alumni (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(150) NOT NULL,
                year_completed SMALLINT UNSIGNED NULL,
                last_form VARCHAR(20) NULL,
                phone VARCHAR(40) NULL,
                email VARCHAR(150) NULL,
                occupation VARCHAR(150) NULL,
                location VARCHAR(150) NULL,
                message TEXT NULL,
                consent TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    } catch (Throwable $e) {
        // Try again on a later request.
    }
}
