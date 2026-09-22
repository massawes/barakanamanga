<?php
// Expects $pageTitle and, optionally, $pageDescription to be set by the
// including page. Falls back to good site-wide defaults so every page
// still has a sensible <title>/description even if it forgets to set one.
$pageTitle = $pageTitle ?? 'Namanga Secondary School Digital Resource Centre';
$pageDescription = $pageDescription
    ?? 'Namanga Secondary School ICT & Computer Science Digital Resource Centre — free notes, summaries, past papers and software for Form 1 to Form 4.';

// Absolute URL of the current page, used for canonical/Open Graph tags.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$currentUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= e($currentUrl) ?>">

<!-- Open Graph / Facebook / WhatsApp link previews -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($currentUrl) ?>">
<meta property="og:site_name" content="Namanga Secondary School Digital Resource Centre">
<meta property="og:locale" content="en_KE">

<!-- Twitter/X card -->
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($pageDescription) ?>">

<link rel="stylesheet" href="<?= e(base_url()) ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main>
