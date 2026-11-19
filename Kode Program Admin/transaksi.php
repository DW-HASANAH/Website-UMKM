<?php
include '../koneksi.php';
if (!isset($_SESSION['id_admin'])) { header("Location: login.php"); exit; }

// Update Status Pesanan jika tombol diklik
if (isset($_POST['update_status'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $status_baru = $_POST['status_pesanan'];
    mysqli_query($conn, "UPDATE transaksi SET status = '$status_baru' WHERE id_transaksi = '$id_transaksi'");
    echo "<script>alert('Status transaksi berhasil diperbarui!'); window.location='transaksi.php';</script>";
}

// Ambil data transaksi (sesuaikan nama tabel jika menggunakan struktur standar pesanan)
$query = "SELECT t.*, p.nama_lengkap FROM transaksi t JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan ORDER BY t.id_transaksi DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Admin Kembang Cerita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --burgundy-main: #632b3e; --burgundy-dark: #451c2a; --rose-pink: #d9778c; --soft-cream: #fffafb; --blush-bg: #fff5f7; }
        body { background-color: var(--blush-bg); font-family: 'Poppins', sans-serif; color: #4a2e35; }
        .sidebar { background: linear-gradient(160deg, var(--burgundy-main) 0%, var(--burgundy-dark) 100%); min-height: 100vh; color: white; box-shadow: 4px 0 20px rgba(99, 43, 62, 0.1); }
        .sidebar h4 { font-family: 'Playfair Display', serif; }
        .sidebar a { color: rgba(255, 255, 255, 0.85); text-decoration: none; display: block; padding: 11px 18px; border-radius: 50px; margin-bottom: 6px; font-size: 13.5px; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, var(--rose-pink) 0%, #b8566d 100%); color: white; transform: translateX(4px); box-shadow: 0 5px 15px rgba(217, 119, 140, 0.35); }
        .card-custom { border: none; border-radius: 28px; background: var(--soft-cream); box-shadow: 0 10px 30px rgba(99, 43, 62, 0.06); }
        .table thead th { background-color: #fbebee; color: var(--burgundy-main); border-bottom: none; font-weight: 600; font-size: 13.5px; padding: 14px; }
        .table thead th:first-child { border-top-left-radius: 14px; border-bottom-left-radius: 14px; }
        .table thead th:last-child { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
        .table tbody td { padding: 14px; vertical-align: middle; font-size: 14px; border-color: #f5e6ea; }
    </style>
</head>
<body>
<div class="container-fluid"><div class="row">
    <div class="col-md-2 sidebar p-4 d-flex flex-column justify-content-between">
        <div>
            <div class="mb-4 px-2"><h4 class="fw-bold mb-0">Kembang Cerita</h4><small class="text-white-50">Admin Dashboard</small></div>
            <div class="nav-menu mt-4">
                <a href="dashboard.php" class="mb-1"><i class="fa fa-chart-pie me-2"></i> Dashboard</a>
                <a href="produk.php" class="mb-1"><i class="fa fa-box me-2"></i> Kelola Produk</a>
                <a href="transaksi.php" class="active mb-1"><i class="fa fa-shopping-bag me-2"></i> Transaksi</a>
                <a href="pelanggan.php" class="mb-1"><i class="fa fa-users me-2"></i> Data Pelanggan</a>
                <a href="feedback.php" class="mb-1"><i class="fa fa-comments me-2"></i> Ulasan / Feedback</a>
                <a href="setting.php" class="mb-1"><i class="fa fa-cog me-2"></i> Pengaturan Toko</a>
            </div>
        </div>
        <div><a href="../index.php" class="mb-2" target="_blank"><i class="fa fa-globe me-2"></i> Lihat Toko</a><a href="logout.php" style="background: rgba(255,255,255,0.1);"><i class="fa fa-sign-out-alt me-2"></i> Logout</a></div>
    </div>
    <div class="col-md-10 p-5">
        <h3 class="fw-bold mb-1" style="font-family:'Playfair Display'; color:var(--burgundy-main);">Kelola Transaksi Pesanan</h3>
        <p class="text-muted small mb-4">Pantau status pesanan dan pembayaran buket bunga dari pelanggan.</p>
        
        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th class="text-center">Aksi / Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $row['id_transaksi']; ?></td>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['tanggal_transaksi'] ?? date('Y-m-d'); ?></td>
                                <td class="fw-semibold text-secondary">Rp <?php echo number_format($row['total_harga'] ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge px-3 py-2 rounded-pill bg-warning text-dark">
                                        <?php echo $row['status'] ?? 'Menunggu'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" class="d-inline-flex gap-2">
                                        <input type="hidden" name="id_transaksi" value="<?php echo $row['id_transaksi']; ?>">
                                        <select name="status_pesanan" class="form-select form-select-sm rounded-pill" style="width: 140px;">
                                            <option value="Menunggu">Menunggu</option>
                                            <option value="Diproses">Diproses</option>
                                            <option value="Dikirim">Dikirim</option>
                                            <option value="Selesai">Selesai</option>
                                            <option value="Dibatalkan">Dibatalkan</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm text-white rounded-pill px-3" style="background-color: var(--rose-pink);">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data transaksi pesanan masuk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div></div>
</body>
</html>