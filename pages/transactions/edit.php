<?php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../functions/transaction_functions.php';
require_once '../../functions/category_functions.php';

// Validasi ID transaksi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$transaction_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data transaksi yang akan diedit
$transaction = getTransactionById($transaction_id, $user_id);

// Jika transaksi tidak ditemukan atau bukan milik user
if (!$transaction) {
    header('Location: list.php');
    exit;
}

// Ambil semua kategori user
$categories = getAllUserCategories($user_id);

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $type = $_POST['type'];
    $category_id = (int)$_POST['category_id'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    
    // Validasi input
    if ($amount <= 0 || !in_array($type, ['income', 'expense']) || empty($date)) {
        $error = "Data tidak valid!";
    } else {
        if (updateTransaction($transaction_id, $user_id, $amount, $type, $category_id, $description, $date)) {
            $_SESSION['success_message'] = "Transaksi berhasil diperbarui!";
            header('Location: list.php');
            exit;
        } else {
            $error = "Gagal memperbarui transaksi!";
        }
    }
}

$page_title = "Edit Transaksi";
include '../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Transaksi</h4>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Jenis Transaksi</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="income" <?= $transaction['type'] === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                                <option value="expense" <?= $transaction['type'] === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       value="<?= htmlspecialchars($transaction['amount']) ?>" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <?php if ($category['type'] === $transaction['type']): ?>
                                        <option value="<?= $category['id'] ?>" 
                                            <?= $category['id'] == $transaction['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?= htmlspecialchars($transaction['date']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($transaction['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="list.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk mengupdate dropdown kategori saat jenis transaksi berubah
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const categorySelect = document.getElementById('category_id');
    const categories = <?= json_encode($categories) ?>;
    
    // Simpan kategori yang sedang dipilih
    const currentSelected = categorySelect.value;
    
    // Kosongkan dropdown
    categorySelect.innerHTML = '<option value="">Pilih Kategori</option>';
    
    // Tambahkan kategori yang sesuai dengan jenis transaksi
    categories.forEach(category => {
        if (category.type === type) {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            
            // Jika ini kategori yang sebelumnya dipilih, set selected
            if (category.id == currentSelected) {
                option.selected = true;
            }
            
            categorySelect.appendChild(option);
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>