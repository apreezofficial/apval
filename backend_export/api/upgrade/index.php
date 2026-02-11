<?php
// backend_export/api/upgrade/index.php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'Method not allowed'], 405);
}

$input = getJsonInput();
$userId = $input['userId'] ?? '';
$userEmail = $input['userEmail'] ?? '';
$receipt = $input['receipt'] ?? '';
$timestamp = $input['timestamp'] ?? '';

if (!$userId || !$userEmail || !$receipt) {
    response(['error' => 'Missing fields'], 400);
}

$id = uuid();
$stmt = $pdo->prepare("INSERT INTO upgrade_requests (id, userId, userEmail, receipt, timestamp) VALUES (?, ?, ?, ?, ?)");

try {
    $stmt->execute([$id, $userId, $userEmail, $receipt, $timestamp]);
    response(['success' => true, 'message' => 'Request submitted']);
} catch (Exception $e) {
    response(['error' => 'Submission failed: ' . $e->getMessage()], 500);
}
