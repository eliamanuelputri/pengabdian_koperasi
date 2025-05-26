<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Tambah Transaksi";

$user_id = $_SESSION['user_id'];
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'pengeluaran'; // Default pengeluaran

// Ambil kategori berdasarkan jenis
$stmt = $pdo->prepare("SELECT * FROM kategori WHERE user_id = ? AND jenis = ? ORDER BY nama");
$stmt->execute([$user_id, $jenis]);
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $kategori_id = $_POST['kategori_id'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    
    try {
        // Validasi kategori sesuai jenis
        $stmt = $pdo->prepare("SELECT id FROM kategori WHERE id = ? AND jenis = ? AND user_id = ?");
        $stmt->execute([$kategori_id, $jenis, $user_id]);
        if (!$stmt->fetch()) {
            throw new PDOException("Kategori tidak valid untuk jenis transaksi ini");
        }
        
        $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, tanggal, jenis, kategori_id, jumlah, keterangan) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $tanggal, $jenis, $kategori_id, $jumlah, $keterangan]);
        
        $_SESSION['success'] = "Transaksi berhasil ditambahkan!";
        header('Location: ../keuangan/');
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menambahkan transaksi: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Transaksi</h2>
        <a href="../keuangan/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Keuangan
        </a>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" 
                           value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis</label>
                    <select class="form-select" id="jenis" name="jenis" required onchange="updateCategories()">
                        <option value="pemasukan" <?php echo $jenis == 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                        <option value="pengeluaran" <?php echo $jenis == 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="jumlah_display" class="form-label">Jumlah (Rp)</label>
                    <input type="text" class="form-control" id="jumlah_display" required>
                    <input type="hidden" id="jumlah" name="jumlah" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="../keuangan/" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
function updateCategories() {
    const jenis = document.getElementById('jenis').value;
    window.location.href = 'add-transaksi.php?jenis=' + jenis;
}

function formatRupiah(angka) {
    return angka.replace(/\D/g, "") // hapus non-digit
        .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

document.addEventListener('DOMContentLoaded', function () {
    const displayInput = document.getElementById('jumlah_display');
    const hiddenInput = document.getElementById('jumlah');

    displayInput.addEventListener('input', function () {
        const raw = displayInput.value.replace(/\./g, '').replace(/\D/g, '');
        hiddenInput.value = raw;
        displayInput.value = formatRupiah(raw);
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>