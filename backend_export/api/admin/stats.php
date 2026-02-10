<?php
require_once '../db.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();

    // Total Valentines
    $stmt = $conn->query("SELECT COUNT(*) as count FROM valentines");
    $valentinesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total Users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Pending Upgrades
    $stmt = $conn->query("SELECT COUNT(*) as count FROM upgrade_requests WHERE status = 'pending'");
    $pendingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Premium Users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE subscriptionTier = 'premium'");
    $premiumCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'valentines' => $valentinesCount,
        'users' => $usersCount,
        'pending' => $pendingCount,
        'premium' => $premiumCount
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
