<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=riwayat_utang_piutang.xls");

$user_id = $_SESSION['user_id'];
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
$jenis = $_GET['jenis'] ?? ''; // utang, piutang, atau kosong

$params = ['user_id' => $user_id];
$filter_sql = '';

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $filter_sql .= " AND tanggal_transaksi BETWEEN :awal AND :akhir";
    $params['awal'] = $tanggal_awal;
    $params['akhir'] = $tanggal_akhir;
}

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
    die("Gagal mengambil data: " . $e->getMessage());
}
?>

<table border="1">
    <thead>
        <tr style="background-color: #e0e0e0;">
            <th>Jenis</th>
            <th>Pihak Terkait</th>
            <th>Jumlah</th>
            <th>Tanggal Transaksi</th>
            <th>Jatuh Tempo</th>
            <th>Keterangan</th>
            <th>Dicatat Pada</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="7" align="center">Tidak ada data untuk periode ini.</td></tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= ucfirst(htmlspecialchars($item['jenis'])) ?></td>
                    <td><?= htmlspecialchars($item['pihak_terkait']) ?></td>
                    <td>Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                    <td><?= date('d-m-Y', strtotime($item['tanggal_transaksi'])) ?></td>
                    <td><?= $item['tanggal_jatuh_tempo'] ? date('d-m-Y', strtotime($item['tanggal_jatuh_tempo'])) : '-' ?></td>
                    <td><?= htmlspecialchars($item['keterangan']) ?: '-' ?></td>
                    <td><?= date('d-m-Y H:i', strtotime($item['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
