<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// Public self-service registration for STAFF accounts only.
// Staff can upload, edit and view resources — but never delete anything
// (enforced server-side in delete-resource.php / delete slide action,
// not just hidden in the UI). Full Admin accounts are never created here.

if (is_admin_logged_in()) {
    redirect(base_url() . '/admin/dashboard.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($username === '' || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $check = $pdo->prepare('SELECT COUNT(*) AS total FROM admins WHERE username = :u');
            $check->execute(['u' => $username]);
            if ((int) $check->fetch()['total'] > 0) {
                $errors[] = 'That username is already taken. Please choose another.';
            }
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (:u, :p, 'staff')");
            $insert->execute(['u' => $username, 'p' => $hash]);
            $success = true;
        }
    }
}

$pageTitle = 'Staff Registration — Namanga Digital Resource Centre';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-content">
    <div class="container narrow">
        <div class="auth-card">
            <h1>Staff Registration</h1>
            <p class="muted">Create a Staff account to upload and edit learning resources. Staff accounts cannot delete resources — only an Admin account can do that.</p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Account created successfully. You can now log in.
                </div>
                <a href="<?= e(base_url()) ?>/admin/login.php" class="btn btn-primary">Go to Login</a>

            <?php else: ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endforeach; ?>

                <form method="post" action="">
                    <?= csrf_field() ?>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required minlength="3"
                           value="<?= e($_POST['username'] ?? '') ?>">

                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required minlength="8">
                        <button type="button" class="password-toggle-btn" aria-label="Show password" tabindex="-1">
                            <?= svg_icon('eye', 'icon-svg icon-eye') ?>
                            <?= svg_icon('eye-off', 'icon-svg icon-eye-off') ?>
                        </button>
                    </div>

                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        <button type="button" class="password-toggle-btn" aria-label="Show password" tabindex="-1">
                            <?= svg_icon('eye', 'icon-svg icon-eye') ?>
                            <?= svg_icon('eye-off', 'icon-svg icon-eye-off') ?>
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Staff Account</button>
                </form>

                <p class="auth-alt-action">
                    Already have an account? <a href="<?= e(base_url()) ?>/admin/login.php">Login here</a>.
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
