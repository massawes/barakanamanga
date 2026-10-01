<?php
require_once __DIR__ . '/includes/bootstrap.php';

$query = trim($_GET['q'] ?? '');
$results = [];

if ($query !== '') {
    $like = '%' . $query . '%';
    $stmt = $pdo->prepare(
        "SELECT * FROM resources
         WHERE title LIKE :q1 OR description LIKE :q2
         ORDER BY resource_type, FIELD(form_level,'form1','form2','form3','form4'), year DESC, created_at DESC"
    );
    $stmt->execute(['q1' => $like, 'q2' => $like]);
    $results = $stmt->fetchAll();
}

$pageTitle = 'Search Results | Namanga Secondary School';
$noindex = true;
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Search Resources</h1>
        <form action="<?= e($base) ?>/search.php" method="get" class="page-search-form">
            <input type="text" name="q" placeholder="Search notes, summaries, past papers, software..." value="<?= e($query) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if ($query === ''): ?>
            <p class="empty-state">Type a keyword above to search all resources.</p>
        <?php else: ?>
            <h2 class="section-title">Search Results for: "<?= e($query) ?>"</h2>
            <?php if (empty($results)): ?>
                <p class="empty-state">No resources matched your search. Please try a different keyword.</p>
            <?php else: ?>
                <div class="resource-list">
                    <?php foreach ($results as $resource): ?>
                        <?php include __DIR__ . '/includes/resource-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
