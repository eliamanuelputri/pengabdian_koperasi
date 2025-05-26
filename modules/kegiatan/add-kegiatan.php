<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Tambah Kegiatan";

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $judul = $_POST['judul'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO kegiatan (user_id, tanggal, judul, waktu_mulai, waktu_selesai, deskripsi, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $tanggal, $judul, $waktu_mulai, $waktu_selesai, $deskripsi, $status]);
        
        $_SESSION['success'] = "Kegiatan berhasil ditambahkan!";
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menambahkan kegiatan: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Tambah Kegiatan</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Kegiatan</label>
                    <input type="text" class="form-control" id="judul" name="judul" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                    <input type="time" class="form-control" id="waktu_mulai" name="waktu_mulai" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                    <input type="time" class="form-control" id="waktu_selesai" name="waktu_selesai" required>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="rencana">Rencana</option>
                <option value="berjalan">Berjalan</option>
                <option value="selesai">Selesai</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>