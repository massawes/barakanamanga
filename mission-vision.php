<?php
// Mission & Vision is hidden (owner 2026-11-25): the old text was not the school's official wording.
// When the school gives its official mission, vision and values, restore this page from git history
// (commit before 2026-11-25) with the official text. Until then this address opens About Us.
require_once __DIR__ . '/includes/bootstrap.php';
header('Location: ' . base_url() . '/about.php', true, 301);
exit;
