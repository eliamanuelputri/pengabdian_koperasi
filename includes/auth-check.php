<?php
// session_start();

// define('BASE_URL', 'http://localhost/keuangan-app/');

if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    header('Location: ' . BASE_URL . 'modules/auth/login.php');
    exit();
}
?>