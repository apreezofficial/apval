<?php
// backend_export/api/auth/login.php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'Method not allowed'], 405);
}

$input = getJsonInput();
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (!$email || !$password) {
    response(['error' => 'Email and password required'], 400);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || $user['password'] !== $password) { // In production use password_verify
    response(['error' => 'Invalid credentials'], 401);
}

unset($user['password']);
response($user);
