<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

$message = '';
$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Proses update saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis = $_POST['jenis'];
    $pihak_terkait = trim($_POST['pihak_terkait']);
    $jumlah = $_POST['jumlah'];
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $tanggal_jatuh_tempo = !empty($_POST['tanggal_jatuh_tempo']) ? $_POST['tanggal_jatuh_tempo'] : null;
    $keterangan = trim($_POST['keterangan']);

    try {
        $sql = "UPDATE utang_piutang 
                SET jenis = :jenis, pihak_terkait = :pihak_terkait, jumlah = :jumlah, tanggal_transaksi = :tanggal_transaksi, tanggal_jatuh_tempo = :tanggal_jatuh_tempo, keterangan = :keterangan 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'jenis' => $jenis,
            'pihak_terkait' => $pihak_terkait,
            'jumlah' => $jumlah,
            'tanggal_transaksi' => $tanggal_transaksi,
            'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
            'keterangan' => $keterangan,
            'id' => $id,
            'user_id' => $user_id
        ]);
        header("Location: index.php?status=edit_success");
        exit();
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Gagal memperbarui data: ' . $e->getMessage() . '</div>';
    }
}

// Ambil data awal untuk ditampilkan di form
try {
    $sql = "SELECT * FROM utang_piutang WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        header("Location: index.php"); // Data tidak ditemukan atau bukan milik user
        exit();
    }
} catch (PDOException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}

$pageTitle = "Edit Utang/Piutang";
require_once '../../includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Data Utang/Piutang</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    <form method="POST" action="edit-utang-piutang.php?id=<?= $id ?>">
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis *</label>
                            <select class="form-select" id="jenis" name="jenis" required>
                                <option value="piutang" <?= ($item['jenis'] == 'piutang') ? 'selected' : '' ?>>Piutang</option>
                                <option value="utang" <?= ($item['jenis'] == 'utang') ? 'selected' : '' ?>>Utang</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pihak_terkait" class="form-label">Nama Pihak Terkait *</label>
                            <input type="text" class="form-control" id="pihak_terkait" name="pihak_terkait" value="<?= htmlspecialchars($item['pihak_terkait']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah (Nominal) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="jumlah" name="jumlah" required value="<?= htmlspecialchars($item['jumlah']) ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi *</label>
                                <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="<?= htmlspecialchars($item['tanggal_transaksi']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="<?= htmlspecialchars($item['tanggal_jatuh_tempo']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= htmlspecialchars($item['keterangan']) ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                             <a href="index.php" class="btn btn-secondary">Batal</a>
                             <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const inputJumlah = document.getElementById('jumlah');

    inputJumlah.addEventListener('input', function(e) {
        let value = this.value.replace(/\./g, '').replace(/[^\d]/g, '');
        if (value) {
            this.value = Number(value).toLocaleString('id-ID');
        } else {
            this.value = '';
        }
    });

    document.querySelector('form').addEventListener('submit', function () {
        inputJumlah.value = inputJumlah.value.replace(/\./g, '').replace(/,/g, '');
    });
</script>

<?php require_once '../../includes/footer.php'; ?>