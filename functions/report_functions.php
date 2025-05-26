<?php

/**
 * Menghitung saldo akhir keseluruhan (total semua transaksi hingga bulan tertentu)
 * @param int $user_id ID User
 * @param string $month Bulan dalam format YYYY-MM
 * @return float Saldo akhir
 */
function getOverallBalance($user_id, $month) {
    global $pdo;
    
    // Hitung total pemasukan hingga bulan ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total 
                          FROM transactions 
                          WHERE user_id = ? AND type = 'income' AND DATE_FORMAT(date, '%Y-%m') <= ?");
    $stmt->execute([$user_id, $month]);
    $total_income = $stmt->fetchColumn();
    
    // Hitung total pengeluaran hingga bulan ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total 
                          FROM transactions 
                          WHERE user_id = ? AND type = 'expense' AND DATE_FORMAT(date, '%Y-%m') <= ?");
    $stmt->execute([$user_id, $month]);
    $total_expense = $stmt->fetchColumn();
    
    return $total_income - $total_expense;
}

?>