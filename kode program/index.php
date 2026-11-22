<?php
include 'koneksi.php';

// Proses login admin langsung dari landing page jika form modal disubmit
$pesan_login_admin = '';
if (isset($_POST['login_admin_landing'])) {
    $username_adm = mysqli_real_escape_string($conn, trim($_POST['username_admin'] ?? ''));
    $password_adm = (string)($_POST['password_admin'] ?? '');

    $q_adm = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username_adm' LIMIT 1");
    if ($q_adm && mysqli_num_rows($q_adm) > 0) {
        $row_adm = mysqli_fetch_assoc($q_adm);
        $hash_db = (string)($row_adm['password_hash'] ?? '');

        $valid_login = false;
        if ($hash_db !== '') {
            if (password_verify($password_adm, $hash_db) || $password_adm === $hash_db || md5($password_adm) === $hash_db) {
                $valid_login = true;
            }
        }

        if ($valid_login) {
            $_SESSION['id_admin'] = $row_adm['id_admin'] ?? 1;
            $_SESSION['nama_admin'] = $row_adm['nama_lengkap'] ?? $row_adm['nama'] ?? $row_adm['username'] ?? 'Admin';
            echo "<script>alert('Login Admin berhasil!'); window.location='admin/dashboard.php';</script>";
            exit;
        } else {
            $pesan_login_admin = 'Password admin salah.';
        }
    } else {
        $pesan_login_admin = 'Username admin tidak ditemukan.';
    }
}

// Ambil data profil toko dari database (id_profil = 1)
$nama_toko = 'Kembang Cerita Flowers';
$deskripsi_toko = 'Rangkai cerita indahmu bersama koleksi buket bunga segar dan elegan.';
$no_wa = '085657241895';
$alamat_toko = 'Upasaya, Bontotiro, Bulukumba & Kota Makassar';
$instagram_toko = 'kembangcerita.flowers';
$status_toko = 'Buka';
$foto_logo = 'logo.jpg';

try {
    $profil = mysqli_query($conn, "SELECT * FROM profil_toko WHERE id_profil = 1 LIMIT 1");
    if ($profil && mysqli_num_rows($profil) > 0) {
        $toko = mysqli_fetch_assoc($profil);
        if (!empty($toko['nama_toko'])) $nama_toko = $toko['nama_toko'];
        if (!empty($toko['deskripsi'])) $deskripsi_toko = $toko['deskripsi'];
        if (!empty($toko['no_whatsapp'])) $no_wa = $toko['no_whatsapp'];
        if (!empty($toko['alamat'])) $alamat_toko = $toko['alamat'];
        if (!empty($toko['instagram'])) $instagram_toko = $toko['instagram'];
        if (!empty($toko['status_toko'])) $status_toko = $toko['status_toko'];
        if (!empty($toko['foto_logo'])) $foto_logo = $toko['foto_logo'];
    }
} catch (Throwable $e) {
    // Gunakan nilai default jika tabel profil_toko belum ada
}

// Cari file logo yang benar-benar ada di folder uploads/ (.jpg, .jpeg, .png, .webp)
function resolveTokoLogo($fotoLogoDb) {
    $bersih = basename((string)$fotoLogoDb);
    if ($bersih === '') {
        $bersih = 'logo.jpg';
    }
    if (file_exists(__DIR__ . '/uploads/' . $bersih)) {
        return 'uploads/' . $bersih;
    }

    $tanpaExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $bersih);
    foreach (['.jpeg', '.jpg', '.png', '.webp'] as $ext) {
        if (file_exists(__DIR__ . '/uploads/' . $tanpaExt . $ext)) {
            return 'uploads/' . $tanpaExt . $ext;
        }
    }

    foreach (['logo.jpeg', 'logo.jpg', 'logo.png', 'logo.webp'] as $cadangan) {
        if (file_exists(__DIR__ . '/uploads/' . $cadangan)) {
            return 'uploads/' . $cadangan;
        }
    }

    return 'uploads/' . $bersih;
}

$logo_url = resolveTokoLogo($foto_logo);

$wa_clean = preg_replace('/[^0-9]/', '', $no_wa);
$wa_link = (substr($wa_clean, 0, 1) === '0') ? '62' . substr($wa_clean, 1) : $wa_clean;

// Helper ambil semua foto untuk 1 produk (foto utama + produk_foto)
function getFotoProduk($conn, $id_produk, $foto_utama) {
    $fotos = [];
    if (!empty($foto_utama)) {
        $fotos[] = $foto_utama;
    }
    try {
        $q = mysqli_query($conn, "SELECT nama_file FROM produk_foto WHERE id_produk = '$id_produk' ORDER BY urutan ASC, id_foto ASC");
        if ($q) {
            while ($f = mysqli_fetch_assoc($q)) {
                if (!empty($f['nama_file']) && !in_array($f['nama_file'], $fotos)) {
                    $fotos[] = $f['nama_file'];
                }
            }
        }
    } catch (Throwable $e) {
        // Abaikan jika tabel produk_foto belum tersedia
    }
    if (empty($fotos)) {
        $fotos[] = 'default.jpg';
    }
    return $fotos;
}

$list_produk = [];
try {
    $produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
    if ($produk && mysqli_num_rows($produk) > 0) {
        while ($p = mysqli_fetch_assoc($produk)) {
            $status_p = $p['status'] ?? 'Aktif';
            if (strtolower((string)$status_p) === 'aktif' || empty($p['status'])) {
                $p['galeri_foto'] = getFotoProduk($conn, (int)$p['id_produk'], $p['foto'] ?? '');
                $list_produk[] = $p;
            }
        }
    }
} catch (Throwable $e) {
    $list_produk = [];
}

$list_testimoni = [];
try {
    $testimoni = mysqli_query($conn, "SELECT * FROM testimoni ORDER BY id_testimoni DESC");
    if ($testimoni && mysqli_num_rows($testimoni) > 0) {
        while ($t = mysqli_fetch_assoc($testimoni)) {
            $st_t = $t['status'] ?? 'Disetujui';
            if (strtolower((string)$st_t) === 'disetujui' || empty($t['status'])) {
                $list_testimoni[] = $t;
            }
        }
    }
} catch (Throwable $e) {
    $list_testimoni = [];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nama_toko); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --feminine-pink: #d9778c;
            --feminine-light: #fff0f3;
            --feminine-dark: #4a2e35;
            --feminine-accent: #f4acb7;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff9fb;
            color: var(--feminine-dark);
            transition: background-color 0.3s, color 0.3s;
        }
        .top-bar {
            background: linear-gradient(135deg, #d9778c, #b55d72) !important;
            color: white;
        }
        .navbar .nav-link {
            white-space: nowrap;
            padding: 6px 12px !important;
            border-radius: 50px;
            color: #dc3545 !important;
            transition: all 0.2s ease;
        }
        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            background-color: rgba(220, 53, 69, 0.08);
            color: #b02a37 !important;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }
        .navbar-actions .btn {
            white-space: nowrap;
        }
        .hero-section {
            background: linear-gradient(135deg, #ffe5ec 0%, #fef6f8 100%);
            border-radius: 25px;
            padding: 50px;
            margin: 25px 0;
            border: 1px solid #f8d7da;
            box-shadow: 0 10px 30px rgba(217, 119, 140, 0.08);
        }
        .btn-feminine {
            background-color: var(--feminine-pink);
            color: white;
            border: none;
            border-radius: 50px;
            transition: all 0.2s;
        }
        .btn-feminine:hover {
            background-color: #c2657a;
            color: white;
            transform: translateY(-2px);
        }
        .card-product {
            border: none;
            border-radius: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 5px 20px rgba(217, 119, 140, 0.06);
            background-color: white;
            cursor: pointer;
        }
        .card-product:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(217, 119, 140, 0.15);
        }
        .card-img-top {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            height: 230px;
            object-fit: cover;
        }
        .modal-carousel-img {
            height: 360px;
            width: 100%;
            object-fit: cover;
            border-radius: 18px;
        }
        .modal-thumb-btn {
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
            background: none;
            opacity: 0.65;
            transition: all 0.2s;
        }
        .modal-thumb-btn.active,
        .modal-thumb-btn:hover {
            border-color: var(--feminine-pink);
            opacity: 1;
            transform: translateY(-2px);
        }
        .modal-thumb-btn img {
            width: 58px;
            height: 58px;
            object-fit: cover;
            display: block;
        }
        .float-wa {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background-color: #25d366;
            color: white;
            border-radius: 50px;
            padding: 12px 22px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            z-index: 1000;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.2s;
        }
        .float-wa:hover {
            transform: scale(1.05);
            color: white;
        }
        .badge-produk {
            background-color: #fbebee;
            color: var(--feminine-pink);
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 50px;
        }

        /* Mode Malam / Dark Theme */
        [data-bs-theme="dark"] body {
            background-color: #161114;
            color: #f8e9ee;
        }
        [data-bs-theme="dark"] .hero-section {
            background: linear-gradient(135deg, #2c1e22 0%, #1a1417 100%);
            border: 1px solid #5a3a42;
        }
        [data-bs-theme="dark"] h1,
        [data-bs-theme="dark"] h2,
        [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] h4,
        [data-bs-theme="dark"] h5,
        [data-bs-theme="dark"] .card-title {
            color: #ffe5ec !important;
        }
        [data-bs-theme="dark"] .navbar .nav-link {
            color: #ff8fa3 !important;
        }
        [data-bs-theme="dark"] .navbar .nav-link:hover,
        [data-bs-theme="dark"] .navbar .nav-link.active {
            background-color: rgba(255, 143, 163, 0.14);
            color: #ffc2d1 !important;
        }
        [data-bs-theme="dark"] .text-muted {
            color: #d8b8c2 !important;
        }
        [data-bs-theme="dark"] .card-product,
        [data-bs-theme="dark"] .modal-content,
        [data-bs-theme="dark"] .card.bg-body {
            background-color: #22181d !important;
            color: #f8e9ee;
        }
        [data-bs-theme="dark"] .badge-produk {
            background-color: #3d222b;
            color: #ff9eb5;
        }
        [data-bs-theme="dark"] .btn-outline-dark {
            color: #ffe5ec;
            border-color: #f4acb7;
        }
        [data-bs-theme="dark"] .btn-outline-dark:hover {
            background-color: var(--feminine-pink);
            border-color: var(--feminine-pink);
            color: white;
        }
        [data-bs-theme="dark"] #modeToggle {
            color: #ff8fa3 !important;
            border-color: #dc3545 !important;
        }
    </style>
</head>
<body>

    <?php if (!empty($pesan_login_admin)): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3 rounded-4" role="alert">
            <i class="fa fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($pesan_login_admin); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Top Bar Diskon & Status Toko -->
    <div class="top-bar py-2 text-center small fw-medium">
        <span><i class="fa fa-tag me-1"></i> Diskon Spesial 10% Khusus Pesanan Pertama! &nbsp;|&nbsp;
        Status Toko: <strong><?php echo htmlspecialchars($status_toko); ?></strong></span>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top shadow-sm bg-body py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center me-3" href="index.php">
                <img src="<?php echo htmlspecialchars($logo_url); ?>" width="45" height="45" class="rounded-circle me-2 object-fit-cover shadow-sm flex-shrink-0" style="border: 2px solid var(--feminine-pink);" alt="<?php echo htmlspecialchars($nama_toko); ?>">
                <span style="color: var(--feminine-pink); font-weight: 700; white-space: nowrap;"><?php echo htmlspecialchars($nama_toko); ?></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 me-lg-3">
                    <li class="nav-item"><a class="nav-link active fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#katalog">Katalog Buket</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#ulasan">Ulasan</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#kontak">Kontak</a></li>
                </ul>

                <div class="navbar-actions mt-3 mt-lg-0">
                    <!-- Mode Gelap / Terang Toggle (warna senada Keranjang) -->
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" id="modeToggle" onclick="toggleMode()" style="width: 33px; height: 33px;" title="Ganti Mode Terang/Malam">
                        <i class="fa fa-moon" id="modeIcon"></i>
                    </button>

                    <a href="keranjang.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-medium">
                        <i class="fa fa-shopping-bag me-1"></i> Keranjang
                    </a>

                    <?php if(isset($_SESSION['id_admin'])): ?>
                        <a href="admin/dashboard.php" class="btn btn-dark btn-sm rounded-pill px-3 fw-medium">Admin Panel</a>
                    <?php else: ?>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-medium" data-bs-toggle="modal" data-bs-target="#modalLoginAdmin">
                            <i class="fa fa-user-shield me-1"></i> Admin
                        </button>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['id_pelanggan'])): ?>
                        <a href="logout.php" class="btn btn-feminine btn-sm px-3 fw-medium shadow-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-feminine btn-sm px-3 fw-medium shadow-sm">Login / Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container">
        <div class="hero-section text-center text-md-start d-md-flex align-items-center justify-content-between">
            <div class="mb-4 mb-md-0">
                <span class="badge mb-3 px-3 py-2 rounded-pill" style="background-color: #fde2e4; color: var(--feminine-pink); font-weight: 500;">
                    <i class="fa fa-heart me-1"></i> Handcrafted with Love
                </span>
                <h1 class="fw-bold display-5">Rangkaian Kisah, <br><span style="color: var(--feminine-pink);">Penuh Makna.</span></h1>
                <p class="text-muted mt-2 mb-4" style="max-width: 500px;"><?php echo htmlspecialchars($deskripsi_toko); ?></p>
                <a href="#katalog" class="btn btn-feminine btn-lg px-5 shadow-sm fw-medium">Belanja Sekarang</a>
            </div>
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Buket Bunga" class="img-fluid rounded-4 shadow-lg" style="max-height: 300px; border: 4px solid white;">
            </div>
        </div>
    </div>

    <!-- Katalog Produk -->
    <div class="container my-5" id="katalog">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Katalog Buket Pilihan</h2>
            <p class="text-muted">Klik produk untuk melihat geser galeri foto dan detail lengkap buket</p>
        </div>

        <div class="row g-4">
            <?php if(!empty($list_produk)): ?>
                <?php foreach($list_produk as $p): ?>
                <?php
                    $id_p = (int)$p['id_produk'];
                    $galeri_foto = $p['galeri_foto'];
                    $jml_foto = count($galeri_foto);
                ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card card-product h-100 bg-body" data-bs-toggle="modal" data-bs-target="#modalDetailProduk<?php echo $id_p; ?>">
                        <div class="position-relative">
                            <img src="uploads/<?php echo htmlspecialchars($galeri_foto[0]); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['nama_produk']); ?>">
                            <?php if($jml_foto > 1): ?>
                                <span class="badge rounded-pill bg-dark bg-opacity-75 position-absolute bottom-0 end-0 m-2 px-2 py-1" style="font-size: 11px;">
                                    <i class="fa fa-images me-1"></i> <?php echo $jml_foto; ?> foto
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-produk">Buket Pilihan</span>
                                <small class="text-muted">Stok: <?php echo (int)($p['stok'] ?? 0); ?></small>
                            </div>
                            <h5 class="card-title fs-6 fw-bold mb-1"><?php echo htmlspecialchars($p['nama_produk']); ?></h5>
                            <p class="fw-bold mb-3 fs-5 mt-auto" style="color: var(--feminine-pink);">Rp <?php echo number_format((int)($p['harga'] ?? 0), 0, ',', '.'); ?></p>

                            <div class="d-grid gap-2" onclick="event.stopPropagation();">
                                <?php if((int)($p['stok'] ?? 0) > 0): ?>
                                    <a href="tambah_keranjang.php?id=<?php echo $id_p; ?>" class="btn btn-outline-dark btn-sm rounded-pill py-2 fw-medium">
                                        <i class="fa fa-shopping-bag me-1"></i> Tambah ke Keranjang
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm rounded-pill py-2 fw-medium" disabled>Stok Habis</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-5">
                    <p>Belum ada produk di katalog. Silakan tambahkan melalui panel admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Detail Produk (Geser Foto Ala Marketplace + Deskripsi) -->
    <?php foreach($list_produk as $p): ?>
    <?php
        $id_p = (int)$p['id_produk'];
        $galeri_foto = $p['galeri_foto'];
        $carousel_id = "carouselProduk" . $id_p;
    ?>
    <div class="modal fade" id="modalDetailProduk<?php echo $id_p; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <span class="badge badge-produk">Detail Buket</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 align-items-center">
                        <!-- Kolom Kiri: Geser Foto -->
                        <div class="col-md-6">
                            <div id="<?php echo $carousel_id; ?>" class="carousel slide" data-bs-ride="false" data-bs-touch="true">
                                <div class="carousel-inner">
                                    <?php foreach($galeri_foto as $idx => $f_item): ?>
                                    <div class="carousel-item <?php echo ($idx === 0) ? 'active' : ''; ?>">
                                        <img src="uploads/<?php echo htmlspecialchars($f_item); ?>" class="modal-carousel-img shadow-sm" alt="<?php echo htmlspecialchars($p['nama_produk']); ?> <?php echo $idx + 1; ?>">
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if(count($galeri_foto) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carousel_id; ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon bg-dark bg-opacity-50 rounded-circle p-3" aria-hidden="true"></span>
                                        <span class="visually-hidden">Sebelumnya</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carousel_id; ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon bg-dark bg-opacity-50 rounded-circle p-3" aria-hidden="true"></span>
                                        <span class="visually-hidden">Berikutnya</span>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if(count($galeri_foto) > 1): ?>
                            <div class="d-flex gap-2 mt-3 justify-content-center flex-wrap">
                                <?php foreach($galeri_foto as $idx => $f_item): ?>
                                    <button type="button"
                                            class="modal-thumb-btn <?php echo ($idx === 0) ? 'active' : ''; ?>"
                                            data-bs-target="#<?php echo $carousel_id; ?>"
                                            data-bs-slide-to="<?php echo $idx; ?>"
                                            aria-label="Foto <?php echo $idx + 1; ?>">
                                        <img src="uploads/<?php echo htmlspecialchars($f_item); ?>" alt="Thumb <?php echo $idx + 1; ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Kolom Kanan: Nama, Harga, Stok, Deskripsi -->
                        <div class="col-md-6">
                            <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($p['nama_produk']); ?></h4>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="fw-bold fs-4" style="color: var(--feminine-pink);">Rp <?php echo number_format((int)($p['harga'] ?? 0), 0, ',', '.'); ?></span>
                                <span class="badge rounded-pill bg-light text-dark border">Stok: <?php echo (int)($p['stok'] ?? 0); ?> pcs</span>
                            </div>

                            <h6 class="fw-semibold small text-uppercase text-muted mb-2">Deskripsi Produk</h6>
                            <p class="text-muted small mb-4" style="line-height: 1.7; white-space: pre-line;"><?php echo htmlspecialchars($p['deskripsi'] ?? 'Buket bunga segar pilihan dirangkai khusus untuk momen spesialmu.'); ?></p>

                            <?php if((int)($p['stok'] ?? 0) > 0): ?>
                                <a href="tambah_keranjang.php?id=<?php echo $id_p; ?>" class="btn btn-feminine w-100 py-2.5 fw-semibold shadow-sm">
                                    <i class="fa fa-shopping-bag me-2"></i> Tambah ke Keranjang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 py-2.5 rounded-pill fw-semibold" disabled>Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Bagian Ulasan Pelanggan -->
    <div class="container my-5 py-4" id="ulasan">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Apa Kata Mereka?</h2>
            <p class="text-muted">Ulasan hangat dari pelanggan setia Kembang Cerita</p>
        </div>
        <div class="row g-4">
            <?php if(!empty($list_testimoni)): ?>
                <?php foreach($list_testimoni as $t): ?>
                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                        <div class="text-warning mb-2">
                            <?php for($i = 0; $i < (int)($t['rating'] ?? 5); $i++): ?>
                                <i class="fa fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted small fst-italic">"<?php echo htmlspecialchars($t['komentar'] ?? ''); ?>"</p>
                        <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- <?php echo htmlspecialchars($t['nama'] ?? 'Pelanggan'); ?></h6>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                        <div class="text-warning mb-2">
                            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                        </div>
                        <p class="text-muted small fst-italic">"Buketnya bener-bener estetik banget, bunganya segar dan pengantaran langsung oleh kurirnya ramah sekali!"</p>
                        <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- Siska Amanda</h6>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                        <div class="text-warning mb-2">
                            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                        </div>
                        <p class="text-muted small fst-italic">"Cocok banget buat kado wisuda sahabatku. Pasti bakal order lagi di sini langganan terus."</p>
                        <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- Rina Melati</h6>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                        <div class="text-warning mb-2">
                            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                        </div>
                        <p class="text-muted small fst-italic">"Pelayanan cepat, bisa request kartu ucapan juga sesuai keinginan. Recommended florist banget!"</p>
                        <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- Dimas Prasetyo</h6>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Login Admin Langsung dari Landing Page -->
    <div class="modal fade" id="modalLoginAdmin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fa fa-user-shield me-2" style="color: var(--feminine-pink);"></i>Login Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Username Admin</label>
                            <input type="text" name="username_admin" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Password Admin</label>
                            <input type="password" name="password_admin" class="form-control rounded-pill px-3" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" name="login_admin_landing" class="btn btn-feminine w-100 py-2 fw-semibold">Masuk ke Dashboard Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo htmlspecialchars($wa_link); ?>" target="_blank" class="float-wa">
        <i class="fa-brands fa-whatsapp fa-lg me-1"></i> Chat WhatsApp
    </a>

    <!-- Footer -->
    <footer id="kontak" class="text-light py-5 mt-5" style="background-color: var(--feminine-dark);">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3 text-white"><?php echo htmlspecialchars($nama_toko); ?></h5>
                    <p class="small text-white-50"><?php echo htmlspecialchars($deskripsi_toko); ?></p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3 text-white">Kontak & Media Sosial</h6>
                    <p class="small mb-2">
                        <i class="fa fa-phone me-2 text-danger"></i> <?php echo htmlspecialchars($no_wa); ?>
                    </p>
                    <p class="small mb-2">
                        <a href="https://instagram.com/<?php echo htmlspecialchars($instagram_toko); ?>" target="_blank" class="text-decoration-none text-light">
                            <i class="fa-brands fa-instagram me-2 text-danger"></i> @<?php echo htmlspecialchars($instagram_toko); ?>
                        </a>
                    </p>
                    <p class="small text-white-50">
                        <i class="fa fa-map-marker-alt me-2"></i> <?php echo htmlspecialchars($alamat_toko); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3 text-white">Sistem Toko</h6>
                    <p class="small text-white-50">Melayani pemesanan buket secara langsung maupun pengiriman kurir lokal terpercaya.</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small text-white-50">&copy; 2026 <?php echo htmlspecialchars($nama_toko); ?>. All Rights Reserved.</div>
        </div>
    </footer>

    <script>
        function toggleMode() {
            const html = document.documentElement;
            const icon = document.getElementById('modeIcon');
            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                icon.className = 'fa fa-moon';
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                icon.className = 'fa fa-sun';
            }
        }

        // Sinkronkan highlight thumbnail saat foto di-slide/digeser
        document.querySelectorAll('.carousel').forEach(function(carouselEl) {
            carouselEl.addEventListener('slid.bs.carousel', function(event) {
                const parentModal = carouselEl.closest('.modal');
                if (!parentModal) return;
                const thumbs = parentModal.querySelectorAll('.modal-thumb-btn');
                thumbs.forEach(function(btn, idx) {
                    btn.classList.toggle('active', idx === event.to);
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>