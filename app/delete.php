<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

header('Location: index.php');
exit;
