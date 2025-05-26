<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Kelola Kategori";

$user_id = $_SESSION['user_id'];
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'pengeluaran';

// Ambil kategori berdasarkan jenis
$stmt = $pdo->prepare("SELECT * FROM kategori WHERE user_id = ? AND jenis = ? ORDER BY nama");
$stmt->execute([$user_id, $jenis]);
$categories = $stmt->fetchAll();

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $nama = $_POST['nama'];
    $jenis = $_POST['jenis'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO kategori (user_id, nama, jenis) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $nama, $jenis]);
        
        $_SESSION['success'] = "Kategori berhasil ditambahkan!";
        header('Location: kategori.php?jenis='.$jenis);
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menambahkan kategori: " . $e->getMessage();
    }
}

// Hapus kategori
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $jenis = $_GET['jenis'];
    
    try {
        // Cek apakah kategori digunakan di transaksi
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE kategori_id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Kategori tidak bisa dihapus karena sudah digunakan dalam transaksi!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            $_SESSION['success'] = "Kategori berhasil dihapus!";
        }
        
        header('Location: kategori.php?jenis='.$jenis);
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menghapus kategori: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Kategori</h2>
        <a href="../keuangan/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Keuangan
        </a>
    </div>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $jenis == 'pengeluaran' ? 'active' : ''; ?>" 
               href="?jenis=pengeluaran">Kategori Pengeluaran</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $jenis == 'pemasukan' ? 'active' : ''; ?>" 
               href="?jenis=pemasukan">Kategori Pemasukan</a>
        </li>
    </ul>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5>Tambah Kategori Baru</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="nama" placeholder="Nama Kategori" required>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="jenis" required>
                            <option value="pengeluaran" <?php echo $jenis == 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                            <option value="pemasukan" <?php echo $jenis == 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" name="add_category" class="btn btn-primary">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Daftar Kategori <?php echo ucfirst($jenis); ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td><?php echo $cat['nama']; ?></td>
                            <td>
                                <a href="?delete=<?php echo $cat['id']; ?>&jenis=<?php echo $jenis; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>