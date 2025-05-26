<?php
include '../../config/database.php';
include '../../includes/auth_check.php';
include '../../functions/transaction_functions.php';
include '../../functions/category_functions.php';

// Tambah kategori baru jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $category_type = $_POST['category_type'];
    $user_id = $_SESSION['user_id'];
    
    addCategory($user_id, $category_name, $category_type);
}

// Tambah transaksi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $user_id = $_SESSION['user_id'];
    
    addTransaction($user_id, $amount, $type, $category_id, $description, $date);
    $success = "Transaksi berhasil ditambahkan!";
}

// Ambil kategori user
$income_categories = getUserCategories($_SESSION['user_id'], 'income');
$expense_categories = getUserCategories($_SESSION['user_id'], 'expense');

$page_title = "Tambah Transaksi";
include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Transaksi Baru</h4>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Jenis Transaksi</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <!-- Kategori akan diisi via JavaScript berdasarkan jenis transaksi -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" name="add_transaction" class="btn btn-primary">Simpan Transaksi</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Tambah Kategori Baru</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label for="category_type" class="form-label">Jenis Kategori</label>
                        <select class="form-select" id="category_type" name="category_type" required>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-secondary">Tambah Kategori</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Update kategori berdasarkan jenis transaksi
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const categorySelect = document.getElementById('category_id');
    
    // Kosongkan dropdown kategori
    categorySelect.innerHTML = '<option value="">Pilih Kategori</option>';
    
    // Tambahkan kategori yang sesuai
    const categories = type === 'income' ? <?php echo json_encode($income_categories); ?> : <?php echo json_encode($expense_categories); ?>;
    
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categorySelect.appendChild(option);
    });
});

// Trigger change event saat halaman dimuat
document.getElementById('type').dispatchEvent(new Event('change'));
</script>

<?php include '../../includes/footer.php'; ?>