<?php
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit;
}

$id_pelanggan = $_SESSION['id_pelanggan'];

// Ambil data pelanggan
$pelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_pelanggan = '$id_pelanggan'"));

// Ambil keranjang
$krg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM keranjang WHERE id_pelanggan = '$id_pelanggan'"));
$id_keranjang = $krg['id_keranjang'];

$items = [];
$total_belanja = 0;
$query_detail = mysqli_query($conn, "SELECT dk.*, p.nama_produk, p.harga FROM detail_keranjang dk JOIN produk p ON dk.id_produk = p.id_produk WHERE dk.id_keranjang = '$id_keranjang'");
while ($row = mysqli_fetch_assoc($query_detail)) {
    $items[] = $row;
    $total_belanja += $row['harga'] * $row['jumlah'];
}

if (isset($_POST['proses_pesanan'])) {
    $nama_penerima = mysqli_real_escape_string($conn, $_POST['nama_penerima']);
    $no_hp_penerima = mysqli_real_escape_string($conn, $_POST['no_hp_penerima']);
    $alamat_penerima = mysqli_real_escape_string($conn, $_POST['alamat_penerima']);
    $metode_pengiriman = $_POST['metode_pengiriman']; // Ambil di Stand atau Diantar Penjual
    $ongkir = ($metode_pengiriman == 'Diantar Penjual') ? 15000 : 0; // Contoh biayaantar langsung
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan_pesanan']);
    $total_harga = $total_belanja + $ongkir;

    // Simpan ke tabel pesanan
    $ins_pesanan = mysqli_query($conn, "INSERT INTO pesanan (id_pelanggan, nama_penerima, no_hp_penerima, alamat_penerima, ongkos_kirim, catatan_pesanan, total_harga, status_pesanan) VALUES ('$id_pelanggan', '$nama_penerima', '$no_hp_penerima', '$alamat_penerima', '$ongkir', '$catatan', '$total_harga', 'Menunggu Pembayaran')");
    
    if ($ins_pesanan) {
        $id_pesanan_baru = mysqli_insert_id($conn);

        // Pindahkan item keranjang ke detail_pesanan
        foreach ($items as itm) {
            $id_prod = $itm['id_produk'];
            $jml = $itm['jumlah'];
            $hrg = $itm['harga'];
            $sub = $hrg * $jml;
            mysqli_query($conn, "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan, subtotal) VALUES ('$id_pesanan_baru', '$id_prod', '$jml', '$hrg', '$sub')");
        }

        // Kosongkan keranjang
        mysqli_query($conn, "DELETE FROM detail_keranjang WHERE id_keranjang = '$id_keranjang'");

        echo "<script>alert('Pesanan berhasil dibuat! Silakan lakukan pembayaran.'); window.location='index.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Kembang Cerita Flowers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff5f7; font-family: 'Poppins', sans-serif; }
        .btn-pastel { background-color: #d8a47f; color: white; border: none; }
        .btn-pastel:hover { background-color: #c5916d; color: white; }
    </style>
</head>
<body>
    <div class="container my-5">
        <h3 class="fw-bold mb-4">Checkout Pesanan Buket</h3>
        <form method="POST">
            <div class="row">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm p-4 rounded-4 mb-4">
                        <h5 class="fw-bold mb-3">Informasi Penerima & Pengiriman</h5>
                        <div class="mb-3">
                            <label class="form-label small">Nama Penerima</label>
                            <input type="text" name="nama_penerima" class="form-control" value="<?php echo $pelanggan['nama']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">No. HP / WhatsApp Penerima</label>
                            <input type="text" name="no_hp_penerima" class="form-control" value="<?php echo $pelanggan['no_hp']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Alamat Tujuan / Catatan Pengambilan</label>
                            <textarea name="alamat_penerima" class="form-control" rows="3" required><?php echo $pelanggan['alamat']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Metode Penerimaan</label>
                            <select name="metode_pengiriman" class="form-select" required>
                                <option value="Ambil di Stand">Ambil Sendiri di Stand Toko</option>
                                <option value="Diantar Penjual">Diantar Langsung oleh Penjual (+ Ongkir)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Catatan Khusus untuk Buket (Opsional)</label>
                            <textarea name="catatan_pesanan" class="form-control" placeholder="Contoh: Ucapan selamat wisuda untuk Kak Siska..." rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <h5 class="fw-bold mb-3">Rincian Pembayaran</h5>
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($items as $itm): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><?php echo $itm['nama_produk']; ?> (x<?php echo $itm['jumlah']; ?>)</span>
                                <span>Rp <?php echo number_format($itm['harga'] * $itm['jumlah'], 0, ',', '.'); ?></span>
                            </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between px-0 fw-bold">
                                <span>Total Belanja</span>
                                <span class="text-danger">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span>
                            </li>
                        </ul>
                        <button type="submit" name="proses_pesanan" class="btn btn-pastel w-100 py-2 rounded-pill">Buat Pesanan Sekarang</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>