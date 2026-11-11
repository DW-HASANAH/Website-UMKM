
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data admin untuk pilihan dropdown
$stmt = $pdo->query("SELECT id_admin, nama FROM admin");
$admin = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses tambah kategori
if (isset($_POST['simpan'])) {
    $id_admin = $_POST['id_admin'];
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);

    if ($id_admin == "" || $nama_kategori == "") {
        $pesan = "Admin dan nama kategori wajib diisi!";
    } else {
        $sql = "INSERT INTO kategori
                (id_admin, nama_kategori, deskripsi)
                VALUES (:id_admin, :nama_kategori, :deskripsi)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_admin' => $id_admin,
            ':nama_kategori' => $nama_kategori,
            ':deskripsi' => $deskripsi
        ]);

        $pesan = "Kategori berhasil ditambahkan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4>Tambah Kategori</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Admin</label>
                    <select name="id_admin" class="form-select" required>
                        <option value="">-- Pilih Admin --</option>

                        <?php foreach ($admin as $a): ?>
                            <option value="<?= $a['id_admin'] ?>">
                                <?= htmlspecialchars($a['nama']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="nama_kategori"
                           class="form-control" maxlength="100"
                           placeholder="Masukkan nama kategori" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                              rows="4"
                              placeholder="Masukkan deskripsi kategori"></textarea>
                </div>

                <button type="submit" name="simpan"
                        class="btn btn-primary">
                    Simpan Kategori
                </button>

            </form>
        </div>
    </div>
</div>

</body>
</html>
