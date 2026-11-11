
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data pesanan
$pesanan = $pdo->query("
    SELECT p.id_pesanan, pl.nama
    FROM pesanan p
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    ORDER BY p.id_pesanan DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Mengambil data produk
$produk = $pdo->query("
    SELECT * FROM produk
    WHERE status = 'Aktif'
")->fetchAll(PDO::FETCH_ASSOC);

// Proses tambah detail pesanan
if (isset($_POST['simpan'])) {

    $id_pesanan = $_POST['id_pesanan'];
    $id_produk = $_POST['id_produk'];
    $jumlah = (int) $_POST['jumlah'];

    try {

        if (!$id_pesanan || !$id_produk || $jumlah <= 0) {
            throw new Exception("Semua data wajib diisi dengan benar!");
        }

        $pdo->beginTransaction();

        // Mengambil harga dan stok produk
        $stmt = $pdo->prepare("
            SELECT harga, stok
            FROM produk
            WHERE id_produk = ?
        ");

        $stmt->execute([$id_produk]);
        $data_produk = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data_produk) {
            throw new Exception("Produk tidak ditemukan!");
        }

        if ($jumlah > $data_produk['stok']) {
            throw new Exception("Stok produk tidak mencukupi!");
        }

        // Mengambil data pesanan dan mengunci baris
        $stmt = $pdo->prepare("
            SELECT ongkos_kirim
            FROM pesanan
            WHERE id_pesanan = ?
            FOR UPDATE
        ");

        $stmt->execute([$id_pesanan]);
        $data_pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data_pesanan) {
            throw new Exception("Pesanan tidak ditemukan!");
        }

        // Menghitung harga
        $harga = $data_produk['harga'];
        $subtotal = $harga * $jumlah;

        // Menyimpan detail pesanan
        $sql = "INSERT INTO detail_pesanan
                (id_pesanan, id_produk, jumlah,
                 harga_satuan, subtotal)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $id_pesanan,
            $id_produk,
            $jumlah,
            $harga,
            $subtotal
        ]);

        // Menghitung ulang total pesanan
        $stmt = $pdo->prepare("
            SELECT SUM(subtotal)
            FROM detail_pesanan
            WHERE id_pesanan = ?
        ");

        $stmt->execute([$id_pesanan]);
        $total_produk = $stmt->fetchColumn();

        $total_harga = $total_produk + $data_pesanan['ongkos_kirim'];

        // Memperbarui total harga pesanan
        $stmt = $pdo->prepare("
            UPDATE pesanan
            SET total_harga = ?
            WHERE id_pesanan = ?
        ");

        $stmt->execute([$total_harga, $id_pesanan]);

        $pdo->commit();

        $pesan = "Detail pesanan berhasil ditambahkan!";

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Detail Pesanan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Detail Pesanan</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Nomor Pesanan</label>

                    <select name="id_pesanan"
                            class="form-select" required>

                        <option value="">Pilih Pesanan</option>

                        <?php foreach ($pesanan as $p): ?>
                            <option value="<?= $p['id_pesanan'] ?>">
                                Pesanan #<?= $p['id_pesanan'] ?>
                                - <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Produk</label>

                    <select name="id_produk"
                            class="form-select" required>

                        <option value="">Pilih Produk</option>

                        <?php foreach ($produk as $pr): ?>
                            <option value="<?= $pr['id_produk'] ?>">
                                <?= htmlspecialchars($pr['nama_produk']) ?>
                                - Rp<?= number_format($pr['harga'], 0, ',', '.') ?>
                                (Stok: <?= $pr['stok'] ?>)
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Produk</label>

                    <input type="number"
                           name="jumlah"
                           class="form-control"
                           min="1"
                           required
                           placeholder="Masukkan jumlah produk">
                </div>

                <button type="submit"
                        name="simpan"
                        class="btn btn-primary">
                    Simpan Detail Pesanan
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
