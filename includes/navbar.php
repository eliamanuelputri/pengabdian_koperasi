<?php
$current_path = $_SERVER['REQUEST_URI'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">Keuangan App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_path, 'dashboard') !== false ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>modules/dashboard/">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_path, 'keuangan') !== false ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>modules/keuangan/">
                            <i class="fas fa-wallet"></i> Keuangan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_path, 'utang_piutang') !== false ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>modules/utang_piutang/">
                            <i class="fas fa-hand-holding-usd"></i> Utang & Piutang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_path, 'kegiatan') !== false ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>modules/kegiatan/">
                            <i class="fas fa-calendar-alt"></i> Kegiatan
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_path, 'login') !== false ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>modules/auth/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($current_path, 'register') !== false ? 'active' : '' ?>" href="<?php echo BASE_URL; ?>modules/auth/register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
