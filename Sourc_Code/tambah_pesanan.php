
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data pelanggan dan admin
$pelanggan = $pdo->query("SELECT * FROM pelanggan")->fetchAll(PDO::FETCH_ASSOC);
$admin = $pdo->query("SELECT * FROM admin")->fetchAll(PDO::FETCH_ASSOC);

// Proses simpan pesanan
if (isset($_POST['simpan'])) {

    $id_pelanggan = $_POST['id_pelanggan'];
    $id_admin = $_POST['id_admin'];
    $tanggal = $_POST['tanggal_pesanan'];
    $nama = trim($_POST['nama_penerima']);
    $no_hp = trim($_POST['no_hp_penerima']);
    $alamat = trim($_POST['alamat_penerima']);
    $ongkir = $_POST['ongkos_kirim'];
    $catatan = trim($_POST['catatan_pesanan']);
    $status = $_POST['status_pesanan'];

    try {

        if (!$id_pelanggan || !$id_admin || $nama == "" || $alamat == "") {
            throw new Exception("Data wajib diisi!");
        }

        if ($ongkir < 0) {
            throw new Exception("Ongkos kirim tidak boleh negatif!");
        }

        // Total sementara 0, akan dihitung setelah detail pesanan dibuat
        $total = 0;

        $sql = "INSERT INTO pesanan
                (id_pelanggan, id_admin, tanggal_pesanan,
                 nama_penerima, no_hp_penerima, alamat_penerima,
                 ongkos_kirim, catatan_pesanan, total_harga,
                 status_pesanan)
                VALUES
                (:pelanggan, :admin, :tanggal,
                 :nama, :no_hp, :alamat,
                 :ongkir, :catatan, :total, :status)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':pelanggan' => $id_pelanggan,
            ':admin' => $id_admin,
            ':tanggal' => $tanggal,
            ':nama' => $nama,
            ':no_hp' => $no_hp,
            ':alamat' => $alamat,
            ':ongkir' => $ongkir,
            ':catatan' => $catatan,
            ':total' => $total,
            ':status' => $status
        ]);

        $pesan = "Pesanan berhasil ditambahkan!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pesanan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Pesanan</h4>
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
                    <select name="id_pelanggan" class="form-select" required>
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
                    <label class="form-label">Tanggal Pesanan</label>
                    <input type="datetime-local"
                           name="tanggal_pesanan"
                           class="form-control"
                           value="<?= date('Y-m-d\TH:i') ?>"
                           required>
                </div>

                <hr>
                <h5>Informasi Penerima</h5>

                <div class="mb-3">
                    <label class="form-label">Nama Penerima</label>
                    <input type="text" name="nama_penerima"
                           class="form-control" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor HP Penerima</label>
                    <input type="text" name="no_hp_penerima"
                           class="form-control" maxlength="20">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Penerima</label>
                    <textarea name="alamat_penerima"
                              class="form-control"
                              rows="3" required></textarea>
                </div>

                <hr>
                <h5>Informasi Pesanan</h5>

                <div class="mb-3">
                    <label class="form-label">Ongkos Kirim (Rp)</label>
                    <input type="number" name="ongkos_kirim"
                           class="form-control"
                           min="0" step="0.01"
                           value="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan Pesanan</label>
                    <textarea name="catatan_pesanan"
                              class="form-control"
                              rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Pesanan</label>
                    <select name="status_pesanan" class="form-select" required>
                        <option value="Menunggu Pembayaran">
                            Menunggu Pembayaran
                        </option>
                        <option value="Diproses">Diproses</option>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <button type="submit" name="simpan"
                        class="btn btn-primary">
                    Simpan Pesanan
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
