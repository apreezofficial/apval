<?php
require_once '../db.php';

try {
    // Total Valentines
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM valentines");
    $valentinesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Pending Upgrades
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM upgrade_requests WHERE status = 'pending'");
    $pendingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Premium Users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE subscriptionTier = 'premium'");
    $premiumCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    response([
        'valentines' => (int)$valentinesCount,
        'users' => (int)$usersCount,
        'pending' => (int)$pendingCount,
        'premium' => (int)$premiumCount
    ]);

} catch (PDOException $e) {
    response(['error' => $e->getMessage()], 500);
}
?>
