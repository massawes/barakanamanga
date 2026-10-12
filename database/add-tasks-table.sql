-- ============================================================
-- NAMANGA SECONDARY SCHOOL — Digital Resource Centre
-- Adds the "tasks" table for the new public Upload Tasks feature.
--
-- HOW TO RUN THIS ON YOUR LIVE INFINITYFREE SITE:
--   1. Log in to your InfinityFree dashboard.
--   2. Go to "MySQL Databases" -> click "Enter phpMyAdmin" next to
--      if0_42977553_namanga_resource_centre.
--   3. Click the "SQL" tab at the top.
--   4. Paste the contents of this file into the box.
--   5. Click "Go".
-- That's it — no need to re-import the whole namanga.sql file,
-- this only adds the one new table your existing data is untouched.
-- ============================================================

CREATE TABLE IF NOT EXISTS tasks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
