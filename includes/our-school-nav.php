<?php
// Tab row shown at the top of every "Our School" page.
$schoolPages = [
    'about.php'          => ['About Us', 'school'],
    'mission-vision.php' => ['Mission & Vision', 'target'],
    'history.php'        => ['Our History', 'history'],
    'our-students.php'   => ['Our Students', 'students'],
];
$currentSchoolPage = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="school-subnav" aria-label="Our School">
    <?php foreach ($schoolPages as $file => [$label, $icon]): ?>
        <a href="<?= e(base_url()) ?>/<?= e($file) ?>" class="<?= $currentSchoolPage === $file ? 'active' : '' ?>">
            <?= svg_icon($icon) ?> <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</nav>
