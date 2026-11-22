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
if (isset($_GET['setujui'])) {
    $id_t = (int)$_GET['setujui'];
    if ($id_t > 0) {
        mysqli_query($conn, "UPDATE testimoni SET status = 'Disetujui' WHERE id_testimoni = '$id_t'");
        $pesan = 'Ulasan berhasil disetujui dan ditampilkan di halaman utama.';
    }
}
if (isset($_GET['hapus'])) {
    $id_t = (int)$_GET['hapus'];
    if ($id_t > 0) {
        mysqli_query($conn, "DELETE FROM testimoni WHERE id_testimoni = '$id_t'");
        $pesan = 'Ulasan berhasil dihapus.';
    }
}

$daftar_ulasan = [];
try {
    $q_ul = mysqli_query($conn, "SELECT * FROM testimoni ORDER BY id_testimoni DESC");
    if ($q_ul) {
        while ($row = mysqli_fetch_assoc($q_ul)) {
            $daftar_ulasan[] = $row;
        }
    }
} catch (Throwable $e) {
    $daftar_ulasan = [];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan / Feedback - <?php echo htmlspecialchars($nama_toko); ?></title>
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
                    <h3 class="fw-bold form-title mb-1">Ulasan / Feedback</h3>
                    <p class="text-muted small mb-0">Kelola testimoni dan rating pelanggan</p>
                </div>

                <?php if ($pesan !== ''): ?>
                    <div class="alert alert-success rounded-4"><?php echo htmlspecialchars($pesan); ?></div>
                <?php endif; ?>

                <div class="card card-custom p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>Rating</th>
                                    <th>Komentar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($daftar_ulasan)): ?>
                                    <?php foreach ($daftar_ulasan as $ul): ?>
                                    <?php $id_ul = (int)($ul['id_testimoni'] ?? 0); ?>
                                    <tr>
                                        <td class="small fw-semibold"><?php echo htmlspecialchars($ul['nama'] ?? 'Pelanggan'); ?></td>
                                        <td class="small text-warning"><?php echo (int)($ul['rating'] ?? 5); ?> <i class="fa fa-star"></i></td>
                                        <td class="small"><?php echo htmlspecialchars($ul['komentar'] ?? ''); ?></td>
                                        <td><span class="badge rounded-pill bg-secondary"><?php echo htmlspecialchars($ul['status'] ?? 'Disetujui'); ?></span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="feedback.php?setujui=<?php echo $id_ul; ?>" class="btn btn-sm btn-outline-success rounded-pill px-2.5">Setujui</a>
                                                <a href="feedback.php?hapus=<?php echo $id_ul; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="return confirm('Hapus ulasan ini?')"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada ulasan masuk.</td>
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