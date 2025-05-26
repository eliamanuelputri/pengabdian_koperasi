<?php
// Redirect ke dashboard jika sudah login, atau ke halaman login jika belum
include 'config/database.php';
include 'functions/auth_functions.php';

if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
} else {
    header('Location: /pages/auth/login.php');
}
exit;
?>