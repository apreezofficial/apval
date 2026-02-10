<?php
// backend_export/api/admin/reject.php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'Method not allowed'], 405);
}

$input = getJsonInput();
$requestId = $input['requestId'] ?? '';

if (!$requestId) {
    response(['error' => 'Missing request ID'], 400);
}

$stmt = $pdo->prepare("UPDATE upgrade_requests SET status = 'rejected' WHERE id = ?");
$stmt->execute([$requestId]);

if ($stmt->rowCount() > 0) {
    response(['success' => true, 'message' => 'Request rejected']);
} else {
    response(['error' => 'Request not found'], 404);
}
