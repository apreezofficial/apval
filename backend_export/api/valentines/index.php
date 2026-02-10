<?php
// backend_export/api/valentines/index.php
require_once '../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = getJsonInput();

if ($method === 'POST') {
    $userId = $input['userId'] ?? '';
    if (!$userId) response(['error' => 'User ID required'], 400);

    $id = uuid();
    $templateId = $input['templateId'] ?? '';
    $recipient = $input['recipient'] ?? '';
    $sender = $input['sender'] ?? '';
    
    // Store everything else in data
    $data = $input;
    unset($data['userId']); // redundant
    
    $stmt = $pdo->prepare("INSERT INTO valentines (id, userId, templateId, recipient, sender, data) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $userId, $templateId, $recipient, $sender, json_encode($data)]);
    response(['id' => $id]);

} elseif ($method === 'PUT') {
    $id = $input['id'] ?? '';
    $userId = $input['userId'] ?? '';
    if (!$id) response(['error' => 'ID required'], 400);

    $customSlug = $input['customSlug'] ?? null;
    $templateId = $input['templateId'] ?? '';
    $recipient = $input['recipient'] ?? '';
    $sender = $input['sender'] ?? '';

    if ($customSlug) {
        // Validation logic
        if (!preg_match('/^[a-z0-9-]+$/', $customSlug)) {
            response(['error' => 'Slug must be lowercase alphanumeric with hyphens'], 400);
        }

        // Check Premium
        $stmt = $pdo->prepare("SELECT subscriptionTier FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user || $user['subscriptionTier'] !== 'premium') {
            response(['error' => 'Custom slugs are a Premium feature'], 403);
        }

        // Check Uniqueness
        $stmt = $pdo->prepare("SELECT id FROM valentines WHERE customSlug = ? AND id != ?");
        $stmt->execute([$customSlug, $id]);
        if ($stmt->fetch()) {
            response(['error' => 'This custom link is already taken'], 409);
        }
    }

    $data = $input;
    unset($data['id'], $data['userId']);

    $stmt = $pdo->prepare("UPDATE valentines SET templateId = ?, recipient = ?, sender = ?, customSlug = ?, data = ? WHERE id = ?");
    $stmt->execute([$templateId, $recipient, $sender, $customSlug, json_encode($data), $id]);
    response(['success' => true]);

} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if (!$id) response(['error' => 'ID required'], 400);

    $stmt = $pdo->prepare("DELETE FROM valentines WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        response(['success' => true]);
    } else {
        response(['error' => 'Valentine not found'], 404);
    }
} else {
    response(['error' => 'Method not allowed'], 405);
}
