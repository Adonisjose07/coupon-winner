<?php
require_once __DIR__ . '/db.php';

function getSetting($key)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

function updateSetting($key, $value)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = ?");
    $stmt->execute([$key, $value, $value]);
}

function performDraw()
{
    global $pdo;

    // Check if already drawn
    if (getSetting('draw_completed') == '1') {
        return ["status" => "error", "message" => "Draw already completed!"];
    }

    // 1. Get all participants
    $stmt = $pdo->query("SELECT id, name, email, referred_by FROM participants");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($participants) == 0) {
        return ["status" => "error", "message" => "No participants found."];
    }

    // 2. Build Weighted Pool
    $pool = [];
    $referral_counts = [];

    // First count referrals efficiently
    $refStmt = $pdo->query("SELECT referred_by, COUNT(*) as count FROM participants WHERE referred_by IS NOT NULL GROUP BY referred_by");
    $refData = $refStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [user_id => count]

    foreach ($participants as $p) {
        $id = $p['id'];
        $entries = 1 + (isset($refData[$id]) ? $refData[$id] : 0);

        // Add ID to pool 'entries' times
        for ($i = 0; $i < $entries; $i++) {
            $pool[] = $id;
        }
    }

    // 3. Pick Winner
    $winner_index = array_rand($pool);
    $winner_id = $pool[$winner_index];

    // Get Winner Details
    $winner = null;
    foreach ($participants as $p) {
        if ($p['id'] == $winner_id) {
            $winner = $p;
            break;
        }
    }

    // 4. Update Settings
    updateSetting('winner_id', $winner_id);
    updateSetting('draw_completed', '1');

    // 5. Send Emails (Simulation)
    $log = [];
    foreach ($participants as $p) {
        $to = $p['email'];
        $subject = ($p['id'] == $winner_id) ? "YOU WON the $200 Amazon Gift Card!" : "Update on the Car Sweepstakes";

        if ($p['id'] == $winner_id) {
            $message = "Congratulations {$p['name']}! You have been selected as the winner.";
        } else {
            $message = "Hi {$p['name']}, unfortunately you didn't win this time. Stay tuned for more!";
        }

        // Simulate sending
        // mail($to, $subject, $message); 
        $log[] = "Sent to {$to}: " . ($p['id'] == $winner_id ? "[WINNER]" : "[Non-Winner]");
    }

    return ["status" => "success", "winner" => $winner, "log" => $log];
}
?>