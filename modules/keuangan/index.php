<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Keuangan";

$user_id = $_SESSION['user_id'];

// Tentukan rentang bulan ini
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

// Ambil transaksi terbaru bulan ini (exclude soft deleted)
$stmt = $pdo->prepare("SELECT t.*, k.nama as kategori_nama 
                      FROM transaksi t 
                      JOIN kategori k ON t.kategori_id = k.id 
                      WHERE t.user_id = ? 
                      AND k.jenis = t.jenis
                      AND t.tanggal BETWEEN ? AND ?
                      AND t.deleted_at IS NULL
                      ORDER BY t.tanggal DESC 
                      LIMIT 10");
$stmt->execute([$user_id, $start_date, $end_date]);
$transactions = $stmt->fetchAll();

// Hitung saldo bulan ini (exclude soft deleted)
$stmt = $pdo->prepare("SELECT 
                        SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_pemasukan,
                        SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_pengeluaran
                      FROM transaksi 
                      WHERE user_id = ?
                      AND tanggal BETWEEN ? AND ?
                      AND deleted_at IS NULL");
$stmt->execute([$user_id, $start_date, $end_date]);
$saldo = $stmt->fetch();

// Hitung saldo akhir (akumulasi seluruh transaksi tanpa filter tanggal, exclude soft deleted)
$stmt_final = $pdo->prepare("SELECT 
                        SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_pemasukan,
                        SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_pengeluaran
                      FROM transaksi 
                      WHERE user_id = ?
                      AND deleted_at IS NULL");
$stmt_final->execute([$user_id]);
$final_totals = $stmt_final->fetch();

$final_balance = $final_totals['total_pemasukan'] - $final_totals['total_pengeluaran'];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Keuangan</h2>
        <a href="../dashboard/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Aplikasi
        </a>
    </div>
    <p class="text-muted">Menampilkan saldo dan transaksi pada bulan ini (<?php echo date('F Y'); ?>).</p>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pemasukan Bulan Ini</h5>
                    <p class="card-text">Rp <?php echo number_format($saldo['total_pemasukan'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pengeluaran Bulan Ini</h5>
                    <p class="card-text">Rp <?php echo number_format($saldo['total_pengeluaran'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Saldo Bulan Ini</h5>
                    <p class="card-text">Rp <?php echo number_format($saldo['total_pemasukan'] - $saldo['total_pengeluaran'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Saldo Akhir</h5>
                    <p class="card-text">Rp <?php echo number_format($final_balance, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Transaksi Terakhir Bulan Ini</h3>
        <div class="row g-2">
          <div class="col-12 col-sm-auto">
            <a href="add-transaksi.php" class="btn btn-primary w-100">Tambah Transaksi</a>
          </div>
          <div class="col-12 col-sm-auto">
            <a href="laporan.php" class="btn btn-info w-100">Lihat Laporan</a>
          </div>
          <div class="col-12 col-sm-auto">
            <a href="kategori.php" class="btn btn-secondary w-100">Kelola Kategori</a>
          </div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="transaksiTable" class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($transactions as $trx): ?>
                <tr>
                    <td><?php echo date('d M Y', strtotime($trx['tanggal'])); ?></td>
                    <td><?php echo htmlspecialchars($trx['kategori_nama']); ?></td>
                    <td>
                        <span class="badge <?php echo $trx['jenis'] == 'pemasukan' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo ucfirst($trx['jenis']); ?>
                        </span>
                    </td>
                    <td>Rp <?php echo number_format($trx['jumlah'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($trx['keterangan']); ?></td>
                    <td>
                        <a href="edit-transaksi.php?id=<?php echo $trx['id']; ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                        <form action="delete-transaksi.php" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                            <input type="hidden" name="id" value="<?php echo $trx['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            </tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#transaksiTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "order": [[0, "desc"]]  // urutkan tanggal descending
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
