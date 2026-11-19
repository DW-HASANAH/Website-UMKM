<?php
include '../koneksi.php';
if (!isset($_SESSION['id_admin'])) { header("Location: login.php"); exit; }
$jml_produk = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM produk"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Kembang Cerita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --burgundy-main: #632b3e; --burgundy-dark: #451c2a; --rose-pink: #d9778c; --soft-cream: #fffafb; --blush-bg: #fff5f7; }
        body { background-color: var(--blush-bg); font-family: 'Poppins', sans-serif; color: #4a2e35; }
        .sidebar { background: linear-gradient(160deg, var(--burgundy-main) 0%, var(--burgundy-dark) 100%); min-height: 100vh; color: white; }
        .sidebar h4 { font-family: 'Playfair Display', serif; }
        .sidebar a { color: rgba(255, 255, 255, 0.85); text-decoration: none; display: block; padding: 11px 18px; border-radius: 50px; margin-bottom: 6px; font-size: 13.5px; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, var(--rose-pink) 0%, #b8566d 100%); color: white; transform: translateX(4px); }
        .card-custom { border: none; border-radius: 28px; background: var(--soft-cream); box-shadow: 0 10px 30px rgba(99, 43, 62, 0.06); }
    </style>
</head>
<body>
    <div class="container-fluid"><div class="row">
        <div class="col-md-2 sidebar p-4 d-flex flex-column justify-content-between">
            <div>
                <div class="mb-4 px-2"><h4 class="fw-bold mb-0">Kembang Cerita</h4><small class="text-white-50">Admin Dashboard</small></div>
                <div class="nav-menu mt-4">
                    <a href="dashboard.php" class="active mb-1"><i class="fa fa-chart-pie me-2"></i> Dashboard</a>
                    <a href="produk.php" class="mb-1"><i class="fa fa-box me-2"></i> Kelola Produk</a>
                    <a href="transaksi.php" class="mb-1"><i class="fa fa-shopping-bag me-2"></i> Transaksi</a>
                    <a href="pelanggan.php" class="mb-1"><i class="fa fa-users me-2"></i> Data Pelanggan</a>
                    <a href="feedback.php" class="mb-1"><i class="fa fa-comments me-2"></i> Ulasan / Feedback</a>
                    <a href="setting.php" class="mb-1"><i class="fa fa-cog me-2"></i> Pengaturan Toko</a>
                </div>
            </div>
            <div><a href="../index.php" class="mb-2" target="_blank"><i class="fa fa-globe me-2"></i> Lihat Toko</a><a href="logout.php" style="background: rgba(255,255,255,0.1);"><i class="fa fa-sign-out-alt me-2"></i> Logout</a></div>
        </div>
        <div class="col-md-10 p-5">
            <h3 class="fw-bold mb-4" style="font-family:'Playfair Display'; color:var(--burgundy-main);">Dashboard Toko</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-custom p-4 border-start border-4 border-danger">
                        <h6 class="text-muted small">Total Produk</h6>
                        <h3 class="fw-bold mb-0" style="color: var(--burgundy-main);"><?php echo $jml_produk; ?> Buket</h3>
                    </div>
                </div>
            </div>
        </div>
    </div></div>
</body>
</html>