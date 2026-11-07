<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Public registration is closed (owner, 2026-11-07). Only the Super Admin
// creates accounts, on Manage Staff Accounts. Old links land on the login page.
redirect(base_url() . '/admin/login.php');
