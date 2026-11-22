<?php
session_start();
unset($_SESSION['id_admin']);
unset($_SESSION['nama_admin']);
header("Location: login.php");
exit;
?>