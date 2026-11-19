<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_produk = $_GET['id'];

// Jika keranjang belum ada, buat array session keranjang
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Jika produk sudah ada di keranjang, tambahkan jumlahnya (+1), jika belum set jumlahnya 1
if (isset($_SESSION['keranjang'][$id_produk])) {
    $_SESSION['keranjang'][$id_produk] += 1;
} else {
    $_SESSION['keranjang'][$id_produk] = 1;
}

echo "<script>alert('Buket berhasil ditambahkan ke keranjang!'); window.location='keranjang.php';</script>";
?>