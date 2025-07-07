<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

try {
    $user_id = $_SESSION['user_id'];

    // Total utang
    $sqlUtang = "SELECT SUM(jumlah) AS total_utang FROM utang_piutang 
                WHERE user_id = :user_id AND jenis = 'utang' AND status = 'belum lunas'";
    $stmtUtang = $pdo->prepare($sqlUtang);
    $stmtUtang->execute(['user_id' => $user_id]);
    $totalUtang = $stmtUtang->fetchColumn() ?? 0;

    // Total piutang
    $sqlPiutang = "SELECT SUM(jumlah) AS total_piutang FROM utang_piutang 
                WHERE user_id = :user_id AND jenis = 'piutang' AND status = 'belum lunas'";
    $stmtPiutang = $pdo->prepare($sqlPiutang);
    $stmtPiutang->execute(['user_id' => $user_id]);
    $totalPiutang = $stmtPiutang->fetchColumn() ?? 0;

    // Ambil data utang & piutang yang belum lunas
    $sql = "SELECT id, jenis, pihak_terkait, jumlah, tanggal_jatuh_tempo, status 
            FROM utang_piutang 
            WHERE user_id = :user_id AND status = 'belum lunas'
            ORDER BY tanggal_jatuh_tempo ASC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}

$pageTitle = "Daftar Utang & Piutang";
require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Utang & Piutang</h2>
        <div class="d-flex gap-2">
            <a href="../dashboard/" class="btn btn-outline-primary">
                <i class="fas fa-home"></i> Kembali ke Daftar Aplikasi
            </a>
            <a href="riwayat.php" class="btn btn-outline-secondary">
                <i class="fas fa-history"></i> Lihat Riwayat
            </a>
            <a href="add-utang-piutang.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Baru
            </a>
        </div>
    </div>

    <!-- Summary Total -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card text-bg-danger h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Utang</h5>
                    <p class="card-text fs-4">Rp <?= number_format($totalUtang, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card text-bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Piutang</h5>
                    <p class="card-text fs-4">Rp <?= number_format($totalPiutang, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                if ($_GET['status'] == 'add_success') echo 'Data berhasil ditambahkan!';
                if ($_GET['status'] == 'edit_success') echo 'Data berhasil diperbarui!';
                if ($_GET['status'] == 'lunas_success') echo 'Berhasil menandai lunas dan transaksi telah dicatat!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tabel Data -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-striped border">
                    <thead style="background-color: #e9f1fa;">
                        <tr class="text-dark text-center fw-semibold">
                            <th style="width: 12%;">Jenis</th>
                            <th style="width: 28%;">Pihak Terkait</th>
                            <th class="text-end" style="width: 18%;">Jumlah</th>
                            <th class="text-center" style="width: 18%;">Jatuh Tempo</th>
                            <th class="text-center" style="width: 24%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Tidak ada data utang/piutang yang aktif.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fs-6 <?= $item['jenis'] == 'piutang' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= htmlspecialchars(ucfirst($item['jenis'])) ?>
                                        </span>
                                    </td>
                                    <td class="ps-3"><?= htmlspecialchars($item['pihak_terkait']) ?></td>
                                    <td class="text-end fw-semibold pe-3">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                                    <td class="text-center ps-2">
                                        <?php 
                                        if ($item['tanggal_jatuh_tempo']) {
                                            $jatuh_tempo = new DateTime($item['tanggal_jatuh_tempo']);
                                            $hari_ini = new DateTime();
                                            $color = $jatuh_tempo < $hari_ini ? 'text-danger fw-bold' : 'text-dark';
                                            echo '<span class="'.$color.'">' . $jatuh_tempo->format('d M Y') . '</span>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="set-lunas.php?id=<?= $item['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Anda yakin ingin menandai ini sebagai LUNAS? Aksi ini akan otomatis membuat catatan transaksi keuangan baru.')">
                                                <i class="fas fa-check"></i> Lunas
                                            </a>
                                            <a href="edit-utang-piutang.php?id=<?= $item['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
