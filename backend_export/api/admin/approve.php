<?php
// backend_export/api/admin/approve.php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'Method not allowed'], 405);
}

$input = getJsonInput();
$requestId = $input['requestId'] ?? '';
$userId = $input['userId'] ?? '';

if (!$requestId || !$userId) {
    response(['error' => 'Missing fields'], 400);
}

try {
    $pdo->beginTransaction();

    // 1. Update User to Premium
    $stmt = $pdo->prepare("UPDATE users SET subscriptionTier = 'premium' WHERE id = ?");
    $stmt->execute([$userId]);

    // 2. Mark Request as Approved (or delete)
    $stmt = $pdo->prepare("UPDATE upgrade_requests SET status = 'approved' WHERE id = ?");
    $stmt->execute([$requestId]);

    $pdo->commit();
    response(['success' => true, 'message' => 'User upgraded successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    response(['error' => 'Approval failed: ' . $e->getMessage()], 500);
}
