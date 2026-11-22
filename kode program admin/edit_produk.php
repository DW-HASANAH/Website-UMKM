<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID produk dari URL
if (!isset($_GET['id'])) {
    header("Location: produk.php");
    exit;
}

$id_produk = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
$produk = mysqli_fetch_assoc($query);

if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

// Proses Update Produk
if (isset($_POST['update_produk'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    $nama_foto = $_FILES['foto']['name'];
    $lokasi_foto = $_FILES['foto']['tmp_name'];

    // Jika admin mengunggah foto baru
    if (!empty($nama_foto)) {
        move_uploaded_file($lokasi_foto, "../uploads/" . $nama_foto);
        // Update data beserta foto baru
        mysqli_query($conn, "UPDATE produk SET nama_produk = '$nama', harga = '$harga', stok = '$stok', deskripsi = '$deskripsi', foto = '$nama_foto' WHERE id_produk = '$id_produk'");
    } else {
        // Update data tanpa mengubah foto lama
        mysqli_query($conn, "UPDATE produk SET nama_produk = '$nama', harga = '$harga', stok = '$stok', deskripsi = '$deskripsi' WHERE id_produk = '$id_produk'");
    }

    echo "<script>alert('Produk berhasil diperbarui!'); window.location='produk.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Admin Kembang Cerita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --burgundy-main: #632b3e; --burgundy-dark: #451c2a; --rose-pink: #d9778c; --soft-cream: #fffafb; --blush-bg: #fff5f7; }
        body { background-color: var(--blush-bg); font-family: 'Poppins', sans-serif; color: #4a2e35; }
        .sidebar { background: linear-gradient(160deg, var(--burgundy-main) 0%, var(--burgundy-dark) 100%); min-height: 100vh; color: white; box-shadow: 4px 0 20px rgba(99, 43, 62, 0.1); }
        .sidebar h4 { font-family: 'Playfair Display', serif; }
        .sidebar a { color: rgba(255, 255, 255, 0.85); text-decoration: none; display: block; padding: 11px 18px; border-radius: 50px; margin-bottom: 6px; font-size: 13.5px; transition: all 0.3s ease; }
        .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, var(--rose-pink) 0%, #b8566d 100%); color: white; transform: translateX(4px); box-shadow: 0 5px 15px rgba(217, 119, 140, 0.35); }
        .card-custom { border: none; border-radius: 28px; box-shadow: 0 10px 30px rgba(99, 43, 62, 0.06); background: var(--soft-cream); }
        .form-title { font-family: 'Playfair Display', serif; color: var(--burgundy-main); }
        .form-control { border-radius: 50px; padding: 11px 20px; font-size: 13.5px; border: 1.5px solid #f0d5dc; background-color: #ffffff; color: #4a2e35; }
        textarea.form-control { border-radius: 20px !important; }
        .form-control:focus { outline: none; border-color: var(--rose-pink); box-shadow: 0 0 0 4px rgba(217, 119, 140, 0.15); }
        .btn-feminine-grad { background: linear-gradient(135deg, var(--rose-pink) 0%, #b8566d 100%); color: white; border: none; border-radius: 50px; padding: 11px 25px; font-weight: 600; font-size: 14px; box-shadow: 0 6px 18px rgba(217, 119, 140, 0.3); }
        .btn-feminine-grad:hover { transform: translateY(-2px); color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-4 px-2">
                        <h4 class="fw-bold mb-0">Kembang Cerita</h4>
                        <small class="text-white-50" style="font-size: 11.5px;">Admin Dashboard</small>
                    </div>
                    <div class="nav-menu mt-4">
                        <a href="dashboard.php" class="mb-1"><i class="fa fa-chart-pie me-2"></i> Dashboard</a>
                        <a href="produk.php" class="active mb-1"><i class="fa fa-box me-2"></i> Kelola Produk</a>
                        <a href="transaksi.php" class="mb-1"><i class="fa fa-shopping-bag me-2"></i> Transaksi</a>
                        <a href="pelanggan.php" class="mb-1"><i class="fa fa-users me-2"></i> Data Pelanggan</a>
                        <a href="feedback.php" class="mb-1"><i class="fa fa-comments me-2"></i> Ulasan / Feedback</a>
                        <a href="setting.php" class="mb-1"><i class="fa fa-cog me-2"></i> Pengaturan Toko</a>
                    </div>
                </div>
                <div>
                    <a href="../index.php" class="mb-2" target="_blank"><i class="fa fa-globe me-2"></i> Lihat Toko</a>
                    <a href="logout.php" style="background: rgba(255,255,255,0.1);"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Konten Utama -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold form-title mb-1">Edit Produk Buket</h3>
                        <p class="text-muted small mb-0">Perbarui informasi atau detail buket bunga</p>
                    </div>
                    <a href="produk.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <!-- Form Edit Produk -->
                <div class="card card-custom p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold text-muted">Nama Buket</label>
                                <input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold text-muted">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control" value="<?php echo $produk['harga']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold text-muted">Stok</label>
                                <input type="number" name="stok" class="form-control" value="<?php echo $produk['stok']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold text-muted">Foto Buket Baru (Opsional)</label>
                                <input type="file" name="foto" class="form-control">
                                <small class="text-muted" style="font-size: 11.5px;">Biarkan kosong jika tidak ingin mengganti foto.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold text-muted">Foto Saat Ini</label>
                                <div>
                                    <img src="../uploads/<?php echo $produk['foto'] ?: 'default.jpg'; ?>" width="60" height="60" class="rounded-4 shadow-sm object-fit-cover mt-1">
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-semibold text-muted">Deskripsi Produk</label>
                                <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" name="update_produk" class="btn btn-feminine-grad mt-2">
                            <i class="fa fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>