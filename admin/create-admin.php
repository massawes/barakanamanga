<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// This page creates the FIRST admin account only.
// It automatically disables itself once any admin account exists,
// so the SQL file never needs to contain a plain-text password.

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM admins WHERE role = 'admin'");
$adminExists = (int) $stmt->fetch()['total'] > 0;

$errors = [];
$success = false;

if ($adminExists) {
    // Locked — nothing more to do here.
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Re-check to prevent a race condition creating two admins.
            $check = $pdo->query("SELECT COUNT(*) AS total FROM admins WHERE role = 'admin'")->fetch();
            if ((int) $check['total'] > 0) {
                $errors[] = 'An admin account already exists.';
                $adminExists = true;
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // This is always the very first Admin account, so it becomes
                // the Super Admin — the only account that can later see/use
                // "Manage Staff Accounts", even after others are promoted.
                $insert = $pdo->prepare("INSERT INTO admins (username, password, role, is_super_admin) VALUES (:u, :p, 'admin', 1)");
                $insert->execute(['u' => $username, 'p' => $hash]);
                $success = true;
            }
        }
    }
}

$pageTitle = 'Create First Admin Account';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-content">
    <div class="container narrow">
        <div class="auth-card">
            <h1>Create First Admin Account</h1>

            <?php if ($adminExists && !$success): ?>
                <div class="alert alert-error">
                    An admin account already exists. This setup page is now disabled for security.
                </div>
                <a href="<?= e(base_url()) ?>/admin/login.php" class="btn btn-primary">Go to Admin Login</a>

            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    Admin account created successfully. You can now log in.
                </div>
                <a href="<?= e(base_url()) ?>/admin/login.php" class="btn btn-primary">Go to Admin Login</a>

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

                    <button type="submit" class="btn btn-primary">Create Admin Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
