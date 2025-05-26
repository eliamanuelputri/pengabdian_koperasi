<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/auth/login.php');
    exit;
}

// Tambahkan pengecekan role atau permission tambahan di sini jika diperlukan
?>