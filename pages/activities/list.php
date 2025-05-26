<?php
include '../../config/database.php';
include '../../includes/auth_check.php';
include '../../functions/activity_functions.php';

// Tambah kegiatan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $user_id = $_SESSION['user_id'];
    
    addActivity($user_id, $title, $description, $date);
    $success = "Kegiatan berhasil ditambahkan!";
}

// Ambil parameter filter
$date = $_GET['date'] ?? date('Y-m-d');

// Ambil kegiatan berdasarkan tanggal
$activities = getActivitiesByDate($_SESSION['user_id'], $date);

$page_title = "Kegiatan Harian";
include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Kegiatan</h4>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Kegiatan</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Kegiatan</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Daftar Kegiatan</h4>
                    <form method="GET" class="d-flex">
                        <input type="date" class="form-control me-2" name="date" value="<?php echo $date; ?>">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </form>
                </div>
                
                <?php if (empty($activities)): ?>
                    <div class="alert alert-info">Tidak ada kegiatan untuk tanggal ini</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo $activity['title']; ?></h5>
                                    <small><?php echo date('H:i', strtotime($activity['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo $activity['description']; ?></p>
                                <small class="text-muted">Ditambahkan pada <?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>