<?php
function addCategory($user_id, $name, $type) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $name, $type]);
    } catch (PDOException $e) {
        // Kategori mungkin sudah ada
        return false;
    }
}

function getUserCategories($user_id, $type = null) {
    global $pdo;
    
    $sql = "SELECT id, name, type FROM categories WHERE user_id = ?";
    $params = [$user_id];
    
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUserCategories($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, name, type FROM categories 
                          WHERE user_id = ? 
                          ORDER BY type, name");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById($category_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$category_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>