<?php
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan']) || empty($_SESSION['keranjang'])) {
    header("Location: keranjang.php");
    exit;
}

$id_pelanggan = (int)$_SESSION['id_pelanggan'];
$metode_bayar = mysqli_real_escape_string($conn, $_POST['metode_bayar'] ?? 'QRIS Instan');
$kode_ref = 'PG-KC-' . date('YmdHis') . rand(100, 999);

mysqli_begin_transaction($conn);

try {
    $total_pembelian = 0;
    $items = [];

    foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
        $id_p = (int)$id_produk;
        $jml = (int)$jumlah;
        if ($jml <= 0) continue;

        $q_p = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id_p' FOR UPDATE");
        $p = mysqli_fetch_assoc($q_p);
        if (!$p) {
            throw new Exception("Produk tidak ditemukan.");
        }
        if ((int)$p['stok'] < $jml) {
            throw new Exception("Stok produk " . $p['nama_produk'] . " tidak mencukupi (tersisa " . $p['stok'] . ").");
        }

        $subtotal = (int)$p['harga'] * $jml;
        $total_pembelian += $subtotal;
        $items[] = [
            'id_produk' => $id_p,
            'jumlah' => $jml,
            'harga' => (int)$p['harga'],
            'subtotal' => $subtotal
        ];
    }

    if ($total_pembelian <= 0) {
        throw new Exception("Keranjang kosong.");
    }

    $insert_trx = mysqli_query($conn, "INSERT INTO transaksi (id_pelanggan, total_pembelian, status_transaksi, tipe_order, metode_bayar, status_bayar, kode_referensi)
        VALUES ('$id_pelanggan', '$total_pembelian', 'Diproses', 'Online', '$metode_bayar', 'Lunas (Payment Gateway)', '$kode_ref')");

    if (!$insert_trx) {
        throw new Exception("Gagal menyimpan transaksi.");
    }

    $id_transaksi_baru = mysqli_insert_id($conn);

    foreach ($items as $it) {
        $id_p = $it['id_produk'];
        $jml = $it['jumlah'];
        $hrg = $it['harga'];
        $sub = $it['subtotal'];

        mysqli_query($conn, "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal)
            VALUES ('$id_transaksi_baru', '$id_p', '$jml', '$hrg', '$sub')");

        // Otomatis kurangi stok produk
        $upd_stok = mysqli_query($conn, "UPDATE produk SET stok = stok - $jml WHERE id_produk = '$id_p' AND stok >= $jml");
        if (!$upd_stok || mysqli_affected_rows($conn) == 0) {
            throw new Exception("Gagal mengurangi stok produk.");
        }
    }

    mysqli_commit($conn);
    unset($_SESSION['keranjang']);

    echo "<script>alert('Pembayaran Payment Gateway berhasil! Kode Referensi: $kode_ref. Stok produk telah diperbarui.'); window.location='index.php';</script>";
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $msg = addslashes($e->getMessage());
    echo "<script>alert('Checkout gagal: $msg'); window.location='keranjang.php';</script>";
    exit;
}
?>