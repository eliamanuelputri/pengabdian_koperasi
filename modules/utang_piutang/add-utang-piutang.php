<?php
// Memanggil file koneksi dan otentikasi
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

$message = '';

// Proses formulir saat tombol 'submit' ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan session
    $user_id = $_SESSION['user_id'];
    $jenis = $_POST['jenis'];
    $pihak_terkait = trim($_POST['pihak_terkait']);
    $jumlah = $_POST['jumlah'];
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    // Jadikan tanggal jatuh tempo opsional (boleh null)
    $tanggal_jatuh_tempo = !empty($_POST['tanggal_jatuh_tempo']) ? $_POST['tanggal_jatuh_tempo'] : null;
    $keterangan = trim($_POST['keterangan']);

    // Validasi dasar
    if (empty($jenis) || empty($pihak_terkait) || empty($jumlah) || empty($tanggal_transaksi)) {
        $message = '<div class="alert alert-danger">Semua kolom yang ditandai * wajib diisi.</div>';
    } else {
        try {
            // Query untuk memasukkan data baru menggunakan prepared statement
            $sql = "INSERT INTO utang_piutang (user_id, jenis, pihak_terkait, jumlah, tanggal_transaksi, tanggal_jatuh_tempo, keterangan, status) 
                    VALUES (:user_id, :jenis, :pihak_terkait, :jumlah, :tanggal_transaksi, :tanggal_jatuh_tempo, :keterangan, 'belum lunas')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'jenis' => $jenis,
                'pihak_terkait' => $pihak_terkait,
                'jumlah' => $jumlah,
                'tanggal_transaksi' => $tanggal_transaksi,
                'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                'keterangan' => $keterangan
            ]);

            // Alihkan ke halaman utama modul dengan notifikasi sukses
            header("Location: index.php?status=add_success");
            exit();

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Terjadi kesalahan: ' . $e->getMessage() . '</div>';
        }
    }
}

$pageTitle = "Tambah Utang/Piutang";
require_once '../../includes/header.php'; // Asumsi header.php ada di sini
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Tambah Data Utang/Piutang</h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    <form method="POST" action="add-utang-piutang.php">
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis *</label>
                            <select class="form-select" id="jenis" name="jenis" required>
                                <option value="piutang">Piutang (Orang lain berutang ke saya)</option>
                                <option value="utang">Utang (Saya berutang ke orang lain)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pihak_terkait" class="form-label">Nama Pihak Terkait *</label>
                            <input type="text" class="form-control" id="pihak_terkait" name="pihak_terkait" placeholder="Contoh: Budi, Supplier Jaya" required>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah (Nominal) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="jumlah" name="jumlah" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi *</label>
                                <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Utang untuk pembelian bahan baku"></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                             <a href="index.php" class="btn btn-secondary">Batal</a>
                             <button type="submit" class="btn btn-primary">Simpan</button>
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

<?php
require_once '../../includes/footer.php'; // Asumsi footer.php ada di sini
?>