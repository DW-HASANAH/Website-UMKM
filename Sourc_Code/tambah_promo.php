
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data admin
$admin = $pdo->query("SELECT * FROM admin")->fetchAll(PDO::FETCH_ASSOC);

// Proses simpan promo
if (isset($_POST['simpan'])) {

    $id_admin = $_POST['id_admin'];
    $nama_promo = trim($_POST['nama_promo']);
    $deskripsi = trim($_POST['deskripsi']);
    $jenis_diskon = $_POST['jenis_diskon'];
    $nilai_diskon = $_POST['nilai_diskon'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status = $_POST['status'];

    try {
        if ($nama_promo == "" || $id_admin == "") {
            throw new Exception("Nama promo dan admin wajib diisi!");
        }

        if ($nilai_diskon <= 0) {
            throw new Exception("Nilai diskon harus lebih dari 0!");
        }

        if ($jenis_diskon == "persentase" && $nilai_diskon > 100) {
            throw new Exception("Diskon persentase maksimal 100%!");
        }

        if ($tanggal_selesai < $tanggal_mulai) {
            throw new Exception("Tanggal selesai tidak boleh sebelum tanggal mulai!");
        }

        $sql = "INSERT INTO promo
                (id_admin, nama_promo, deskripsi, jenis_diskon,
                 nilai_diskon, tanggal_mulai, tanggal_selesai, status)
                VALUES
                (:admin, :nama, :deskripsi, :jenis,
                 :nilai, :mulai, :selesai, :status)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':admin' => $id_admin,
            ':nama' => $nama_promo,
            ':deskripsi' => $deskripsi,
            ':jenis' => $jenis_diskon,
            ':nilai' => $nilai_diskon,
            ':mulai' => $tanggal_mulai,
            ':selesai' => $tanggal_selesai,
            ':status' => $status
        ]);

        $pesan = "Promo berhasil ditambahkan!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Promo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Promo</h4>
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
                        <option value="">Pilih Admin</option>

                        <?php foreach ($admin as $a): ?>
                            <option value="<?= $a['id_admin'] ?>">
                                <?= htmlspecialchars($a['nama']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Promo</label>
                    <input type="text" name="nama_promo"
                           class="form-control" maxlength="150" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"
                              rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Diskon</label>
                    <select name="jenis_diskon" class="form-select" required>
                        <option value="">Pilih Jenis Diskon</option>
                        <option value="persentase">Persentase (%)</option>
                        <option value="nominal">Nominal (Rp)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nilai Diskon</label>
                    <input type="number" name="nilai_diskon"
                           class="form-control" min="0.01"
                           step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Promo</label>
                    <select name="status" class="form-select" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>

                <button type="submit" name="simpan"
                        class="btn btn-primary">
                    Simpan Promo
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
