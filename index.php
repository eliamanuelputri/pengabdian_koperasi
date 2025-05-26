<?php
// File: /keuangan-app/index.php

// Mulai session
session_start();

// Definisikan BASE_URL
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']));

// Redirect berdasarkan status login
if (isset($_SESSION['user_id'])) {
    // Jika sudah login, redirect ke dashboard
    header('Location: ' . BASE_URL . 'modules/dashboard/');
} else {
    // Jika belum login, redirect ke halaman login
    header('Location: ' . BASE_URL . 'modules/auth/login.php');
}

exit();
?>