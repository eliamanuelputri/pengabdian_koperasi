<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Ganti Password";

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Ambil password lama dari DB
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password_lama, $user['password'])) {
        $message = "<div class='alert alert-danger'>Password lama salah.</div>";
    } elseif (strlen($password_baru) < 6) {
        $message = "<div class='alert alert-danger'>Password baru minimal 6 karakter.</div>";
    } elseif ($password_baru !== $konfirmasi) {
        $message = "<div class='alert alert-danger'>Konfirmasi password tidak cocok.</div>";
    } else {
        // Update password
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash_baru, $user_id]);

        $message = "<div class='alert alert-success'>Password berhasil diperbarui.</div>";
    }
}
?>

<div class="container mt-4">
    <h2>Ganti Password</h2>

    <?= $message ?>

    <form method="post" class="mt-3">
        <div class="mb-3">
            <label for="password_lama" class="form-label">Password Lama</label>
            <input type="password" name="password_lama" id="password_lama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password_baru" class="form-label">Password Baru</label>
            <input type="password" name="password_baru" id="password_baru" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label for="konfirmasi" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi" id="konfirmasi" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
