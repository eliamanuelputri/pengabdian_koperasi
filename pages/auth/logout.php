<?php
include '../../functions/auth_functions.php';

logoutUser();
header('Location: /pages/auth/login.php');
exit;
?>