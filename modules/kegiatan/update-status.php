<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $current = $_POST['current_status'] ?? '';

    if ($id && in_array($current, ['rencana', 'berjalan', 'selesai'])) {
        $next = [
            'rencana' => 'berjalan',
            'berjalan' => 'selesai',
            'selesai' => 'rencana'
        ];

        $new_status = $next[$current];

        $stmt = $pdo->prepare("UPDATE kegiatan SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_status, $id, $_SESSION['user_id']]);

        echo $new_status;
        exit;
    }
}
http_response_code(400);
echo "invalid request";
