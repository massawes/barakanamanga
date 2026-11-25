<?php
// Optional variables the including page may set before including this file:
//   $pageTitle       — text for <title>
//   $pageDescription — meta description (shown under the link in Google)
//   $noindex         — true for pages Google should not list (e.g. search results)
$pageTitle = $pageTitle ?? (is_portal() ? 'Student Portal | Namanga Secondary School' : 'Namanga Secondary School Website');
$pageDescription = $pageDescription ?? (is_portal()
    ? 'Namanga Secondary School Student Portal: free notes, theory past papers and Form 4 practicals for Form 1 to Form 4.'
    : 'Official website of Namanga Secondary School, a government secondary school in Namanga, Longido District, Arusha, Tanzania (NECTA S2911).');
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
<meta property="og:site_name" content="<?= is_portal() ? 'Namanga Secondary School Student Portal' : 'Namanga Secondary School' ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(SITE_URL) ?>/assets/images/<?= $isHome ? 'school/students-red.jpg' : 'logo-navy.png' ?>">
<?php if ($isHome): ?>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'HighSchool',
            '@id' => SCHOOL_URL . '/#school',
            'name' => 'Namanga Secondary School',
            'alternateName' => ['Namanga Secondary', 'Namanga Secondary School Tanzania', 'Namanga School', 'Namanga Website', 'namangasecondary.sc.tz'],
            'description' => 'Secondary school in Namanga, Longido District, Arusha, Tanzania (NECTA centre S2911), offering Form 1 to Form 4 education. Motto: Education for Liberty and Prosperity.',
            'disambiguatingDescription' => 'The Tanzanian Namanga Secondary School in Longido District, Arusha Region (NECTA S2911), not the school of the same name in Namanga, Kenya.',
            'slogan' => 'Education for Liberty and Prosperity',
            'identifier' => ['@type' => 'PropertyValue', 'propertyID' => 'NECTA centre number', 'value' => 'S2911'],
            'url' => SCHOOL_URL . '/',
            'telephone' => '+255695389537',
            'email' => 'namanga@longido.dc.go.tz',
            'logo' => SCHOOL_URL . '/assets/images/logo-square.png',
            'image' => [
                SCHOOL_URL . '/assets/images/school/students-red.jpg',
                SCHOOL_URL . '/assets/images/school/assembly.jpg',
                SCHOOL_URL . '/assets/images/school/ceremony.jpg',
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'postOfficeBoxNumber' => 'P.O. Box 6560',
                'streetAddress' => 'Namanga, Longido District',
                'addressLocality' => 'Namanga',
                'addressRegion' => 'Arusha',
                'addressCountry' => 'TZ',
            ],
            'areaServed' => 'Longido District, Arusha, Tanzania',
            'sameAs' => [
                'https://www.facebook.com/namangasecondaryschool',
                'https://www.tiktok.com/@namangasecondaryschool',
                'https://www.youtube.com/@namangasecondaryschool',
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => SITE_URL . '/#website',
            'name' => is_portal() ? 'Namanga Secondary School Student Portal' : 'Namanga Secondary School Website',
            'alternateName' => is_portal() ? 'Namanga Masomo' : 'Namanga Website',
            'url' => SITE_URL . '/',
            'publisher' => ['@id' => SCHOOL_URL . '/#school'],
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
<link rel="icon" href="<?= e(base_url()) ?>/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="192x192" href="<?= e(base_url()) ?>/assets/images/favicon.png">
<link rel="apple-touch-icon" href="<?= e(base_url()) ?>/assets/images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&family=Playfair+Display:wght@700;800&display=swap">
<link rel="stylesheet" href="<?= e(base_url()) ?>/assets/css/style.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main>
