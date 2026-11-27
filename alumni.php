<?php
// Alumni: former students register so the school can stay in touch (owner 2026-11-26).
// What they type is private (only Admins see it, admin/manage-alumni.php).
require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];
$done = false;
$form = ['full_name' => '', 'year_completed' => '', 'last_form' => 'Form Four', 'phone' => '', 'email' => '', 'occupation' => '', 'location' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($form) as $k) {
        $form[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $_SESSION['alumni_sent'] = (int) ($_SESSION['alumni_sent'] ?? 0);
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (($_POST['website'] ?? '') !== '') {
        $errors[] = 'Please try again.'; // hidden field filled in: an automatic spam form
    } elseif ($_SESSION['alumni_sent'] >= ALUMNI_MAX_PER_SESSION) {
        $errors[] = 'Thank you, we have already received your registration.';
    } else {
        if (mb_strlen($form['full_name']) < 3 || mb_strlen($form['full_name']) > 150) {
            $errors[] = 'Please write your full name.';
        }
        $year = $form['year_completed'] === '' ? null : (int) $form['year_completed'];
        if ($year !== null && ($year < 1990 || $year > (int) date('Y'))) {
            $errors[] = 'Please check the year you completed.';
        }
        $form['last_form'] = 'Form Four'; // Alumni = Form Four leavers only (owner 2026-11-27)
        if ($form['phone'] === '' && $form['email'] === '') {
            $errors[] = 'Please give a phone number or an email so the school can reach you.';
        }
        if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please check your email address.';
        }
        if (empty($_POST['consent'])) {
            $errors[] = 'Please tick the box to allow the school to keep your details.';
        }
        if (!$errors) {
            $pdo->prepare('INSERT INTO alumni (full_name, year_completed, last_form, phone, email, occupation, location, message, consent)
                VALUES (:n, :y, :f, :p, :e, :o, :l, :m, 1)')->execute([
                'n' => mb_substr($form['full_name'], 0, 150), 'y' => $year, 'f' => $form['last_form'],
                'p' => mb_substr($form['phone'], 0, 40) ?: null, 'e' => mb_substr($form['email'], 0, 150) ?: null,
                'o' => mb_substr($form['occupation'], 0, 150) ?: null, 'l' => mb_substr($form['location'], 0, 150) ?: null,
                'm' => mb_substr($form['message'], 0, 2000) ?: null,
            ]);
            $_SESSION['alumni_sent']++;
            $done = true;
        }
    }
}

$pageTitle = 'Alumni | Namanga Secondary School';
$pageDescription = 'Former students of Namanga Secondary School: register to stay in touch with your school.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Alumni</h1>
        <p>Former students of Namanga Secondary School &mdash; stay in touch with your school</p>
    </div>
</section>

<section class="page-content">
    <div class="container narrow">
        <?php if ($done): ?>
            <div class="alert alert-success">Thank you! Your details have been received by the school. Welcome to the Namanga Secondary School alumni family.</div>
            <p><a class="btn btn-outline" href="<?= e($base) ?>/index.php">Back to the home page</a></p>
        <?php else: ?>
            <p>Did you study at Namanga Secondary School? Register below so the school can keep in touch with you,
            invite you to school events and connect you with current students. Your details are seen only by the school administration.</p>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endforeach; ?>

            <form method="post" action="" class="upload-form alumni-form">
                <?= csrf_field() ?>
                <div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <label for="full_name">Full name *</label>
                <input type="text" id="full_name" name="full_name" maxlength="150" required value="<?= e($form['full_name']) ?>">

                <label for="year_completed">Year you completed Form Four</label>
                <input type="number" id="year_completed" name="year_completed" min="1990" max="<?= (int) date('Y') ?>" value="<?= e($form['year_completed']) ?>" placeholder="e.g. 2015">

                <div class="form-row-2">
                    <div>
                        <label for="phone">Phone / WhatsApp</label>
                        <input type="tel" id="phone" name="phone" maxlength="40" value="<?= e($form['phone']) ?>" placeholder="07xx xxx xxx">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="150" value="<?= e($form['email']) ?>">
                    </div>
                </div>

                <div class="form-row-2">
                    <div>
                        <label for="occupation">What do you do now?</label>
                        <input type="text" id="occupation" name="occupation" maxlength="150" value="<?= e($form['occupation']) ?>" placeholder="e.g. Teacher, Student at university, Business">
                    </div>
                    <div>
                        <label for="location">Where do you live?</label>
                        <input type="text" id="location" name="location" maxlength="150" value="<?= e($form['location']) ?>" placeholder="e.g. Arusha">
                    </div>
                </div>

                <label for="message">Message to the school (optional)</label>
                <textarea id="message" name="message" rows="4" maxlength="2000"><?= e($form['message']) ?></textarea>

                <label class="checkbox-line"><input type="checkbox" name="consent" value="1" required> I allow Namanga Secondary School to keep my details and contact me.</label>

                <button type="submit" class="btn btn-primary" style="margin-top:14px;">Register</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
