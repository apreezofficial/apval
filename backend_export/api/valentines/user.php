<?php
// backend_export/api/valentines/user.php
require_once '../db.php';

$userId = $_GET['id'] ?? '';
if (!$userId) response(['error' => 'User ID required'], 400);

$stmt = $pdo->prepare("SELECT * FROM valentines WHERE userId = ? ORDER BY createdAt DESC");
$stmt->execute([$userId]);
$results = $stmt->fetchAll();

$formatted = [];
foreach ($results as $row) {
    $data = json_decode($row['data'], true) ?? [];
    unset($row['data']);
    $formatted[] = array_merge($row, $data);
}

response($formatted);
