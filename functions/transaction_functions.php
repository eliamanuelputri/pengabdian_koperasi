<?php
function addTransaction($user_id, $amount, $type, $category_id, $description, $date) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, category_id, description, date) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $amount, $type, $category_id, $description, $date]);
}

// Update fungsi getFilteredTransactions untuk support pagination
function getFilteredTransactions($user_id, $month, $category_id = '', $type = '', $limit = null, $offset = 0) {
    global $pdo;
    
    $sql = "SELECT t.*, c.name as category_name 
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND DATE_FORMAT(t.date, '%Y-%m') = ?";
    
    $params = [$user_id, $month];
    
    if (!empty($category_id)) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_id;
    }
    
    if (!empty($type)) {
        $sql .= " AND t.type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY t.date DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$offset . ", " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMonthlySummary($user_id, $month) {
    global $pdo;
    
    // Total pemasukan
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total 
                          FROM transactions 
                          WHERE user_id = ? AND type = 'income' AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $month]);
    $total_income = $stmt->fetchColumn();
    
    // Total pengeluaran
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total 
                          FROM transactions 
                          WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $month]);
    $total_expense = $stmt->fetchColumn();
    
    // Saldo
    $balance = $total_income - $total_expense;
    
    return [
        'total_income' => $total_income,
        'total_expense' => $total_expense,
        'balance' => $balance
    ];
}

function getExpenseSuggestions($user_id, $month) {
    global $pdo;
    $suggestions = [];
    
    // 1. Cari kategori pengeluaran terbesar
    $stmt = $pdo->prepare("SELECT c.name, SUM(t.amount) as total
                          FROM transactions t
                          JOIN categories c ON t.category_id = c.id
                          WHERE t.user_id = ? AND t.type = 'expense' AND DATE_FORMAT(t.date, '%Y-%m') = ?
                          GROUP BY t.category_id
                          ORDER BY total DESC
                          LIMIT 1");
    $stmt->execute([$user_id, $month]);
    $top_category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($top_category && $top_category['total'] > 0) {
        $suggestions[] = "Fokus pada pengurangan pengeluaran untuk kategori <strong>{$top_category['name']}</strong> (Rp " . number_format($top_category['total'], 0, ',', '.') . ")";
    }
    
    // 2. Bandingkan dengan bulan sebelumnya
    $prev_month = date('Y-m', strtotime($month . ' -1 month'));
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total 
                          FROM transactions 
                          WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $prev_month]);
    $prev_month_expense = $stmt->fetchColumn();
    
    $stmt->execute([$user_id, $month]);
    $current_month_expense = $stmt->fetchColumn();
    
    if ($current_month_expense > $prev_month_expense && $prev_month_expense > 0) {
        $increase = (($current_month_expense - $prev_month_expense) / $prev_month_expense) * 100;
        $suggestions[] = "Pengeluaran bulan ini meningkat " . round($increase, 1) . "% dibanding bulan lalu";
    }
    
    // 3. Cari transaksi besar (> 1 juta)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count 
                          FROM transactions 
                          WHERE user_id = ? AND type = 'expense' AND amount > 1000000 AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $month]);
    $big_expenses = $stmt->fetchColumn();
    
    if ($big_expenses > 0) {
        $suggestions[] = "Anda memiliki $big_expenses pengeluaran besar (di atas Rp 1.000.000) bulan ini. Pertimbangkan untuk mengevaluasi kembali.";
    }
    
    return $suggestions;
}

function countFilteredTransactions($user_id, $month, $category_id = '', $type = '') {
    global $pdo;
    
    $sql = "SELECT COUNT(*) 
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND DATE_FORMAT(t.date, '%Y-%m') = ?";
    
    $params = [$user_id, $month];
    
    if (!empty($category_id)) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_id;
    }
    
    if (!empty($type)) {
        $sql .= " AND t.type = ?";
        $params[] = $type;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchColumn();
}

function getTransactionById($transaction_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT t.*, c.name as category_name, c.type as category_type 
                          FROM transactions t
                          JOIN categories c ON t.category_id = c.id
                          WHERE t.id = ? AND t.user_id = ?");
    $stmt->execute([$transaction_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateTransaction($transaction_id, $user_id, $amount, $type, $category_id, $description, $date) {
    global $pdo;
    
    // Pastikan transaksi milik user yang bersangkutan
    $stmt = $pdo->prepare("UPDATE transactions 
                          SET amount = ?, type = ?, category_id = ?, description = ?, date = ?
                          WHERE id = ? AND user_id = ?");
    return $stmt->execute([
        $amount, 
        $type, 
        $category_id, 
        $description, 
        $date,
        $transaction_id,
        $user_id
    ]);
}
?>