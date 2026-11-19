<?php
include '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

// 1. PROSES TAMBAH PRODUK
if (isset($_POST['tambah_produk'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $id_kat = $_POST['id_kategori'];
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

    mysqli_query($conn, "INSERT INTO produk (id_kategori, nama_produk, deskripsi, harga, stok, foto, status) VALUES ('$id_kat', '$nama', '$deskripsi', '$harga', '$stok', '$nama_foto', 'Aktif')");
    echo "<script>alert('Produk berhasil ditambahkan!'); window.location='produk.php';</script>";
}

// 2. PROSES HAPUS PRODUK
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk = '$id'");
    echo "<script>alert('Produk berhasil dihapus!'); window.location='produk.php';</script>";
}

$list_produk = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori");
$list_kat = mysqli_query($conn, "SELECT * FROM kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin Kembang Cerita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .sidebar { background-color: #632b3e; min-height: 100vh; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; display: block; padding: 10px 20px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background-color: #d9778c; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-4">
                <h4 class="fw-bold mb-4">Admin Panel</h4>
                <a href="produk.php" class="active"><i class="fa fa-box me-2"></i> Kelola Produk</a>
                <a href="../index.php" class="mt-4" target="_blank"><i class="fa fa-globe me-2"></i> Lihat Toko</a>
                <a href="logout.php"><i class="fa fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Konten Utama -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold">Kelola Produk Buket</h3>
                    <span class="text-muted">Halo, <strong><?php echo $_SESSION['nama_admin']; ?></strong></span>
                </div>

                <!-- Form Tambah Produk -->
                <div class="card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white">
                    <h5 class="fw-bold mb-3" style="color: #632b3e;">Tambah Produk Baru</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small">Nama Buket</label>
                                <input type="text" name="nama_produk" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small">Kategori</label>
                                <select name="id_kategori" class="form-control" required>
                                    <?php while($k = mysqli_fetch_assoc($list_kat)): ?>
                                        <option value="<?php echo $k['id_kategori']; ?>"><?php echo $k['nama_kategori']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small">Stok</label>
                                <input type="number" name="stok" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small">Foto Buket</label>
                                <input type="file" name="foto" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small">Deskripsi Produk</label>
                                <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <button type="submit" name="tambah_produk" class="btn btn-dark rounded-pill px-4">Simpan Produk</button>
                    </form>
                </div>

                <!-- Tabel Data Produk (Read & Delete) -->
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                    <h5 class="fw-bold mb-3" style="color: #632b3e;">Daftar Produk</h5>
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($list_produk)): ?>
                            <tr>
                                <td><img src="../uploads/<?php echo $row['foto']; ?>" width="50" height="50" class="rounded-3 object-fit-cover"></td>
                                <td class="fw-bold"><?php echo $row['nama_produk']; ?></td>
                                <td><?php echo $row['nama_kategori']; ?></td>
                                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $row['stok']; ?></td>
                                <td>
                                    <a href="edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-warning btn-sm text-white rounded-circle"><i class="fa fa-edit"></i></a>
                                    <a href="produk.php?hapus=<?php echo $row['id_produk']; ?>" class="btn btn-danger btn-sm rounded-circle" onclick="return confirm('Yakin ingin menghapus produk ini?')"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>