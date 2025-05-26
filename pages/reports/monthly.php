<?php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../functions/transaction_functions.php';
require_once '../../functions/report_functions.php';
require_once '../../functions/category_functions.php';

// Ambil parameter filter
$month = $_GET['month'] ?? date('Y-m');
$category_id = $_GET['category_id'] ?? '';
$type = $_GET['type'] ?? '';

// Ambil data transaksi berdasarkan filter
$transactions = getFilteredTransactions($_SESSION['user_id'], $month, $category_id, $type);

// Ambil total pemasukan dan pengeluaran
$monthly_summary = getMonthlySummary($_SESSION['user_id'], $month);

// Ambil saldo akhir keseluruhan (total semua transaksi hingga bulan ini)
$overall_balance = getOverallBalance($_SESSION['user_id'], $month);

// Ambil saran pengeluaran
$suggestions = getExpenseSuggestions($_SESSION['user_id'], $month);

// Ambil semua kategori user untuk dropdown
$categories = getAllUserCategories($_SESSION['user_id']);

$page_title = "Laporan Bulanan";
include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Laporan Bulanan</h4>
                
                <!-- Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Bulan</label>
                        <input type="month" class="form-control" id="month" name="month" value="<?= htmlspecialchars($month) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">Jenis</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Semua</option>
                            <option value="income" <?= $type === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                            <option value="expense" <?= $type === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?> 
                                    (<?= $category['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
                
                <!-- Ringkasan Keuangan -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Pemasukan Bulan Ini</h5>
                                <h3 class="card-text">Rp <?= number_format($monthly_summary['total_income'], 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Pengeluaran Bulan Ini</h5>
                                <h3 class="card-text">Rp <?= number_format($monthly_summary['total_expense'], 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Saldo Bulan Ini</h5>
                                <h3 class="card-text">Rp <?= number_format($monthly_summary['balance'], 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Saldo Akhir (Total)</h5>
                                <h3 class="card-text">Rp <?= number_format($overall_balance, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Saran Pengeluaran -->
                <?php if (!empty($suggestions)): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-lightbulb"></i> Saran untuk Mengurangi Pengeluaran:</h5>
                        <ul>
                            <?php foreach ($suggestions as $suggestion): ?>
                                <li><?= htmlspecialchars($suggestion) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Daftar Transaksi -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">Tidak ada data transaksi</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($transaction['date'])) ?></td>
                                        <td><?= htmlspecialchars($transaction['category_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['description'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $transaction['type'] === 'income' ? 'success' : 'danger' ?>">
                                                <?= $transaction['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' ?>
                                            </span>
                                        </td>
                                        <td class="text-end text-<?= $transaction['type'] === 'income' ? 'success' : 'danger' ?>">
                                            Rp <?= number_format($transaction['amount'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>