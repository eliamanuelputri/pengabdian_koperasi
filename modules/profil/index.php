<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Profil Pengguna";
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<div class='alert alert-danger'>Anda belum login.</div>";
    require_once '../../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger'>Data pengguna tidak ditemukan.</div>";
    require_once '../../includes/footer.php';
    exit;
}
?>

<div class="container mt-4">
     <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Profil Saya</h2>
        <a href="../dashboard/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Aplikasi
        </a>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>

            <?php if (!empty($user['email'])): ?>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
            <?php endif; ?>

            <?php if (!empty($user['created_at'])): ?>
                <p><strong>Terdaftar Sejak:</strong> <?= date('d M Y', strtotime($user['created_at'])); ?></p>
            <?php endif; ?>

            <a href="edit.php" class="btn btn-warning">Ganti Password</a>
            <a href="../auth/logout.php" class="btn btn-danger ms-2">Logout</a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
