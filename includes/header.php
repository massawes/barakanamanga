<?php
// Expects $pageTitle to be set by the including page (optional).
$pageTitle = $pageTitle ?? 'Namanga Secondary School Digital Resource Centre';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="Namanga Secondary School ICT & Computer Science Digital Resource Centre — notes, summaries, past papers and software for Form 1 to Form 4.">
<link rel="stylesheet" href="<?= e(base_url()) ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main>
