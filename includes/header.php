<?php
// Optional variables the including page may set before including this file:
//   $pageTitle       — text for <title>
//   $pageDescription — meta description (shown under the link in Google)
//   $noindex         — true for pages Google should not list (e.g. search results)
$pageTitle = $pageTitle ?? 'Namanga Secondary School Website';
$pageDescription = $pageDescription ?? 'Official website of Namanga Secondary School, Tanzania — ICT & Computer Science notes, summaries, past papers, NECTA results and software for Form 1 to Form 4.';
$noindex = $noindex ?? false;
$canonical = canonical_url();
$isHome = $canonical === SITE_URL . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<?php if ($noindex): ?>
<meta name="robots" content="noindex, follow">
<?php else: ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="Namanga Secondary School">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($isHome): ?>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'HighSchool',
            '@id' => SITE_URL . '/#school',
            'name' => 'Namanga Secondary School',
            'alternateName' => ['Namanga Secondary', 'Namanga School', 'Namanga Website'],
            'url' => SITE_URL . '/',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Namanga',
                'addressRegion' => 'Arusha',
                'addressCountry' => 'TZ',
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => SITE_URL . '/#website',
            'name' => 'Namanga Secondary School Website',
            'alternateName' => 'Namanga Website',
            'url' => SITE_URL . '/',
            'publisher' => ['@id' => SITE_URL . '/#school'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => SITE_URL . '/search.php?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>

</script>
<?php endif; ?>
<link rel="stylesheet" href="<?= e(base_url()) ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main>
