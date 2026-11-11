
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data pesanan
$pesanan = $pdo->query("
    SELECT p.id_pesanan, p.total_harga, pl.nama
    FROM pesanan p
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    ORDER BY p.id_pesanan DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Proses simpan pembayaran
if (isset($_POST['simpan'])) {

    $id_pesanan = $_POST['id_pesanan'];
    $tanggal = $_POST['tanggal_bayar'];
    $metode = $_POST['metode_pembayaran'];
    $jumlah = $_POST['jumlah_bayar'];
    $status = $_POST['status_pembayaran'];

    try {

        if (!$id_pesanan || $jumlah <= 0) {
            throw new Exception("Data pembayaran tidak valid!");
        }

        // Validasi pesanan
        $stmt = $pdo->prepare("
            SELECT total_harga
            FROM pesanan
            WHERE id_pesanan = ?
        ");

        $stmt->execute([$id_pesanan]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception("Pesanan tidak ditemukan!");
        }

        if ($jumlah > $data['total_harga']) {
            throw new Exception("Jumlah pembayaran melebihi total pesanan!");
        }

        // Upload bukti pembayaran
        $bukti = null;

        if (isset($_FILES['bukti_pembayaran']) &&
            $_FILES['bukti_pembayaran']['error'] != UPLOAD_ERR_NO_FILE) {

            if ($_FILES['bukti_pembayaran']['error'] != UPLOAD_ERR_OK) {
                throw new Exception("Upload bukti pembayaran gagal!");
            }

            $folder = "uploads/bukti_pembayaran/";

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $tipe = mime_content_type($_FILES['bukti_pembayaran']['tmp_name']);

            $format = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($format[$tipe])) {
                throw new Exception("Bukti harus berupa JPG, PNG, atau WEBP!");
            }

            if ($_FILES['bukti_pembayaran']['size'] > 2 * 1024 * 1024) {
                throw new Exception("Ukuran bukti maksimal 2 MB!");
            }

            $namaFile = uniqid('bukti_') . '.' . $format[$tipe];

            if (!move_uploaded_file(
                $_FILES['bukti_pembayaran']['tmp_name'],
                $folder . $namaFile
            )) {
                throw new Exception("Gagal menyimpan file!");
            }

            $bukti = $namaFile;
        }

        // Simpan pembayaran
        $sql = "INSERT INTO pembayaran
                (id_pesanan, tanggal_bayar, metode_pembayaran,
                 jumlah_bayar, bukti_pembayaran, status_pembayaran)
                VALUES
                (:pesanan, :tanggal, :metode,
                 :jumlah, :bukti, :status)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':pesanan' => $id_pesanan,
            ':tanggal' => $tanggal,
            ':metode' => $metode,
            ':jumlah' => $jumlah,
            ':bukti' => $bukti,
            ':status' => $status
        ]);

        $pesan = "Pembayaran berhasil ditambahkan!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pembayaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Pembayaran</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Nomor Pesanan</label>

                    <select name="id_pesanan"
                            class="form-select" required>

                        <option value="">Pilih Pesanan</option>

                        <?php foreach ($pesanan as $p): ?>
                            <option value="<?= $p['id_pesanan'] ?>">
                                Pesanan #<?= $p['id_pesanan'] ?>
                                - <?= htmlspecialchars($p['nama']) ?>
                                (Rp<?= number_format($p['total_harga'], 0, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Pembayaran</label>

                    <input type="datetime-local"
                           name="tanggal_bayar"
                           class="form-control"
                           value="<?= date('Y-m-d\TH:i') ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran</label>

                    <select name="metode_pembayaran"
                            class="form-select" required>

                        <option value="">Pilih Metode</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="COD">COD</option>
                        <option value="Tunai">Tunai</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Pembayaran (Rp)</label>

                    <input type="number"
                           name="jumlah_bayar"
                           class="form-control"
                           min="0.01"
                           step="0.01"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bukti Pembayaran</label>

                    <input type="file"
                           name="bukti_pembayaran"
                           class="form-control"
                           accept="image/jpeg,image/png,image/webp">

                    <small class="text-muted">
                        Format JPG, PNG, WEBP. Maksimal 2 MB.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Pembayaran</label>

                    <select name="status_pembayaran"
                            class="form-select" required>

                        <option value="Menunggu Verifikasi">
                            Menunggu Verifikasi
                        </option>

                        <option value="Berhasil">Berhasil</option>

                        <option value="Ditolak">Ditolak</option>

                    </select>
                </div>

                <button type="submit"
                        name="simpan"
                        class="btn btn-primary">
                    Simpan Pembayaran
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
