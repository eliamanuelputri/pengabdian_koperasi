<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Laporan Kegiatan";

$user_id = $_SESSION['user_id'];

// Filter default bulan ini
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Query untuk laporan
$query = "SELECT * FROM kegiatan 
          WHERE user_id = ? 
          AND tanggal BETWEEN ? AND ?";
$params = [$user_id, $start_date, $end_date];

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY tanggal DESC, waktu_mulai";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$activities = $stmt->fetchAll();

// Statistik
$query_stats = "SELECT status, COUNT(*) as total 
               FROM kegiatan 
               WHERE user_id = ? 
               AND tanggal BETWEEN ? AND ?
               GROUP BY status";
$stmt_stats = $pdo->prepare($query_stats);
$stmt_stats->execute([$user_id, $start_date, $end_date]);
$stats = $stmt_stats->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Laporan Kegiatan</h2>
        <a href="../kegiatan/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Kegiatan
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="rencana" <?php echo $status == 'rencana' ? 'selected' : ''; ?>>Rencana</option>
                        <option value="berjalan" <?php echo $status == 'berjalan' ? 'selected' : ''; ?>>Berjalan</option>
                        <option value="selesai" <?php echo $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if(!empty($stats)): ?>
    <div class="row mb-4">
        <?php foreach($stats as $stat): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo ucfirst($stat['status']); ?></h5>
                    <p class="card-text"><?php echo $stat['total']; ?> kegiatan</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Daftar Kegiatan</h5>
        </div>
        <div class="card-body">
            <?php if(empty($activities)): ?>
                <div class="alert alert-info">Tidak ada kegiatan dalam periode ini.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($activities as $act): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($act['tanggal'])); ?></td>
                                <td><?php echo $act['judul']; ?></td>
                                <td>
                                    <?php echo date('H:i', strtotime($act['waktu_mulai'])); ?> - 
                                    <?php echo date('H:i', strtotime($act['waktu_selesai'])); ?>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-<?php echo $act['status'] == 'selesai' ? 'success' : ($act['status'] == 'berjalan' ? 'warning' : 'info'); ?> status-badge"
                                        data-id="<?= $act['id']; ?>" data-status="<?= $act['status']; ?>"
                                        style="cursor: pointer;">
                                        <?= ucfirst($act['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.status-badge').forEach(function (badge) {
        badge.addEventListener('click', function () {
            const id = this.dataset.id;
            const currentStatus = this.dataset.status;
            const badgeEl = this;

            fetch('update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&current_status=${currentStatus}`
                })
                .then(res => res.text())
                .then(newStatus => {
                    badgeEl.dataset.status = newStatus;
                    badgeEl.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                    // Ubah warna badge sesuai status baru
                    badgeEl.className = 'badge status-badge';
                    if (newStatus === 'rencana') {
                        badgeEl.classList.add('bg-info');
                    } else if (newStatus === 'berjalan') {
                        badgeEl.classList.add('bg-warning');
                    } else if (newStatus === 'selesai') {
                        badgeEl.classList.add('bg-success');
                    }
                })
                .catch(error => {
                    alert('Gagal mengubah status');
                    console.error(error);
                });
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>