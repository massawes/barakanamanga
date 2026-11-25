<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
// Owner 2026-11-26: Admins may delete any resource; a teacher only the files they uploaded (checked below).

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid delete request.');
    redirect(resource_list_url());
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flash_set('error', 'Resource not found.');
    redirect(resource_list_url());
}

$stmt = $pdo->prepare('SELECT * FROM resources WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$resource = $stmt->fetch();

if ($resource && !can_manage_resource($resource)) {
    flash_set('error', 'You can only delete the files you uploaded yourself.');
} elseif ($resource) {
    $delete = $pdo->prepare('DELETE FROM resources WHERE id = :id');
    $delete->execute(['id' => $id]);
    // Remove the physical file only after the database record is gone,
    // so we never end up with a database row pointing at nothing.
    delete_resource_file($resource['file_path']);
    flash_set('success', 'Resource deleted successfully.');
} else {
    flash_set('error', 'Resource not found.');
}

redirect(resource_list_url());
