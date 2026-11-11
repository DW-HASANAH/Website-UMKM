
<?php
include 'koneksi.php';

$pesan = "";

// Mengambil data promo
$promo = $pdo->query("SELECT * FROM promo")->fetchAll(PDO::FETCH_ASSOC);

// Mengambil data produk
$produk = $pdo->query("SELECT * FROM produk")->fetchAll(PDO::FETCH_ASSOC);

// Proses simpan
if (isset($_POST['simpan'])) {

    $id_promo = $_POST['id_promo'];
    $id_produk = $_POST['id_produk'];

    try {
        // Cek apakah produk sudah terdaftar dalam promo
        $cek = $pdo->prepare(
            "SELECT * FROM promo_produk
             WHERE id_promo = ? AND id_produk = ?"
        );

        $cek->execute([$id_promo, $id_produk]);

        if ($cek->rowCount() > 0) {
            throw new Exception("Produk sudah terdaftar pada promo ini!");
        }

        // Simpan data
        $sql = "INSERT INTO promo_produk
                (id_promo, id_produk)
                VALUES (:promo, :produk)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':promo' => $id_promo,
            ':produk' => $id_produk
        ]);

        $pesan = "Produk berhasil ditambahkan ke promo!";

    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Promo Produk</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4>Tambah Promo Produk</h4>
        </div>

        <div class="card-body">

            <?php if ($pesan != ""): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Pilih Promo</label>

                    <select name="id_promo" class="form-select" required>
                        <option value="">-- Pilih Promo --</option>

                        <?php foreach ($promo as $p): ?>
                            <option value="<?= $p['id_promo'] ?>">
                                <?= htmlspecialchars($p['nama_promo']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Produk</label>

                    <select name="id_produk" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>

                        <?php foreach ($produk as $pr): ?>
                            <option value="<?= $pr['id_produk'] ?>">
                                <?= htmlspecialchars($pr['nama_produk']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <button type="submit" name="simpan"
                        class="btn btn-primary">
                    Simpan Promo Produk
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
