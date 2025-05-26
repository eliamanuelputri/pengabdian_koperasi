<?php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../functions/transaction_functions.php';
require_once '../../functions/category_functions.php';

// Ambil parameter filter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$month = $_GET['month'] ?? date('Y-m');
$category_id = $_GET['category_id'] ?? '';
$type = $_GET['type'] ?? '';

// Validasi input
if ($page < 1) $page = 1;

// Hitung offset untuk pagination
$offset = ($page - 1) * $per_page;

// Ambil data transaksi dengan filter dan pagination
$transactions = getFilteredTransactions(
    $_SESSION['user_id'], 
    $month, 
    $category_id, 
    $type,
    $per_page,
    $offset
);

// Hitung total transaksi untuk pagination
$total_transactions = countFilteredTransactions(
    $_SESSION['user_id'], 
    $month, 
    $category_id, 
    $type
);

// Hitung total halaman
$total_pages = ceil($total_transactions / $per_page);

// Ambil semua kategori untuk dropdown filter
$categories = getAllUserCategories($_SESSION['user_id']);

$page_title = "Daftar Transaksi";
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title mb-0">Daftar Transaksi</h4>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Transaksi
            </a>
        </div>

        <!-- Filter Form -->
        <form method="get" class="row g-3 mb-4">
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

        <!-- Tabel Transaksi -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th class="text-end">Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Tidak ada data transaksi</td>
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
                                <td>
                                    <a href="edit.php?id=<?= $transaction['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $transaction['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger btn-delete"
                                       onclick="return confirm('Hapus transaksi ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            &laquo; Sebelumnya
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            Selanjutnya &raquo;
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>