<?php
// The Download App page was removed from the website (owner, 2026-11-07).
// This old address now goes to the homepage.
require_once __DIR__ . '/includes/bootstrap.php';
header('Location: ' . base_url() . '/index.php', true, 301);
exit;
