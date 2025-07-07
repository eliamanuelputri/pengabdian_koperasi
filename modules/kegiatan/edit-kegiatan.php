<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Edit Kegiatan";

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<div class='alert alert-danger'>ID tidak valid.</div>";
    require_once '../../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM kegiatan WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$kegiatan = $stmt->fetch();

if (!$kegiatan) {
    echo "<div class='alert alert-danger'>Kegiatan tidak ditemukan.</div>";
    require_once '../../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $tanggal = $_POST['tanggal'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE kegiatan SET judul = ?, tanggal = ?, waktu_mulai = ?, waktu_selesai = ?, deskripsi = ?, status = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$judul, $tanggal, $waktu_mulai, $waktu_selesai, $deskripsi, $status, $id, $user_id]);

    header('Location: index.php');
    exit;
}
?>

<div class="container mt-4">
    <h2>Edit Kegiatan</h2>
    <form method="post">
        <div class="mb-3">
            <label>Judul</label>
            <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($kegiatan['judul']); ?>"
                required>
        </div>
        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= $kegiatan['tanggal']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Waktu Mulai</label>
            <input type="time" name="waktu_mulai" class="form-control" value="<?= $kegiatan['waktu_mulai']; ?>"
                required>
        </div>
        <div class="mb-3">
            <label>Waktu Selesai</label>
            <input type="time" name="waktu_selesai" class="form-control" value="<?= $kegiatan['waktu_selesai']; ?>"
                required>
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($kegiatan['deskripsi']); ?></textarea>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="rencana" <?= $kegiatan['status'] === 'rencana' ? 'selected' : '' ?>>Rencana</option>
                <option value="berjalan" <?= $kegiatan['status'] === 'berjalan' ? 'selected' : '' ?>>Berjalan</option>
                <option value="selesai" <?= $kegiatan['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>