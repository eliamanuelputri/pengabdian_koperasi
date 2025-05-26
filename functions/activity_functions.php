<?php
function addActivity($user_id, $title, $description, $date) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO activities (user_id, title, description, date) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $description, $date]);
}

function getActivitiesByDate($user_id, $date) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM activities 
                          WHERE user_id = ? AND date = ? 
                          ORDER BY created_at DESC");
    $stmt->execute([$user_id, $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentActivities($user_id, $limit = 5) {
    global $pdo;
    
    // Perbaikan: Gunakan parameter binding yang benar untuk LIMIT
    $stmt = $pdo->prepare("SELECT * FROM activities 
                          WHERE user_id = ? 
                          ORDER BY date DESC, created_at DESC 
                          LIMIT ?");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>