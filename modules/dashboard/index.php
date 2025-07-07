<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/header.php';

// Ambil nama pengguna dari session
$username = $_SESSION['username'] ?? 'Pengguna';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Dashboard</h2>
        <div class="text-end">
            <span class="me-3">Halo, <strong><?= htmlspecialchars($username); ?></strong></span>
            <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Keuangan -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Keuangan</h5>
                    <p class="card-text">Kelola catatan keuangan harian Anda.</p>
                    <a href="../keuangan/" class="btn btn-primary">Buka Aplikasi</a>
                </div>
            </div>
        </div>

        <!-- Utang & Piutang -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Utang & Piutang</h5>
                    <p class="card-text">Catat dan kelola utang atau piutang bisnis Anda.</p>
                    <a href="../utang_piutang/" class="btn btn-primary">Kelola Sekarang</a>
                </div>
            </div>
        </div>

        <!-- Kegiatan Harian -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Kegiatan Harian</h5>
                    <p class="card-text">Catat kegiatan harian Anda.</p>
                    <a href="../kegiatan/" class="btn btn-primary">Buka Aplikasi</a>
                </div>
            </div>
        </div>

        <!-- Profil -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Profil</h5>
                    <p class="card-text">Lihat dan ubah informasi akun Anda.</p>
                    <a href="../profil/" class="btn btn-primary">Buka Profil</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
