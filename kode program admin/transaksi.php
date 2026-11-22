<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$nama_toko = 'Kembang Cerita Flowers';
$foto_logo = 'logo.jpg';

try {
    $q_toko = mysqli_query($conn, "SELECT nama_toko, foto_logo FROM profil_toko WHERE id_profil = 1 LIMIT 1");
    if ($q_toko && mysqli_num_rows($q_toko) > 0) {
        $r_toko = mysqli_fetch_assoc($q_toko);
        if (!empty($r_toko['nama_toko'])) $nama_toko = $r_toko['nama_toko'];
        if (!empty($r_toko['foto_logo'])) $foto_logo = $r_toko['foto_logo'];
    }
} catch (Throwable $e) {
    // Abaikan jika tabel belum tersedia
}

$pesan = '';
if (isset($_POST['update_status_trx'])) {
    $id_trx = (int)($_POST['id_transaksi'] ?? 0);
    $status_baru = mysqli_real_escape_string($conn, trim($_POST['status_transaksi'] ?? 'Diproses'));
    if ($id_trx > 0) {
        mysqli_query($conn, "UPDATE transaksi SET status_transaksi = '$status_baru' WHERE id_transaksi = '$id_trx'");
        $pesan = 'Status transaksi berhasil diperbarui.';
    }
}

$daftar_transaksi = [];
try {
    $q_trx = mysqli_query($conn, "SELECT t.*, p.*
        FROM transaksi t
        LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
        ORDER BY t.id_transaksi DESC");
    if ($q_trx) {
        while ($row = mysqli_fetch_assoc($q_trx)) {
            $daftar_transaksi[] = $row;
        }
    }
} catch (Throwable $e) {
    $daftar_transaksi = [];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - <?php echo htmlspecialchars($nama_toko); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --burgundy-main: #632b3e;
            --rose-pink: #d9778c;
            --soft-cream: #fffafb;
            --blush-bg: #fff5f7;
        }
        body {
            background-color: var(--blush-bg);
            font-family: 'Poppins', sans-serif;
            color: #4a2e35;
        }
        .card-custom {
            border: none;
            border-radius: 24px;
            background: var(--soft-cream);
            box-shadow: 0 10px 30px rgba(99, 43, 62, 0.06);
        }
        .form-title {
            font-family: 'Playfair Display', serif;
            color: var(--burgundy-main);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-10 p-5">
                <div class="mb-4">
                    <h3 class="fw-bold form-title mb-1">Data Transaksi</h3>
                    <p class="text-muted small mb-0">Kelola pesanan masuk, metode pembayaran, dan status pemrosesan</p>
                </div>

                <?php if ($pesan !== ''): ?>
                    <div class="alert alert-success rounded-4"><?php echo htmlspecialchars($pesan); ?></div>
                <?php endif; ?>

                <div class="card card-custom p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Kode / Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Metode Bayar</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($daftar_transaksi)): ?>
                                    <?php foreach ($daftar_transaksi as $trx): ?>
                                    <?php
                                        $nama_plg = $trx['nama_pelanggan'] ?? $trx['nama_lengkap'] ?? $trx['nama'] ?? 'Pelanggan';
                                        $st_now = $trx['status_transaksi'] ?? 'Diproses';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small"><?php echo htmlspecialchars($trx['kode_referensi'] ?? ('TRX-' . ($trx['id_transaksi'] ?? ''))); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($trx['tanggal_transaksi'] ?? '-'); ?></small>
                                        </td>
                                        <td class="small fw-medium"><?php echo htmlspecialchars($nama_plg); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($trx['metode_bayar'] ?? '-'); ?></td>
                                        <td class="fw-bold small" style="color: var(--rose-pink);">Rp <?php echo number_format((int)($trx['total_pembelian'] ?? 0), 0, ',', '.'); ?></td>
                                        <td>
                                            <form method="POST" class="d-flex gap-2 align-items-center">
                                                <input type="hidden" name="id_transaksi" value="<?php echo (int)($trx['id_transaksi'] ?? 0); ?>">
                                                <select name="status_transaksi" class="form-select form-select-sm rounded-pill" style="width: 145px;">
                                                    <option value="Diproses" <?php echo $st_now === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                    <option value="Dikirim" <?php echo $st_now === 'Dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                                    <option value="Selesai" <?php echo $st_now === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                    <option value="Dibatalkan" <?php echo $st_now === 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                </select>
                                                <button type="submit" name="update_status_trx" class="btn btn-sm btn-outline-danger rounded-pill px-3">Simpan</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada data transaksi.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>