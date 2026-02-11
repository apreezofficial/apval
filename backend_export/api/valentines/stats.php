<?php
// backend_export/api/valentines/stats.php
require_once '../db.php';

$id = $_GET['id'] ?? '';
if (!$id) response(['error' => 'ID required'], 400);

// Get views for this valentine
$stmt = $pdo->prepare("SELECT * FROM valentine_views WHERE valentineId = ? ORDER BY viewed_at DESC");
$stmt->execute([$id]);
$views = $stmt->fetchAll();

// We can do basic device parsing here
foreach ($views as &$view) {
    if (!$view['country'] && $view['viewer_ip']) {
        // Try simple geolocation
        try {
            $geo = @json_decode(file_get_contents("http://ip-api.com/json/{$view['viewer_ip']}?fields=status,country,city"), true);
            if ($geo && $geo['status'] === 'success') {
                $view['country'] = $geo['country'];
                $view['city'] = $geo['city'];
                
                // Save back to DB so we don't fetch every time
                $update = $pdo->prepare("UPDATE valentine_views SET country = ?, city = ? WHERE id = ?");
                $update->execute([$view['country'], $view['city'], $view['id']]);
            }
        } catch (Exception $e) {}
    }

    $ua = $view['user_agent'];
    $view['device'] = 'Desktop';
    if (preg_match('/mobile|android|iphone|ipad|phone/i', $ua)) {
        $view['device'] = 'Mobile';
    }
    
    // Very basic browser detection
    if (preg_match('/chrome/i', $ua)) $view['browser'] = 'Chrome';
    elseif (preg_match('/firefox/i', $ua)) $view['browser'] = 'Firefox';
    elseif (preg_match('/safari/i', $ua)) $view['browser'] = 'Safari';
    elseif (preg_match('/edg/i', $ua)) $view['browser'] = 'Edge';
    else $view['browser'] = 'Other';
}

response($views);
?>
