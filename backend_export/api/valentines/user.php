<?php
// backend_export/api/valentines/user.php
require_once '../db.php';

$userId = $_GET['id'] ?? '';
if (!$userId) response(['error' => 'User ID required'], 400);

$stmt = $pdo->prepare("
    SELECT v.*, COUNT(vw.id) as view_count 
    FROM valentines v 
    LEFT JOIN valentine_views vw ON v.id = vw.valentineId 
    WHERE v.userId = ? 
    GROUP BY v.id 
    ORDER BY v.createdAt DESC
");
$stmt->execute([$userId]);
$results = $stmt->fetchAll();

$formatted = [];
foreach ($results as $row) {
    $data = json_decode($row['data'], true) ?? [];
    unset($row['data']);
    $formatted[] = array_merge($row, $data);
}

response($formatted);
