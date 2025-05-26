<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE transaksi SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $_SESSION['success'] = "Transaksi berhasil dihapus.";
    } else {
        $_SESSION['error'] = "ID transaksi tidak valid.";
    }
} else {
    $_SESSION['error'] = "Metode request tidak valid.";
}

header('Location: index.php');
exit;
