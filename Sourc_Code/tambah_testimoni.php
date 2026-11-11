
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data pelanggan dan admin
$pelanggan = $pdo->query("SELECT * FROM pelanggan")
                 ->fetchAll(PDO::FETCH_ASSOC);

$admin = $pdo->query("SELECT * FROM admin")
             ->fetchAll(PDO::FETCH_ASSOC);

// Proses simpan testimoni
if (isset($_POST['simpan'])) {

    $id_pelanggan = $_POST['id_pelanggan'];
    $id_admin = $_POST['id_admin'];
    $nama = trim($_POST['nama_pelanggan']);
    $isi = trim($_POST['isi_testimoni']);
    $rating = $_POST['rating'];
    $status = $_POST['status'];

    try {

        if (!$id_pelanggan || !$id_admin || $nama == "" || $isi == "") {
            throw new Exception("Semua data wajib diisi!");
        }

        if ($rating < 1 || $rating > 5) {
            throw new Exception("Rating harus antara 1 sampai 5!");
        }

        $sql = "INSERT INTO testimoni
                (id_pelanggan, id_admin, nama_pelanggan,
                 isi_testimoni, rating, status)
                VALUES
                (:pelanggan, :admin, :nama,
                 :isi, :rating, :status)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':pelanggan' => $id_pelanggan,
            ':admin' => $id_admin,
            ':nama' => $nama,
            ':isi' => $isi,
            ':rating' => $rating,
            ':status' => $status
        ]);

        $pesan = "Testimoni berhasil ditambahkan!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Testimoni</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Testimoni</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Pelanggan</label>

                    <select name="id_pelanggan"
                            class="form-select" required>

                        <option value="">Pilih Pelanggan</option>

                        <?php foreach ($pelanggan as $p): ?>
                            <option value="<?= $p['id_pelanggan'] ?>">
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Admin</label>

                    <select name="id_admin"
                            class="form-select" required>

                        <option value="">Pilih Admin</option>

                        <?php foreach ($admin as $a): ?>
                            <option value="<?= $a['id_admin'] ?>">
                                <?= htmlspecialchars($a['nama']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Pelanggan</label>

                    <input type="text"
                           name="nama_pelanggan"
                           class="form-control"
                           maxlength="100"
                           placeholder="Masukkan nama pelanggan"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Isi Testimoni</label>

                    <textarea name="isi_testimoni"
                              class="form-control"
                              rows="4"
                              placeholder="Masukkan ulasan pelanggan"
                              required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating</label>

                    <select name="rating"
                            class="form-select" required>

                        <option value="">Pilih Rating</option>
                        <option value="5">5 - Sangat Baik</option>
                        <option value="4">4 - Baik</option>
                        <option value="3">3 - Cukup</option>
                        <option value="2">2 - Kurang</option>
                        <option value="1">1 - Sangat Kurang</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Testimoni</label>

                    <select name="status"
                            class="form-select" required>

                        <option value="Menunggu">Menunggu</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Ditolak">Ditolak</option>

                    </select>
                </div>

                <button type="submit"
                        name="simpan"
                        class="btn btn-primary">
                    Simpan Testimoni
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
