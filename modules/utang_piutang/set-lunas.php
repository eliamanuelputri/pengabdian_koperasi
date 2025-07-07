<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    header("Location: index.php");
    exit();
}

// Mulai transaksi database
$pdo->beginTransaction();

try {
    // 1. Ambil data utang/piutang yang akan dilunasi
    $sql_get = "SELECT * FROM utang_piutang WHERE id = :id AND user_id = :user_id AND status = 'belum lunas'";
    $stmt_get = $pdo->prepare($sql_get);
    $stmt_get->execute(['id' => $id, 'user_id' => $user_id]);
    $item = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $pdo->rollBack();
        header("Location: index.php?status=notfound");
        exit();
    }

    // 2. Update status jadi 'lunas'
    $sql_update = "UPDATE utang_piutang SET status = 'lunas' WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute(['id' => $id]);

    // 3. Siapkan data transaksi otomatis
    $jenis_transaksi = ($item['jenis'] == 'piutang') ? 'pemasukan' : 'pengeluaran';
    $keterangan_transaksi = "Pelunasan " . $item['jenis'] . " dari/kepada " . htmlspecialchars($item['pihak_terkait']);

    // 4. Cari kategori 'Lain-lain' milik user
    $sql_kat = "SELECT id FROM kategori WHERE user_id = :user_id AND nama = 'Lain-lain' AND jenis = :jenis LIMIT 1";
    $stmt_kat = $pdo->prepare($sql_kat);
    $stmt_kat->execute([
        'user_id' => $user_id,
        'jenis' => $jenis_transaksi
    ]);
    $kategori_id = $stmt_kat->fetchColumn();

    // 5. Jika kategori belum ada, buat baru
    if (!$kategori_id) {
        $sql_new_kat = "INSERT INTO kategori (user_id, nama, jenis) VALUES (:user_id, 'Lain-lain', :jenis)";
        $stmt_new_kat = $pdo->prepare($sql_new_kat);
        $stmt_new_kat->execute([
            'user_id' => $user_id,
            'jenis' => $jenis_transaksi
        ]);
        $kategori_id = $pdo->lastInsertId();
    }

    // 6. Tambahkan ke tabel transaksi
    $sql_insert = "INSERT INTO transaksi (user_id, tanggal, jenis, kategori_id, jumlah, keterangan) 
                   VALUES (:user_id, CURDATE(), :jenis, :kategori_id, :jumlah, :keterangan)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        'user_id' => $user_id,
        'jenis' => $jenis_transaksi,
        'kategori_id' => $kategori_id,
        'jumlah' => $item['jumlah'],
        'keterangan' => $keterangan_transaksi
    ]);

    // Commit jika semua sukses
    $pdo->commit();

    header("Location: index.php?status=lunas_success");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Transaksi Gagal: " . $e->getMessage());
}
?>
