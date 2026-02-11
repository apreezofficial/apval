<?php
// backend_export/api/admin/requests.php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    response(['error' => 'Method not allowed'], 405);
}

$stmt = $pdo->query("SELECT * FROM upgrade_requests WHERE status = 'pending' ORDER BY createdAt DESC");
$requests = $stmt->fetchAll();

response($requests);
