<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (is_admin_logged_in()) {
    redirect(base_url() . '/admin/dashboard.php');
}

// If no admin account exists yet, send the user to first-time setup.
$adminCount = (int) $pdo->query("SELECT COUNT(*) AS total FROM admins WHERE role = 'admin'")->fetch()['total'];
if ($adminCount === 0) {
    redirect(base_url() . '/admin/create-admin.php');
}

$error = null;

// Very simple brute-force slow-down: track failed attempts in session.
if (empty($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_locked_until'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (time() < ($_SESSION['login_locked_until'] ?? 0)) {
        $error = 'Too many failed attempts. Please wait a minute before trying again.';
    } elseif (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['login_attempts'] = 0;
            admin_login((int) $admin['id'], $admin['username'], $admin['role'], (bool) ($admin['is_super_admin'] ?? false));
            redirect(base_url() . '/admin/dashboard.php');
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_locked_until'] = time() + 60;
                $_SESSION['login_attempts'] = 0;
            }
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Admin Login — Namanga Digital Resource Centre';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-content">
    <div class="container narrow">
        <div class="auth-card">
            <h1>Admin Login</h1>
            <p class="muted">Sign in to manage learning resources.</p>

            <?php if ($msg = flash_get('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <?= csrf_field() ?>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus
                       value="<?= e($_POST['username'] ?? '') ?>">

                <label for="password">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle-btn" aria-label="Show password" tabindex="-1">
                        <?= svg_icon('eye', 'icon-svg icon-eye') ?>
                        <?= svg_icon('eye-off', 'icon-svg icon-eye-off') ?>
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-alt-action">
                New staff member? <a href="<?= e(base_url()) ?>/admin/register.php">Register here</a> to get an account for uploading and editing resources.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
