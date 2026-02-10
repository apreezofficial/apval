<?php
// backend_export/api/user/index.php
require_once '../db.php';

$id = $_GET['id'] ?? '';
if (!$id) response(['error' => 'User ID required'], 400);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    response(['error' => 'User not found'], 404);
}

unset($user['password']);
response($user);
