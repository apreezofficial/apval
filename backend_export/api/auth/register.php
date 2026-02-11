<?php
// backend_export/api/auth/register.php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'Method not allowed'], 405);
}

$input = getJsonInput();
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$name = $input['name'] ?? '';

if (!$email || !$password) {
    response(['error' => 'Email and password required'], 400);
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    response(['error' => 'User already exists'], 400);
}

$id = uuid();
$stmt = $pdo->prepare("INSERT INTO users (id, email, password, name) VALUES (?, ?, ?, ?)");
try {
    $stmt->execute([$id, $email, $password, $name]);
    response(['id' => $id, 'name' => $name, 'email' => $email]);
} catch (Exception $e) {
    response(['error' => 'Registration failed: ' . $e->getMessage()], 500);
}
