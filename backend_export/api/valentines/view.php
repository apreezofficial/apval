<?php
// backend_export/api/valentines/view.php
require_once '../db.php';

$id = $_GET['id'] ?? '';
if (!$id) response(['error' => 'ID or Slug required'], 400);

// Try ID first
$stmt = $pdo->prepare("SELECT * FROM valentines WHERE id = ?");
$stmt->execute([$id]);
$val = $stmt->fetch();

// Try Slug if not found
if (!$val) {
    $stmt = $pdo->prepare("SELECT * FROM valentines WHERE customSlug = ?");
    $stmt->execute([$id]);
    $val = $stmt->fetch();
}

if (!$val) {
    response(['error' => 'Valentine not found'], 404);
}

// Merge JSON data into the result
$data = json_decode($val['data'], true) ?? [];
unset($val['data']);
response(array_merge($val, $data));
