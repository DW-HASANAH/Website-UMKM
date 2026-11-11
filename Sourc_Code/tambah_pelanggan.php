
<?php
include 'koneksi.php';

$pesan = "";

// Proses tambah pelanggan
if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama']);
    $no_hp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $alamat = trim($_POST['alamat']);

    try {

        if ($nama == "" || $no_hp == "") {
            throw new Exception("Nama dan nomor HP wajib diisi!");
        }

        if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format email tidak valid!");
        }

        // Query simpan data
        $sql = "INSERT INTO pelanggan
                (nama, no_hp, email, alamat)
                VALUES
                (:nama, :no_hp, :email, :alamat)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nama' => $nama,
            ':no_hp' => $no_hp,
            ':email' => $email ?: null,
            ':alamat' => $alamat
        ]);

        $pesan = "Data pelanggan berhasil ditambahkan!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pelanggan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Pelanggan</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Nama Pelanggan</label>
                    <input type="text"
                           name="nama"
                           class="form-control"
                           maxlength="100"
                           placeholder="Masukkan nama pelanggan"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor HP</label>
                    <input type="tel"
                           name="no_hp"
                           class="form-control"
                           maxlength="20"
                           placeholder="Masukkan nomor HP"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           maxlength="100"
                           placeholder="Masukkan email">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat"
                              class="form-control"
                              rows="4"
                              placeholder="Masukkan alamat pelanggan"></textarea>
                </div>

                <button type="submit"
                        name="simpan"
                        class="btn btn-primary">
                    Simpan Pelanggan
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
