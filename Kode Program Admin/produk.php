<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

// 1. PROSES TAMBAH PRODUK
if (isset($_POST['tambah_produk'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    $nama_foto = $_FILES['foto']['name'];
    $lokasi_foto = $_FILES['foto']['tmp_name'];
    if (!empty($nama_foto)) {
        move_uploaded_file($lokasi_foto, "../uploads/" . $nama_foto);
    } else {
        $nama_foto = "default.jpg";
    }

    mysqli_query($conn, "INSERT INTO produk (nama_produk, deskripsi, harga, stok, foto, status) VALUES ('$nama', '$deskripsi', '$harga', '$stok', '$nama_foto', 'Aktif')");
    echo "<script>alert('Produk berhasil ditambahkan!'); window.location='produk.php';</script>";
}

// 2. PROSES HAPUS PRODUK
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk = '$id'");
    echo "<script>alert('Produk berhasil dihapus!'); window.location='produk.php';</script>";
}

$list_produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin Kembang Cerita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --burgundy-main: #632b3e;
            --burgundy-dark: #451c2a;
            --rose-pink: #d9778c;
            --soft-cream: #fffafb;
            --blush-bg: #fff5f7;
        }

        body {
            background-color: var(--blush-bg);
            font-family: 'Poppins', sans-serif;
            color: #4a2e35;
        }

        /* Sidebar Estetik */
        .sidebar {
            background: linear-gradient(160deg, var(--burgundy-main) 0%, var(--burgundy-dark) 100%);
            min-height: 100vh;
            color: white;
            box-shadow: 4px 0 20px rgba(99, 43, 62, 0.1);
        }

        .sidebar h4 {
            font-family: 'Playfair Display', serif;
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: block;
            padding: 11px 18px;
            border-radius: 50px;
            margin-bottom: 6px;
            font-size: 13.5px;
            transition: all 0.3s ease;
        }

        .sidebar a:hover, .sidebar a.active {
            background: linear-gradient(135deg, var(--rose-pink) 0%, #b8566d 100%);
            color: white;
            transform: translateX(4px);
            box-shadow: 0 5px 15px rgba(217, 119, 140, 0.35);
        }

        /* Kartu & Konten */
        .card-custom {
            border: none;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(99, 43, 62, 0.06);
            background: var(--soft-cream);
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            color: var(--burgundy-main);
        }

        .form-control {
            border-radius: 50px;
            padding: 11px 20px;
            font-size: 13.5px;
            border: 1.5px solid #f0d5dc;
            background-color: #ffffff;
            color: #4a2e35;
        }

        textarea.form-control {
            border-radius: 20px !important;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--rose-pink);
            box-shadow: 0 0 0 4px rgba(217, 119, 140, 0.15);
        }

        /* Tombol Gradasi Feminin */
        .btn-feminine-grad {
            background: linear-gradient(135deg, var(--rose-pink) 0%, #b8566d 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 11px 25px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 6px 18px rgba(217, 119, 140, 0.3);
            transition: all 0.2s;
        }

        .btn-feminine-grad:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(217, 119, 140, 0.4);
            color: white;
        }

        /* Tabel */
        .table thead th {
            background-color: #fbebee;
            color: var(--burgundy-main);
            border-bottom: none;
            font-weight: 600;
            font-size: 13.5px;
            padding: 14px;
        }

        .table thead th:first-child { border-top-left-radius: 14px; border-bottom-left-radius: 14px; }
        .table thead th:last-child { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }

        .table tbody td {
            padding: 14px;
            vertical-align: middle;
            font-size: 14px;
            border-color: #f5e6ea;
        }
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
                        <h3 class="fw-bold form-title mb-1">Kelola Produk Buket</h3>
                        <p class="text-muted small mb-0">Atur katalog produk buket bunga Kembang Cerita</p>
                    </div>
                    <div class="bg-white px-4 py-2 rounded-pill shadow-sm border border-light">
                        <span class="text-muted small">Halo, <strong style="color: var(--burgundy-main);"><?php echo $_SESSION['nama_admin'] ?? 'Admin'; ?></strong></span>
                    </div>
                </div>

                <!-- Form Tambah Produk -->
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold form-title mb-3 fs-5"><i class="fa fa-plus-circle me-2" style="color: var(--rose-pink);"></i>Tambah Buket Baru</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold text-muted">Nama Buket</label>
                                <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Rose Elegance" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold text-muted">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control" placeholder="Contoh: 150000" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-semibold text-muted">Stok</label>
                                <input type="number" name="stok" class="form-control" placeholder="Jumlah stok" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold text-muted">Foto Buket</label>
                                <input type="file" name="foto" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold text-muted">Deskripsi Produk</label>
                                <textarea name="deskripsi" class="form-control" rows="2" placeholder="Tulis deskripsi singkat buket..."></textarea>
                            </div>
                        </div>
                        <button type="submit" name="tambah_produk" class="btn btn-feminine-grad mt-2">
                            <i class="fa fa-save me-1"></i> Simpan Produk
                        </button>
                    </form>
                </div>

                <!-- Tabel Data Produk -->
                <div class="card card-custom p-4">
                    <h5 class="fw-bold form-title mb-3 fs-5"><i class="fa fa-list me-2" style="color: var(--rose-pink);"></i>Daftar Produk Toko</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($list_produk) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($list_produk)): ?>
                                    <tr>
                                        <td>
                                            <img src="../uploads/<?php echo $row['foto'] ?: 'default.jpg'; ?>" width="52" height="52" class="rounded-4 shadow-sm object-fit-cover">
                                        </td>
                                        <td class="fw-bold" style="color: var(--burgundy-main);"><?php echo $row['nama_produk']; ?></td>
                                        <td class="fw-semibold text-secondary">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $row['stok']; ?> pcs</td>
                                        <td class="text-center">
                                            <a href="edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm text-white rounded-circle me-1" style="background-color: #d9778c; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;" title="Edit">
                                                <i class="fa fa-edit" style="font-size: 13px;"></i>
                                            </a>
                                            <a href="produk.php?hapus=<?php echo $row['id_produk']; ?>" class="btn btn-sm text-white rounded-circle" style="background-color: #b5374d; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;" onclick="return confirm('Yakin ingin menghapus produk ini?')" title="Hapus">
                                                <i class="fa fa-trash" style="font-size: 13px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada produk yang ditambahkan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>