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

// Ambil semua history kegiatan (selain hari ini)
$stmt_history = $pdo->prepare("SELECT * FROM kegiatan 
                               WHERE user_id = ? AND tanggal < ?
                               ORDER BY tanggal DESC, waktu_mulai ASC");
$stmt_history->execute([$user_id, $today]);
$history_activities = $stmt_history->fetchAll();

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
                            <div class="text-end">
                                <span
                                    class="badge status-badge bg-<?php echo $act['status'] == 'selesai' ? 'success' : 'warning'; ?>"
                                    data-id="<?= $act['id']; ?>" data-status="<?= $act['status']; ?>"
                                    style="cursor:pointer;">
                                    <?= ucfirst($act['status']); ?>
                                </span>

                                <!-- Titik tiga dropdown -->
                                <div class="dropdown d-inline-block ms-2">
                                    <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                        id="dropdownMenu<?= $act['id']; ?>" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end"
                                        aria-labelledby="dropdownMenu<?= $act['id']; ?>">
                                        <li><a class="dropdown-item"
                                                href="edit-kegiatan.php?id=<?= $act['id']; ?>">Edit</a></li>
                                        <li>
                                            <form action="delete-kegiatan.php" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?');">
                                                <input type="hidden" name="id" value="<?= $act['id']; ?>">
                                                <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                            </form>
                                        </li>
                                    </ul>
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

    <div class="card mt-5">
        <div class="card-header">
            <h5>History Kegiatan</h5>
        </div>
        <div class="card-body">
            <?php if (empty($history_activities)): ?>
            <div class="alert alert-info">Belum ada history kegiatan.</div>
            <?php else: ?>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-striped table-bordered table-sm">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($history_activities as $item): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($item['tanggal'])); ?></td>
                            <td><?= htmlspecialchars($item['judul']); ?></td>
                            <td>
                                <?= date('H:i', strtotime($item['waktu_mulai'])); ?> -
                                <?= date('H:i', strtotime($item['waktu_selesai'])); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?=
                                    $item['status'] == 'selesai' ? 'success' :
                                    ($item['status'] == 'berjalan' ? 'warning' : 'info');
                                ?>">
                                    <?= ucfirst($item['status']); ?>
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

<script>
    // Menutup menu lain saat membuka satu
    document.querySelectorAll('.toggle-menu').forEach(button => {
        button.addEventListener('click', function (e) {
            e.stopPropagation(); // biar klik ini tidak nutup menu
            const id = this.dataset.id;
            const menu = document.getElementById(`menu-${id}`);

            // Tutup semua menu lain dulu
            document.querySelectorAll('.edit-delete-menu').forEach(m => {
                if (m.id !== `menu-${id}`) m.style.display = 'none';
            });

            // Toggle tampilan menu
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
    });

    // Klik di luar menu => tutup semua
    document.addEventListener('click', function () {
        document.querySelectorAll('.edit-delete-menu').forEach(m => {
            m.style.display = 'none';
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>