<?php
// Takwimu za wageni (owner 2026-11-26). Super Admin anaona mifumo YOTE (tovuti, masomo, results, form1, ssms, kikoba);
// Admin wengine wanaona tovuti + masomo tu. Data inatoka includes/analytics.php (database site_analytics).
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role();

$pageTitle = 'Visitor Statistics — Admin Panel';
$activePage = 'statistics';
include __DIR__ . '/../includes/admin-header.php';
?>
<h1>Visitor Statistics (Takwimu za Wageni)</h1>
<?= nva_render(is_super_admin() ? ['website', 'masomo', 'results', 'form1', 'ssms', 'kikoba'] : ['website', 'masomo']) ?>
<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
