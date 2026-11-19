<?php
include 'koneksi.php';

// Ambil profil toko untuk nomor WA
$profil = mysqli_query($conn, "SELECT * FROM profil_toko WHERE id_profil = 1 LIMIT 1");
$toko = mysqli_fetch_assoc($profil);

// Cek apakah keranjang kosong
if (empty($_SESSION['keranjang'])) {
    echo "<script>alert('Keranjang belanjaanmu masih kosong!'); window.location='index.php';</script>";
    exit;
}

// Proses Hapus Item dari Keranjang
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    unset($_SESSION['keranjang'][$id_hapus]);
    echo "<script>alert('Produk dihapus dari keranjang.'); window.location='keranjang.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Kembang Cerita Flowers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --feminine-pink: #d9778c;
            --feminine-dark: #4a2e35;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #fff9fb; color: var(--feminine-dark); }
        .card-custom { border: none; border-radius: 20px; background: white; box-shadow: 0 5px 20px rgba(217, 119, 140, 0.06); }
        .btn-feminine { background-color: var(--feminine-pink); color: white; border-radius: 50px; }
        .btn-feminine:hover { background-color: #c2657a; color: white; }
    </style>
</head>
<body>

    <!-- Navbar Sederhana -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php" style="color: var(--feminine-pink);">Kembang Cerita</a>
            <a href="index.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Lanjut Belanja</a>
        </div>
    </nav>

    <div class="container my-5" style="max-width: 900px;">
        <h2 class="fw-bold mb-4" style="color: var(--feminine-dark);"><i class="fa fa-shopping-bag me-2" style="color: var(--feminine-pink);"></i>Keranjang Belanja Buket</h2>

        <!-- Pengecekan Login: Jika belum login, beri peringatan untuk login/daftar -->
        <?php if (!isset($_SESSION['id_pelanggan'])): ?>
            <div class="alert alert-warning rounded-4 p-4 mb-4 d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <h5 class="fw-bold mb-1"><i class="fa fa-user-lock me-2"></i>Silakan Login Terlebih Dahulu</h5>
                    <p class="small mb-0 text-muted">Kamu harus masuk atau mendaftar akun agar pesanan buketmu bisa tersimpan dan diproses.</p>
                </div>
                <a href="login.php" class="btn btn-dark rounded-pill px-4 fw-medium">Login / Daftar</a>
            </div>
        <?php endif; ?>

        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Buket</th>
                            <th>Harga</th>
                            <th class="text-center">Jumlah</th>
                            <th>Subtotal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_belanja = 0;
                        foreach ($_SESSION['keranjang'] as $id_produk => $jumlah):
                            $ambil = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
                            $pecah = mysqli_fetch_assoc($ambil);
                            if(!$pecah) continue;
                            
                            $subtotal = $pecah['harga'] * $jumlah;
                            $total_belanja += $subtotal;
                        ?>
                        <tr>
                            <td><img src="uploads/<?php echo $pecah['foto'] ?: 'default.jpg'; ?>" width="55" height="55" class="rounded-3 object-fit-cover shadow-sm"></td>
                            <td class="fw-bold"><?php echo $pecah['nama_produk']; ?></td>
                            <td>Rp <?php echo number_format($pecah['harga'], 0, ',', '.'); ?></td>
                            <td class="text-center fw-semibold"><?php echo $jumlah; ?> pcs</td>
                            <td class="fw-bold" style="color: var(--feminine-pink);">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="keranjang.php?hapus=<?php echo $id_produk; ?>" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <h4 class="fw-bold mb-0">Total: <span style="color: var(--feminine-pink);">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span></h4>
                
                <?php if (isset($_SESSION['id_pelanggan'])): ?>
                    <!-- Jika sudah login, tombol langsung mengarahkan pesanan ke WhatsApp dengan rincian keranjang -->
                    <?php 
                        $pesan_wa = "Halo Kembang Cerita Flowers, saya ingin memesan beberapa buket berikut:%0A";
                        foreach ($_SESSION['keranjang'] as $id_p => $jml) {
                            $q_p = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id_p'");
                            $d_p = mysqli_fetch_assoc($q_p);
                            if($d_p) {
                                $pesan_wa .= "- " . urlencode($d_p['nama_produk']) . " (" . $jml . " pcs)%0A";
                            }
                        }
                        $pesan_wa .= "%0ATotal Pembayaran: *Rp " . number_format($total_belanja, 0, ',', '.') . "*%0AMohon info selanjutnya ya, terima kasih!";
                    ?>
                    <a href="https://wa.me/<?php echo $toko['no_whatsapp']; ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn btn-feminine px-5 py-2 fw-bold shadow-sm">
                        <i class="fab fa-whatsapp me-2"></i> Checkout via WhatsApp
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary px-5 py-2 fw-bold rounded-pill" disabled>
                        Login Dulu untuk Checkout
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>