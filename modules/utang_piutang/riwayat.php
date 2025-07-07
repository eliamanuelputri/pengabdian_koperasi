<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

$user_id = $_SESSION['user_id'];

// Ambil input filter
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
$jenis = $_GET['jenis'] ?? ''; // utang, piutang, atau semua

$filter_sql = '';
$params = ['user_id' => $user_id];

// Filter tanggal
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $filter_sql .= " AND tanggal_transaksi BETWEEN :awal AND :akhir";
    $params['awal'] = $tanggal_awal;
    $params['akhir'] = $tanggal_akhir;
}

// Filter jenis
if ($jenis === 'utang' || $jenis === 'piutang') {
    $filter_sql .= " AND jenis = :jenis";
    $params['jenis'] = $jenis;
}

try {
    $sql = "SELECT jenis, pihak_terkait, jumlah, tanggal_transaksi, tanggal_jatuh_tempo, keterangan, created_at 
            FROM utang_piutang 
            WHERE user_id = :user_id AND status = 'lunas' $filter_sql
            ORDER BY tanggal_transaksi DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data riwayat: " . $e->getMessage());
}

$pageTitle = "Riwayat Utang & Piutang";
require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Riwayat Utang & Piutang</h2>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali Ke Utang & Piutang</a>
    </div>

    <!-- Form Filter -->
  <form method="GET" class="row align-items-end g-3 mb-4">
    <div class="col-md-3">
        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
        <input type="date" id="tanggal_awal" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>" class="form-control">
    </div>
    <div class="col-md-3">
        <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
        <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>" class="form-control">
    </div>
    <div class="col-md-3">
        <label for="jenis" class="form-label">Jenis</label>
        <select name="jenis" id="jenis" class="form-select">
            <option value="">Semua</option>
            <option value="utang" <?= $jenis == 'utang' ? 'selected' : '' ?>>Utang</option>
            <option value="piutang" <?= $jenis == 'piutang' ? 'selected' : '' ?>>Piutang</option>
        </select>
    </div>
    <div class="col-md-3">
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="riwayat.php" class="btn btn-outline-secondary">
                <i class="fas fa-undo"></i> Reset
            </a>
            <?php if (!empty($items)): ?>
                <a href="export-riwayat-excel.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>&jenis=<?= $jenis ?>" target="_blank" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Ekspor Excel
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>


    <!-- Tabel Data -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($items)): ?>
                <p class="text-center text-muted">Belum ada riwayat pelunasan pada periode ini.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Jenis</th>
                                <th>Pihak Terkait</th>
                                <th class="text-end">Jumlah</th>
                                <th>Tanggal Transaksi</th>
                                <th>Jatuh Tempo</th>
                                <th>Keterangan</th>
                                <th>Dicatat Pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $item['jenis'] == 'piutang' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= htmlspecialchars(ucfirst($item['jenis'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($item['pihak_terkait']) ?></td>
                                    <td class="text-end">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                    <td><?= date('d M Y', strtotime($item['tanggal_transaksi'])) ?></td>
                                    <td><?= $item['tanggal_jatuh_tempo'] ? date('d M Y', strtotime($item['tanggal_jatuh_tempo'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($item['keterangan']) ?: '-' ?></td>
                                    <td><?= date('d M Y H:i', strtotime($item['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
