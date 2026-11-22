<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$nama_toko = 'Kembang Cerita Flowers';
$deskripsi_toko = 'Rangkai cerita indahmu bersama koleksi buket bunga segar dan elegan.';
$no_wa = '085657241895';
$alamat_toko = 'Upasaya, Bontotiro, Bulukumba & Kota Makassar';
$instagram_toko = 'kembangcerita.flowers';
$status_toko = 'Buka';
$foto_logo = 'logo.jpg';

try {
    $q_toko = mysqli_query($conn, "SELECT * FROM profil_toko WHERE id_profil = 1 LIMIT 1");
    if ($q_toko && mysqli_num_rows($q_toko) > 0) {
        $r_toko = mysqli_fetch_assoc($q_toko);
        if (!empty($r_toko['nama_toko'])) $nama_toko = $r_toko['nama_toko'];
        if (!empty($r_toko['deskripsi'])) $deskripsi_toko = $r_toko['deskripsi'];
        if (!empty($r_toko['no_whatsapp'])) $no_wa = $r_toko['no_whatsapp'];
        if (!empty($r_toko['alamat'])) $alamat_toko = $r_toko['alamat'];
        if (!empty($r_toko['instagram'])) $instagram_toko = $r_toko['instagram'];
        if (!empty($r_toko['status_toko'])) $status_toko = $r_toko['status_toko'];
        if (!empty($r_toko['foto_logo'])) $foto_logo = $r_toko['foto_logo'];
    }
} catch (Throwable $e) {
    // Gunakan nilai default jika tabel belum tersedia
}

$pesan = '';
if (isset($_POST['simpan_pengaturan'])) {
    $nama_baru = trim($_POST['nama_toko'] ?? $nama_toko);
    $desk_baru = trim($_POST['deskripsi'] ?? $deskripsi_toko);
    $wa_baru = trim($_POST['no_whatsapp'] ?? $no_wa);
    $alm_baru = trim($_POST['alamat'] ?? $alamat_toko);
    $ig_baru = trim($_POST['instagram'] ?? $instagram_toko);
    $st_baru = trim($_POST['status_toko'] ?? $status_toko);
    $logo_baru = $foto_logo;

    if (!empty($_FILES['foto_logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $nama_file_logo = 'logo.' . $ext;
            if (@move_uploaded_file($_FILES['foto_logo']['tmp_name'], dirname(__DIR__) . '/uploads/' . $nama_file_logo)) {
                $logo_baru = $nama_file_logo;
            }
        }
    }

    try {
        $nb = mysqli_real_escape_string($conn, $nama_baru);
        $db = mysqli_real_escape_string($conn, $desk_baru);
        $wb = mysqli_real_escape_string($conn, $wa_baru);
        $ab = mysqli_real_escape_string($conn, $alm_baru);
        $ib = mysqli_real_escape_string($conn, $ig_baru);
        $sb = mysqli_real_escape_string($conn, $st_baru);
        $lb = mysqli_real_escape_string($conn, $logo_baru);

        $cek = mysqli_query($conn, "SELECT id_profil FROM profil_toko WHERE id_profil = 1 LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE profil_toko SET
                nama_toko = '$nb',
                deskripsi = '$db',
                no_whatsapp = '$wb',
                alamat = '$ab',
                instagram = '$ib',
                status_toko = '$sb',
                foto_logo = '$lb'
                WHERE id_profil = 1");
        } else {
            mysqli_query($conn, "INSERT INTO profil_toko (id_profil, nama_toko, deskripsi, no_whatsapp, alamat, instagram, status_toko, foto_logo)
                VALUES (1, '$nb', '$db', '$wb', '$ab', '$ib', '$sb', '$lb')");
        }

        $nama_toko = $nama_baru;
        $deskripsi_toko = $desk_baru;
        $no_wa = $wa_baru;
        $alamat_toko = $alm_baru;
        $instagram_toko = $ig_baru;
        $status_toko = $st_baru;
        $foto_logo = $logo_baru;
        $pesan = 'Pengaturan toko berhasil disimpan.';
    } catch (Throwable $e) {
        $pesan = 'Pengaturan diperbarui pada sesi tampilan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Toko - <?php echo htmlspecialchars($nama_toko); ?></title>
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
        .btn-rose {
            background-color: var(--rose-pink);
            color: #ffffff;
            border: none;
            border-radius: 50px;
        }
        .btn-rose:hover {
            background-color: #c2657a;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-10 p-5">
                <div class="mb-4">
                    <h3 class="fw-bold form-title mb-1">Pengaturan Toko</h3>
                    <p class="text-muted small mb-0">Kelola identitas toko, kontak, status operasional, dan logo</p>
                </div>

                <?php if ($pesan !== ''): ?>
                    <div class="alert alert-success rounded-4"><?php echo htmlspecialchars($pesan); ?></div>
                <?php endif; ?>

                <div class="card card-custom p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Toko</label>
                                <input type="text" name="nama_toko" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($nama_toko); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Status Toko</label>
                                <select name="status_toko" class="form-select rounded-pill px-3">
                                    <option value="Buka" <?php echo $status_toko === 'Buka' ? 'selected' : ''; ?>>Buka</option>
                                    <option value="Tutup Sementara" <?php echo $status_toko === 'Tutup Sementara' ? 'selected' : ''; ?>>Tutup Sementara</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">No. WhatsApp</label>
                                <input type="text" name="no_whatsapp" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($no_wa); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Username Instagram</label>
                                <input type="text" name="instagram" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($instagram_toko); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Alamat Toko</label>
                                <input type="text" name="alamat" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($alamat_toko); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Logo Toko Baru (.jpg / .jpeg / .png)</label>
                                <input type="file" name="foto_logo" class="form-control rounded-pill px-3" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Deskripsi Toko</label>
                                <textarea name="deskripsi" rows="3" class="form-control rounded-4 px-3"><?php echo htmlspecialchars($deskripsi_toko); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" name="simpan_pengaturan" class="btn btn-rose px-5 py-2 mt-4 fw-semibold">
                            <i class="fa fa-save me-1"></i> Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>