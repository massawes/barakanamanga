<?php
require_once __DIR__ . '/../includes/bootstrap.php';

admin_logout();

// Start a fresh session just to hold the flash message on the login page.
session_start();
flash_set('success', 'You have been logged out successfully.');
redirect(base_url() . '/admin/login.php');
