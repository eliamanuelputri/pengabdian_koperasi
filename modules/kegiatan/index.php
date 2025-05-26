<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Kegiatan Harian";

$user_id = $_SESSION['user_id'];

// Ambil kegiatan hari ini
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM kegiatan 
                      WHERE user_id = ? AND tanggal = ? 
                      ORDER BY waktu_mulai");
$stmt->execute([$user_id, $today]);
$activities = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kegiatan Harian - <?php echo date('d M Y', strtotime($today)); ?></h2>
        <a href="../dashboard/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Aplikasi
        </a>
    </div>
    
    <div class="card">
        
        <a href="add-kegiatan.php" class="btn btn-primary">Tambah Kegiatan</a>
        <div class="card-body">
            <?php if(empty($activities)): ?>
                <div class="alert alert-info">Tidak ada kegiatan untuk hari ini.</div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach($activities as $act): ?>
                        <div class="list-group-item mb-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5><?php echo $act['judul']; ?></h5>
                                    <p class="mb-1">
                                        <i class="far fa-clock"></i> 
                                        <?php echo date('H:i', strtotime($act['waktu_mulai'])); ?> - 
                                        <?php echo date('H:i', strtotime($act['waktu_selesai'])); ?>
                                    </p>
                                    <p class="mb-1"><?php echo $act['deskripsi']; ?></p>
                                </div>
                                <div>
                                    <span class="badge bg-<?php echo $act['status'] == 'selesai' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($act['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="laporan.php" class="btn btn-info">Lihat Laporan Kegiatan</a>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>