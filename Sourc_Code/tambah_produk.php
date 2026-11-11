
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data admin dan kategori
$admin = $pdo->query("SELECT * FROM admin")->fetchAll(PDO::FETCH_ASSOC);

$kategori = $pdo->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);

// Proses simpan produk
if (isset($_POST['simpan'])) {

    $id_kategori = $_POST['id_kategori'];
    $id_admin = $_POST['id_admin'];
    $nama = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $status = $_POST['status'];

    try {
        if ($nama == "" || $harga < 0 || $stok < 0) {
            throw new Exception("Data produk tidak valid!");
        }

        // Upload foto
        $foto = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

            $folder = "uploads/";

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $tipe = mime_content_type($_FILES['foto']['tmp_name']);
            $format = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($format[$tipe])) {
                throw new Exception("Foto harus JPG, PNG, atau WEBP!");
            }

            if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                throw new Exception("Ukuran foto maksimal 2 MB!");
            }

            $namaFoto = uniqid('produk_') . '.' . $format[$tipe];

            if (!move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                $folder . $namaFoto
            )) {
                throw new Exception("Upload foto gagal!");
            }

            $foto = $namaFoto;
        }

        // Simpan ke database
        $sql = "INSERT INTO produk
                (id_kategori, id_admin, nama_produk, deskripsi,
                 harga, stok, foto, status)
                VALUES
                (:kategori, :admin, :nama, :deskripsi,
                 :harga, :stok, :foto, :status)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':kategori' => $id_kategori,
            ':admin' => $id_admin,
            ':nama' => $nama,
            ':deskripsi' => $deskripsi,
            ':harga' => $harga,
            ':stok' => $stok,
            ':foto' => $foto,
            ':status' => $status
        ]);

        $pesan = "Produk berhasil ditambahkan!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Produk</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Admin</label>
                    <select name="id_admin" class="form-select" required>
                        <option value="">Pilih Admin</option>

                        <?php foreach ($admin as $a): ?>
                            <option value="<?= $a['id_admin'] ?>">
                                <?= htmlspecialchars($a['nama']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="id_kategori" class="form-select" required>
                        <option value="">Pilih Kategori</option>

                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= $k['id_kategori'] ?>">
                                <?= htmlspecialchars($k['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk"
                           class="form-control" maxlength="150" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                              rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga"
                           class="form-control" min="0"
                           step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok"
                           class="form-control" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Foto Produk</label>
                    <input type="file" name="foto"
                           class="form-control"
                           accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">
                        Format JPG, PNG, WEBP. Maksimal 2 MB.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Produk</label>
                    <select name="status" class="form-select" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>

                <button type="submit" name="simpan"
                        class="btn btn-primary">
                    Simpan Produk
                </button>

            </form>
        </div>
    </div>
</div>

</body>
</html>
