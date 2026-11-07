<?php
// The Summary section was removed from the website (owner, 2026-11-07).
// The files and database rows are kept; this old address now goes to the homepage.
require_once __DIR__ . '/includes/bootstrap.php';
header('Location: ' . base_url() . '/index.php', true, 301);
exit;
