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

$daftar_pelanggan = [];
try {
    $q_plg = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
    if ($q_plg) {
        while ($row = mysqli_fetch_assoc($q_plg)) {
            $daftar_pelanggan[] = $row;
        }
    }
} catch (Throwable $e) {
    $daftar_pelanggan = [];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - <?php echo htmlspecialchars($nama_toko); ?></title>
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
                    <h3 class="fw-bold form-title mb-1">Data Pelanggan</h3>
                    <p class="text-muted small mb-0">Daftar pelanggan terdaftar dan pembeli yang bertransaksi</p>
                </div>

                <div class="card card-custom p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>No. Telepon / WA</th>
                                    <th>Email</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($daftar_pelanggan)): ?>
                                    <?php foreach ($daftar_pelanggan as $idx => $plg): ?>
                                    <?php
                                        $nama_p = $plg['nama_pelanggan'] ?? $plg['nama_lengkap'] ?? $plg['nama'] ?? '-';
                                        $telp_p = $plg['telepon'] ?? $plg['no_hp'] ?? $plg['no_whatsapp'] ?? '-';
                                    ?>
                                    <tr>
                                        <td class="small"><?php echo $idx + 1; ?></td>
                                        <td class="small fw-semibold"><?php echo htmlspecialchars($nama_p); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($telp_p); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($plg['email'] ?? '-'); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($plg['alamat'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada data pelanggan.</td>
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