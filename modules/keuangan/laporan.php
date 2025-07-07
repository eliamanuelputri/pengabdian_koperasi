<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

$page_title = "Laporan Keuangan";

$user_id = $_SESSION['user_id'];

// Filter default bulan ini
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$kategori_id = isset($_GET['kategori_id']) ? $_GET['kategori_id'] : '';

// Ambil kategori untuk filter
$stmt = $pdo->prepare("SELECT * FROM kategori WHERE user_id = ? ORDER BY nama");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

// Query untuk laporan (exclude soft deleted)
$query = "SELECT t.*, k.nama as kategori_nama 
          FROM transaksi t 
          JOIN kategori k ON t.kategori_id = k.id 
          WHERE t.user_id = ? 
          AND t.tanggal BETWEEN ? AND ?
          AND t.deleted_at IS NULL";  // soft delete
$params = [$user_id, $start_date, $end_date];

if (!empty($kategori_id)) {
    $query .= " AND t.kategori_id = ?";
    $params[] = $kategori_id;
}

$query .= " ORDER BY t.tanggal DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Hitung total (exclude soft deleted)
$query_total = "SELECT 
                SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_pemasukan,
                SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_pengeluaran
               FROM transaksi 
               WHERE user_id = ? 
               AND tanggal BETWEEN ? AND ?
               AND deleted_at IS NULL";  // soft delete
$params_total = [$user_id, $start_date, $end_date];

if (!empty($kategori_id)) {
    $query_total .= " AND kategori_id = ?";
    $params_total[] = $kategori_id;
}

$stmt_total = $pdo->prepare($query_total);
$stmt_total->execute($params_total);
$totals = $stmt_total->fetch();

// Analisis pengeluaran (exclude soft deleted)
$query_analysis = "SELECT k.nama as kategori, SUM(t.jumlah) as total
                  FROM transaksi t
                  JOIN kategori k ON t.kategori_id = k.id
                  WHERE t.user_id = ? 
                  AND t.jenis = 'pengeluaran'
                  AND t.tanggal BETWEEN ? AND ?
                  AND t.deleted_at IS NULL
                  GROUP BY t.kategori_id
                  ORDER BY total DESC
                  LIMIT 3";
$stmt_analysis = $pdo->prepare($query_analysis);
$stmt_analysis->execute([$user_id, $start_date, $end_date]);
$top_expenses = $stmt_analysis->fetchAll();

// Hitung saldo akhir (akumulasi seluruh transaksi tanpa filter tanggal/kategori, exclude soft deleted)
$query_final_balance = "SELECT 
                        SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_pemasukan,
                        SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_pengeluaran
                       FROM transaksi
                       WHERE user_id = ?
                       AND deleted_at IS NULL";
$stmt_final = $pdo->prepare($query_final_balance);
$stmt_final->execute([$user_id]);
$final_totals = $stmt_final->fetch();

$final_balance = $final_totals['total_pemasukan'] - $final_totals['total_pengeluaran'];

// Ambil data pemasukan dan pengeluaran per tanggal untuk grafik (optional, bisa dihilangkan jika tidak perlu)
$query_income_chart = "SELECT tanggal, SUM(jumlah) as total 
                       FROM transaksi 
                       WHERE user_id = ? 
                       AND jenis = 'pemasukan' 
                       AND tanggal BETWEEN ? AND ?
                       AND deleted_at IS NULL
                       GROUP BY tanggal
                       ORDER BY tanggal";
$stmt_income_chart = $pdo->prepare($query_income_chart);
$stmt_income_chart->execute([$user_id, $start_date, $end_date]);
$income_data = $stmt_income_chart->fetchAll(PDO::FETCH_ASSOC);

$query_expense_chart = "SELECT tanggal, SUM(jumlah) as total 
                        FROM transaksi 
                        WHERE user_id = ? 
                        AND jenis = 'pengeluaran' 
                        AND tanggal BETWEEN ? AND ?
                        AND deleted_at IS NULL
                        GROUP BY tanggal
                        ORDER BY tanggal";
$stmt_expense_chart = $pdo->prepare($query_expense_chart);
$stmt_expense_chart->execute([$user_id, $start_date, $end_date]);
$expense_data = $stmt_expense_chart->fetchAll(PDO::FETCH_ASSOC);

$dates = [];
foreach ($income_data as $row) { $dates[$row['tanggal']] = true; }
foreach ($expense_data as $row) { $dates[$row['tanggal']] = true; }
$dates = array_keys($dates);
sort($dates);

$income_per_date = array_fill_keys($dates, 0);
foreach ($income_data as $row) {
    $income_per_date[$row['tanggal']] = (float)$row['total'];
}
$expense_per_date = array_fill_keys($dates, 0);
foreach ($expense_data as $row) {
    $expense_per_date[$row['tanggal']] = (float)$row['total'];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Laporan Keuangan</h2>
        <a href="../keuangan/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Keuangan
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
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $kategori_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Pemasukan</h5>
                    <p class="card-text">Rp <?php echo number_format($totals['total_pemasukan'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Total Pengeluaran</h5>
                    <p class="card-text">Rp <?php echo number_format($totals['total_pengeluaran'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Saldo Bulan Ini</h5>
                    <p class="card-text">Rp <?php echo number_format($totals['total_pemasukan'] - $totals['total_pengeluaran'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Saldo Akhir</h5>
                    <p class="card-text">Rp <?php echo number_format($final_balance, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($top_expenses)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5>Analisis Pengeluaran</h5>
        </div>
        <div class="card-body row">
            <div class="col-md-6" style="">
                <p>Kategori dengan pengeluaran terbesar:</p>
                <ul>
                    <?php foreach($top_expenses as $expense): ?>
                    <li><?php echo htmlspecialchars(ucfirst(strtolower($expense['kategori']))); ?>: Rp
                        <?php echo number_format($expense['total'], 0, ',', '.'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-6" >
                <canvas id="donutChart" style="width: 350px; height: 350px; margin: 0 auto;""></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header">
            <h5>Daftar Transaksi</h5>
        </div>
        <div class="card-body">
            <a href="export-laporan-excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&kategori_id=<?= $kategori_id ?>" class="btn btn-success mb-3" target="_blank">
                Export ke Excel
            </a>
            <div class="table-responsive">
                <table id="transaksiTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $trx): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($trx['tanggal'])); ?></td>
                            <td><?php echo htmlspecialchars($trx['kategori_nama']); ?></td>
                            <td>
                                <span class="badge <?php echo $trx['jenis'] == 'pemasukan' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($trx['jenis']); ?>
                                </span>
                            </td>
                            <td>Rp <?php echo number_format($trx['jumlah'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($trx['keterangan']); ?></td>
                            <td>
                                <a href="edit-transaksi.php?id=<?php echo $trx['id']; ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                                <form action="delete-transaksi.php" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                                    <input type="hidden" name="id" value="<?php echo $trx['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h5>Grafik Pemasukan dan Pengeluaran</h5>
        </div>
        <div class="card-body">
            <canvas id="incomeExpenseChart" style="width: 100%; height: 300px;"></canvas>
        </div>
    </div>
    
    
</div>

<!-- Load jQuery dan DataTables CSS/JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    $('#transaksiTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "order": [[0, "desc"]] // urut tanggal desc
    });
});

const ctx = document.getElementById('incomeExpenseChart').getContext('2d');

const labels = <?php echo json_encode($dates); ?>;
const incomeData = <?php echo json_encode(array_values($income_per_date)); ?>;
const expenseData = <?php echo json_encode(array_values($expense_per_date)); ?>;

const incomeExpenseChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels.map(dateStr => {
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        }),
        datasets: [
            {
                label: 'Pemasukan',
                data: incomeData,
                borderColor: 'green',
                backgroundColor: 'rgba(0,128,0,0.1)',
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Pengeluaran',
                data: expenseData,
                borderColor: 'red',
                backgroundColor: 'rgba(255,0,0,0.1)',
                fill: true,
                tension: 0.3,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false },
        },
        interaction: {
            mode: 'nearest',
            intersect: false
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
<script>
    const donutCtx = document.getElementById('donutChart').getContext('2d');

    const donutData = {
        labels: <?php echo json_encode(array_column($top_expenses, 'kategori')); ?> ,
        datasets : [{
            label: 'Pengeluaran per Kategori',
            data: <?php echo json_encode(array_map('floatval', array_column($top_expenses, 'total'))); ?>
            ,
            backgroundColor : ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff'],
            hoverOffset: 4
        }]
    };

    const donutChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: donutData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed;
                            const percent = ((value / total) * 100).toFixed(1);
                            return `${context.label}: Rp ${value.toLocaleString('id-ID')} (${percent}%)`;
                        }
                    }
                }
            }
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>
