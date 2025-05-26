<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Edit Transaksi";

$user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: keuangan.php');
    exit;
}

// Ambil data transaksi, pastikan milik user dan belum dihapus
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
$stmt->execute([$id, $user_id]);
$trx = $stmt->fetch();

if (!$trx) {
    $_SESSION['error'] = "Transaksi tidak ditemukan.";
    header('Location: keuangan.php');
    exit;
}

// Ambil kategori user untuk dropdown
$stmt = $pdo->prepare("SELECT * FROM kategori WHERE user_id = ? ORDER BY nama");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $kategori_id = $_POST['kategori_id'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    try {
        // Validasi kategori sesuai jenis dan user
        $stmt = $pdo->prepare("SELECT id FROM kategori WHERE id = ? AND jenis = ? AND user_id = ?");
        $stmt->execute([$kategori_id, $jenis, $user_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Kategori tidak valid untuk jenis transaksi ini");
        }

        // Update transaksi
        $stmt = $pdo->prepare("UPDATE transaksi SET tanggal = ?, jenis = ?, kategori_id = ?, jumlah = ?, keterangan = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$tanggal, $jenis, $kategori_id, $jumlah, $keterangan, $id, $user_id]);

        $_SESSION['success'] = "Transaksi berhasil diperbarui!";
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Edit Transaksi</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($trx['tanggal']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="jenis" class="form-label">Jenis</label>
            <select id="jenis" name="jenis" class="form-select" required>
                <option value="pemasukan" <?php echo $trx['jenis'] == 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                <option value="pengeluaran" <?php echo $trx['jenis'] == 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="kategori_id" class="form-label">Kategori</label>
            <select id="kategori_id" name="kategori_id" class="form-select" required>
                <option value="">Pilih Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $trx['kategori_id'] == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nama']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah (Rp)</label>
            <input type="number" class="form-control" id="jumlah" name="jumlah" value="<?php echo htmlspecialchars($trx['jumlah']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo htmlspecialchars($trx['keterangan']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
