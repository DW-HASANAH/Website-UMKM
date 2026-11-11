
<?php
include 'koneksi.php';

$pesan = "";

if (isset($_POST['simpan'])) {
    $id_admin = $_POST['id_admin'];
    $nama_toko = $_POST['nama_toko'];
    $deskripsi = $_POST['deskripsi'];
    $alamat = $_POST['alamat'];
    $no_whatsapp = $_POST['no_whatsapp'];
    $email = $_POST['email'];

    $foto_logo = "";

    if (!empty($_FILES['foto_logo']['name'])) {
        $folder = "uploads/";

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $foto_logo = time() . "_" . basename($_FILES['foto_logo']['name']);
        move_uploaded_file(
            $_FILES['foto_logo']['tmp_name'],
            $folder . $foto_logo
        );
    }

    $sql = "INSERT INTO profil_toko
            (id_admin, nama_toko, deskripsi, alamat,
             no_whatsapp, email, foto_logo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $id_admin,
        $nama_toko,
        $deskripsi,
        $alamat,
        $no_whatsapp,
        $email,
        $foto_logo
    ]);

    $pesan = "Profil toko berhasil ditambahkan!";
}

$admin = $pdo->query("SELECT * FROM admin")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Profil Toko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Tambah Profil Toko</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan): ?>
                <div class="alert alert-success">
                    <?= $pesan ?>
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
                    <label class="form-label">Nama Toko</label>
                    <input type="text" name="nama_toko" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor WhatsApp</label>
                    <input type="text" name="no_whatsapp" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Foto Logo</label>
                    <input type="file" name="foto_logo" class="form-control" accept="image/*">
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    Simpan Profil
                </button>

            </form>
        </div>
    </div>
</div>

</body>
</html>
