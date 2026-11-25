<?php
// Home page of the Student Portal (masomo.namangasecondary.sc.tz). Included by index.php.
$counts = ['notes' => 0, 'theory' => 0, 'practical' => 0];
try {
    foreach ($pdo->query('SELECT resource_type, COUNT(*) AS total FROM resources GROUP BY resource_type')->fetchAll() as $row) {
        $counts[$row['resource_type']] = (int) $row['total'];
    }
} catch (Exception $e) {
    // Table not ready yet: show the cards without counts.
}

$resourceCards = [
    'notes'     => ['notes.php', 'Notes', 'TIE textbooks, syllabus and notes for every subject, Form 1 to Form 4.', $counts['notes'], 'file', 'Open Notes'],
    'theory'    => ['theory.php', 'Theory Past Papers', 'NECTA and school theory examination papers, by Form, subject and year.', $counts['theory'], 'file', 'Open Past Papers'],
    'practical' => ['practical.php', 'Form 4 Practical', 'Form 4 practical examination papers for the science and computer subjects.', $counts['practical'], 'file', 'Open Practicals'],
];

$pageTitle = 'Student Portal (Masomo) | Namanga Secondary School';
$pageDescription = 'Namanga Secondary School Student Portal: free notes, NECTA and school theory past papers and Form 4 practicals for Form 1 to Form 4.';
$base = base_url();
include __DIR__ . '/header.php';
?>

<section class="portal-hero">
    <div class="container">
        <span class="portal-hero-tag">Namanga Secondary School &middot; Masomo</span>
        <h1>Student Learning Portal</h1>
        <p>Free notes, past papers and practicals for Form 1 to Form 4. Read online or download, at school or at home.</p>
        <form action="<?= e($base) ?>/search.php" method="get" class="portal-search" role="search">
            <label for="portalSearch" class="visually-hidden">Search notes and past papers</label>
            <input type="text" id="portalSearch" name="q" placeholder="Search a subject, topic or year, e.g. Biology 2023" autocomplete="off">
            <button type="submit" class="btn btn-gold"><?= svg_icon('search', 'icon-svg icon-svg-small') ?> Search</button>
        </form>
    </div>
</section>

<section class="resource-cards">
    <div class="container">
        <h2 class="section-title">What would you like to study?</h2>
        <p class="section-subtitle">Choose a category, then your Form and subject</p>
        <div class="photo-cards-grid">
            <?php foreach ($resourceCards as $key => [$page, $title, $text, $count, $word, $linkText]): ?>
                <?= render_photo_card($base . '/' . $page, $key, $title, $text, count_label((int) $count, $word), $linkText) ?>
            <?php endforeach; ?>
        </div>
        <?= render_card_photo_credits(array_keys($resourceCards)) ?>
    </div>
</section>

<section class="portal-forms">
    <div class="container">
        <h2 class="section-title">Notes by Form</h2>
        <div class="portal-form-grid">
            <?php foreach (['form1' => 'Form One', 'form2' => 'Form Two', 'form3' => 'Form Three', 'form4' => 'Form Four'] as $formKey => $formLabel): ?>
                <a class="portal-form-tile" href="<?= e($base) ?>/notes.php?form=<?= e($formKey) ?>">
                    <span class="portal-form-num"><?= e(strtoupper(substr($formLabel, 5))) ?></span>
                    <span class="portal-form-label"><?= e($formLabel) ?> Notes</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="home-intro">
    <div class="container">
        <ul class="feature-grid">
            <li class="feature-card">
                <span class="feature-icon"><?= svg_icon('results', 'icon-svg') ?></span>
                <h3 class="feature-title">Your School Results</h3>
                <p>Check your term and examination results in the <a href="https://results.namangasecondary.sc.tz">Student Result System</a>.</p>
            </li>
            <li class="feature-card">
                <span class="feature-icon"><?= svg_icon('chat', 'icon-svg') ?></span>
                <h3 class="feature-title">Ask AI</h3>
                <p>Stuck on a topic? Use the <strong>Ask AI</strong> button at the bottom of the page for a quick explanation.</p>
            </li>
            <li class="feature-card">
                <span class="feature-icon"><?= svg_icon('school', 'icon-svg') ?></span>
                <h3 class="feature-title">School Website</h3>
                <p>News, announcements, admissions and more on the <a href="<?= e(other_site_home()) ?>">school website</a>.</p>
            </li>
        </ul>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
