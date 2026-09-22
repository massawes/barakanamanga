<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin_login();
require_admin_role(); // Staff accounts may upload/edit/view, but never delete.

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('error', 'Invalid delete request.');
    redirect(base_url() . '/admin/manage-resources.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flash_set('error', 'Resource not found.');
    redirect(base_url() . '/admin/manage-resources.php');
}

$stmt = $pdo->prepare('SELECT * FROM resources WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$resource = $stmt->fetch();

if ($resource) {
    $delete = $pdo->prepare('DELETE FROM resources WHERE id = :id');
    $delete->execute(['id' => $id]);
    // Remove the physical file only after the database record is gone,
    // so we never end up with a database row pointing at nothing.
    delete_resource_file($resource['file_path']);
    flash_set('success', 'Resource deleted successfully.');
} else {
    flash_set('error', 'Resource not found.');
}

redirect(base_url() . '/admin/manage-resources.php');
