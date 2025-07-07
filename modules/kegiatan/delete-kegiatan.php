<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM kegiatan WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
}

header('Location: index.php');
exit;
?>
