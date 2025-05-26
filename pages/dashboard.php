<?php
include '../config/database.php';
include '../includes/auth_check.php';
include '../functions/transaction_functions.php';
include '../functions/activity_functions.php';

// Ambil ringkasan transaksi bulan ini
$current_month = date('Y-m');
$summary = getMonthlySummary($_SESSION['user_id'], $current_month);

// Ambil 5 transaksi terakhir
$recent_transactions = getFilteredTransactions($_SESSION['user_id'], $current_month, '', '', 5);

// Ambil 5 kegiatan terakhir
$recent_activities = getRecentActivities($_SESSION['user_id'], 5);

$page_title = "Dashboard";
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Ringkasan Bulan Ini</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <span class="text-success fw-bold">Pemasukan</span>
                            <h4 class="text-success">Rp <?= number_format($summary['total_income'], 0, ',', '.'); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <span class="text-danger fw-bold">Pengeluaran</span>
                            <h4 class="text-danger">Rp <?= number_format($summary['total_expense'], 0, ',', '.'); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <span class="text-primary fw-bold">Saldo</span>
                            <h4 class="text-primary">Rp <?= number_format($summary['balance'], 0, ',', '.'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Transaksi Terakhir</h5>
                    <a href="<?php echo BASE_URL; ?>pages/transactions/list.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                
                <?php if (empty($recent_transactions)): ?>
                    <div class="alert alert-info">Belum ada transaksi bulan ini</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $transaction['category_name']; ?></h6>
                                    <small class="text-<?= $transaction['type'] === 'income' ? 'success' : 'danger'; ?>">
                                        Rp <?= number_format($transaction['amount'], 0, ',', '.'); ?>
                                    </small>
                                </div>
                                <p class="mb-1 small"><?= $transaction['description'] ?: '-'; ?></p>
                                <small class="text-muted"><?= date('d M Y', strtotime($transaction['date'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Kegiatan Terakhir</h5>
                    <a href="<?php echo BASE_URL; ?>pages/activities/list.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                
                <?php if (empty($recent_activities)): ?>
                    <div class="alert alert-info">Belum ada kegiatan</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= $activity['title']; ?></h6>
                                    <small><?= date('d M', strtotime($activity['date'])); ?></small>
                                </div>
                                <p class="mb-1 small"><?= $activity['description'] ?: '-'; ?></p>
                                <small class="text-muted"><?= date('H:i', strtotime($activity['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Aksi Cepat</h5>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>pages/transactions/add.php?type=income" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Tambah Pemasukan
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/transactions/add.php?type=expense" class="btn btn-danger">
                        <i class="fas fa-minus-circle"></i> Tambah Pengeluaran
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/activities/add.php" class="btn btn-primary">
                        <i class="fas fa-tasks"></i> Tambah Kegiatan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>