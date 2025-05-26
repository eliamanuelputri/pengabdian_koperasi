<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_keuangan.xls");

$user_id = $_SESSION['user_id'];

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$kategori_id = $_GET['kategori_id'] ?? '';

$query = "SELECT t.*, k.nama as kategori_nama 
          FROM transaksi t 
          JOIN kategori k ON t.kategori_id = k.id 
          WHERE t.user_id = ? 
          AND t.tanggal BETWEEN ? AND ? 
          AND t.deleted_at IS NULL";
$params = [$user_id, $start_date, $end_date];

if (!empty($kategori_id)) {
    $query .= " AND t.kategori_id = ?";
    $params[] = $kategori_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>Tanggal</th><th>Kategori</th><th>Jenis</th><th>Jumlah</th><th>Keterangan</th></tr>";
foreach ($data as $row) {
    echo "<tr>";
    echo "<td>" . $row['tanggal'] . "</td>";
    echo "<td>" . htmlspecialchars($row['kategori_nama']) . "</td>";
    echo "<td>" . $row['jenis'] . "</td>";
    echo "<td>" . number_format($row['jumlah'], 0, ',', '.') . "</td>";
    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
    echo "</tr>";
}
echo "</table>";
